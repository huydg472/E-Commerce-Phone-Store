<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import {newsService} from '@/services/newsService'
import {formatDate} from '@/utils/formatDate'
import {useNotificationStore} from '@/stores/notificationStore.js'
import BaseModal from '@/components/common/BaseModal.vue'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import {useClientPagination} from '@/composables/useClientPagination.js'

const loading = ref(true)
const errorMessage = ref('')
const search = ref('')
const statusFilter = ref('all')
const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const deletingId = ref(null)
const formError = ref('')
const fieldErrors = reactive({})

const categories = ref([])

const form = reactive({
  name: '',
  description: '',
  status: 'active',
  sort_order: 0,
})

const normalize = (value) => String(value ?? '').trim().toLowerCase()

const displayCategories = computed(() => Array.isArray(categories.value) ? categories.value : [])

const filteredCategories = computed(() => {
  const query = normalize(search.value)

  return displayCategories.value.filter((category) => {
    const matchesStatus = statusFilter.value === 'all' || category?.status === statusFilter.value
    const matchesQuery =
        !query ||
        [category?.name, category?.slug, category?.description]
            .filter(Boolean)
            .some((field) => normalize(field).includes(query))

    return matchesStatus && matchesQuery
  })
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems,
  pageStart,
  pageEnd,
} = useClientPagination(filteredCategories, {
  defaultPageSize: 5,
  pageSizeOptions: [5, 10],
})

const stats = computed(() => {
  const total = displayCategories.value.length
  const active = displayCategories.value.filter((category) => category?.status === 'active').length
  const inactive = total - active

  return {total, active, inactive}
})

const clearFieldErrors = () => {
  Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key])
}

const setFieldErrors = (errors = {}) => {
  clearFieldErrors()

  Object.entries(errors).forEach(([key, value]) => {
    fieldErrors[key] = Array.isArray(value) ? value[0] : value
  })
}

const loadData = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await newsService.getAdminCategories()
    const payload = response.data?.data ?? response.data ?? []
    categories.value = Array.isArray(payload) ? payload : []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không tải được danh mục tin tức.'
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.name = ''
  form.description = ''
  form.status = 'active'
  form.sort_order = 0
  editingId.value = null
}

const openCreateModal = () => {
  resetForm()
  formError.value = ''
  clearFieldErrors()
  showModal.value = true
}

const openEditModal = (category) => {
  editingId.value = category?.id ?? null
  form.name = category?.name || ''
  form.description = category?.description || ''
  form.status = category?.status || 'active'
  form.sort_order = Number(category?.sort_order || 0)
  formError.value = ''
  clearFieldErrors()
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  formError.value = ''
  clearFieldErrors()
  resetForm()
}

const submitForm = async () => {
  saving.value = true
  formError.value = ''
  clearFieldErrors()

  try {
    const payload = {
      name: form.name.trim(),
      description: form.description.trim() || null,
      status: form.status,
      sort_order: Number(form.sort_order || 0),
    }

    if (editingId.value) {
      await newsService.updateCategory(editingId.value, payload)
      useNotificationStore().success('Đã sửa danh mục tin tức.')
    } else {
      await newsService.createCategory(payload)
      useNotificationStore().success('Đã thêm danh mục tin tức.')
    }

    await loadData()
    closeModal()
  } catch (error) {
    formError.value = error.response?.data?.message || 'Không lưu được danh mục.'
    setFieldErrors(error.response?.data?.errors)
  } finally {
    saving.value = false
  }
}

const handleToggleStatus = async (category) => {
  const nextStatus = category?.status === 'active' ? 'inactive' : 'active'
  const previousStatus = category?.status

  category.status = nextStatus

  try {
    await newsService.updateCategory(category.id, {
      name: category.name,
      description: category.description,
      status: nextStatus,
      sort_order: Number(category.sort_order || 0),
    })
    useNotificationStore().success('Đã đổi trạng thái danh mục.')
  } catch (error) {
    category.status = previousStatus
    errorMessage.value = error.response?.data?.message || 'Không đổi được trạng thái.'
  }
}

const handleDelete = async (category) => {
  if (!category || deletingId.value) {
    return
  }

  if (!window.confirm(`Xóa danh mục "${category.name}"?`)) {
    return
  }

  deletingId.value = category.id

  try {
    await newsService.deleteCategory(category.id)
    useNotificationStore().success('Đã xóa danh mục tin tức.')
    await loadData()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không xóa được danh mục.'
  } finally {
    deletingId.value = null
  }
}

onMounted(loadData)
</script>

<template>
  <div class="admin-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Quản lý nội dung</p>
        <h1>Danh mục tin tức</h1>
        <p class="subtitle">Tạo và sắp xếp chủ đề để bài viết được nhóm rõ ràng trên trang tin tức.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="openCreateModal">
            <i class="bi bi-plus-lg"></i>
            Thêm danh mục
          </button>
          <button type="button" class="secondary-action" @click="search = ''; statusFilter = 'all'">
            <i class="bi bi-arrow-counterclockwise"></i>
            Xóa bộ lọc
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <article class="stat-card">
          <strong>{{ stats.total }}</strong>
          <span>Tổng danh mục</span>
        </article>
        <article class="stat-card">
          <strong>{{ stats.active }}</strong>
          <span>Đang hoạt động</span>
        </article>
        <article class="stat-card">
          <strong>{{ stats.inactive }}</strong>
          <span>Tạm ẩn</span>
        </article>
      </div>
    </section>

    <section v-if="loading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải danh mục...</p>
    </section>

    <section v-else-if="errorMessage" class="notice-card error">
      <i class="bi bi-exclamation-triangle"></i>
      <span>{{ errorMessage }}</span>
    </section>

    <template v-else>
      <section class="filter-card">
        <input v-model.trim="search" type="search" class="search-input" placeholder="Tìm danh mục...">
        <select v-model="statusFilter" class="status-select">
          <option value="all">Tất cả trạng thái</option>
          <option value="active">Đang hoạt động</option>
          <option value="inactive">Tạm ẩn</option>
        </select>
      </section>

      <section class="table-card">
        <div class="table-header">
          <div>
            <h2>Danh sách danh mục</h2>
            <p>Hiển thị {{ pageStart }}-{{ pageEnd }} trong tổng số {{ filteredCategories.length }} danh mục.</p>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table align-middle admin-table mb-0">
            <thead>
            <tr>
              <th>Tên</th>
              <th>Slug</th>
              <th>Bài viết</th>
              <th>Trạng thái</th>
              <th>Thứ tự</th>
              <th>Cập nhật</th>
              <th class="text-end">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="category in paginatedItems" :key="category.id">
              <td>
                <strong>{{ category.name }}</strong>
                <div class="muted">{{ category.description || 'Không có mô tả' }}</div>
              </td>
              <td><span class="slug-pill">{{ category.slug }}</span></td>
              <td>{{ Number(category.posts_count || 0) }}</td>
              <td>
                <button
                    type="button"
                    class="status-pill"
                    :class="category.status === 'active' ? 'is-active' : 'is-inactive'"
                    @click="handleToggleStatus(category)"
                >
                  {{ category.status === 'active' ? 'Hoạt động' : 'Tạm ẩn' }}
                </button>
              </td>
              <td>{{ category.sort_order || 0 }}</td>
              <td>{{ formatDate(category.updated_at) || 'N/A' }}</td>
              <td class="text-end">
                <button type="button" class="action-btn action-edit" @click="openEditModal(category)">
                  <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="action-btn action-delete" :disabled="deletingId === category.id" @click="handleDelete(category)">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <div v-if="totalPages > 1" class="pagination-wrap">
          <ListPaginationControls
              v-model:currentPage="currentPage"
              v-model:pageSize="pageSize"
              :total-pages="totalPages"
              :total-items="filteredCategories.length"
              :page-start="pageStart"
              :page-end="pageEnd"
              item-label="danh mục"
          />
        </div>
      </section>
    </template>

    <BaseModal :show="showModal" :title="editingId ? 'Sửa danh mục tin tức' : 'Thêm danh mục tin tức'" @close="closeModal">
      <form class="modal-form" @submit.prevent="submitForm">
        <div class="field-grid">
          <label class="field">
            <span>Tên danh mục</span>
            <input v-model.trim="form.name" type="text" :class="{ invalid: fieldErrors.name }">
            <small v-if="fieldErrors.name" class="field-error">{{ fieldErrors.name }}</small>
          </label>
          <label class="field">
            <span>Thứ tự</span>
            <input v-model.number="form.sort_order" type="number" min="0" :class="{ invalid: fieldErrors.sort_order }">
            <small v-if="fieldErrors.sort_order" class="field-error">{{ fieldErrors.sort_order }}</small>
          </label>
        </div>

        <label class="field">
          <span>Mô tả</span>
          <textarea v-model.trim="form.description" rows="4" :class="{ invalid: fieldErrors.description }"></textarea>
          <small v-if="fieldErrors.description" class="field-error">{{ fieldErrors.description }}</small>
        </label>

        <label class="field">
          <span>Trạng thái</span>
          <select v-model="form.status" :class="{ invalid: fieldErrors.status }">
            <option value="active">Đang hoạt động</option>
            <option value="inactive">Tạm ẩn</option>
          </select>
          <small v-if="fieldErrors.status" class="field-error">{{ fieldErrors.status }}</small>
        </label>

        <p v-if="formError" class="form-error">{{ formError }}</p>

        <div class="modal-actions">
          <button type="button" class="secondary-action" @click="closeModal">Hủy</button>
          <button type="submit" class="primary-action" :disabled="saving">
            {{ saving ? 'Đang lưu...' : 'Lưu danh mục' }}
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<style scoped>
.hero-card,
.filter-card,
.table-card,
.state-card,
.notice-card {
  margin-bottom: 18px;
}

.hero-card {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 24px;
  border: 1px solid #e5eaf3;
  border-radius: 24px;
  background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
}

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 32px;
  font-weight: 900;
}

.subtitle {
  margin: 10px 0 0;
  color: #475569;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 16px;
}

.primary-action,
.secondary-action {
  min-height: 44px;
  padding: 0 16px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
}

.primary-action {
  border: none;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
}

.secondary-action {
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}

.primary-action:hover {
  filter: brightness(0.98);
}

.secondary-action:hover {
  border-color: #bfdbfe;
  color: #2563eb;
  background: #f8fbff;
}

.hero-stats {
  display: grid;
  gap: 12px;
  min-width: 220px;
}

.stat-card {
  padding: 16px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #fff;
}

.stat-card strong {
  display: block;
  color: #0f172a;
  font-size: 28px;
  font-weight: 900;
}

.stat-card span,
.muted {
  color: #64748b;
  font-size: 13px;
}

.filter-card {
  padding: 14px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #fff;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 200px;
  gap: 12px;
}

.search-input,
.status-select,
.field input,
.field select,
.field textarea {
  width: 100%;
  min-height: 44px;
  padding: 0 14px;
  border: 1px solid #dbe4f0;
  border-radius: 12px;
  background: #fff;
}

.field textarea {
  min-height: 110px;
  padding: 12px 14px;
  resize: vertical;
}

.table-card {
  padding: 20px;
  border: 1px solid #e5eaf3;
  border-radius: 22px;
  background: #fff;
}

.table-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}

.table-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 20px;
  font-weight: 900;
}

.table-header p {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 13px;
}

.slug-pill,
.status-pill {
  display: inline-flex;
  align-items: center;
  min-height: 30px;
  padding: 0 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
}

.slug-pill {
  background: #f8fafc;
  color: #475569;
}

.status-pill {
  border: 1px solid transparent;
}

.status-pill.is-active {
  background: #ecfdf5;
  color: #047857;
}

.status-pill.is-inactive {
  background: #fff7ed;
  color: #c2410c;
}

.action-btn {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 10px;
  margin-left: 6px;
}

.action-edit {
  background: #eff6ff;
  color: #2563eb;
}

.action-delete {
  background: #fef2f2;
  color: #dc2626;
}

.pagination-wrap {
  margin-top: 14px;
}

.modal-form {
  display: grid;
  gap: 14px;
}

.field {
  display: grid;
  gap: 8px;
}

.field span {
  color: #334155;
  font-size: 13px;
  font-weight: 800;
}

.field-grid {
  display: grid;
  grid-template-columns: 1fr 140px;
  gap: 12px;
}

.field-error,
.form-error {
  color: #dc2626;
  font-size: 12px;
  font-weight: 700;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 4px;
}

@media (max-width: 991.98px) {
  .hero-card,
  .filter-card {
    grid-template-columns: 1fr;
    flex-direction: column;
  }

  .secondary-action,
  .primary-action {
    width: 100%;
    justify-content: center;
  }
}
</style>
