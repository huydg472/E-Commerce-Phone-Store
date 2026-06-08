<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { storeToRefs } from 'pinia'
import { useBrandStore } from '@/stores/brandStore'
import { formatDate } from '@/utils/formatDate'

const brandStore = useBrandStore()
const { items: brands, loading: brandLoading } = storeToRefs(brandStore)

const search = ref('')
const statusFilter = ref('all')
const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const loadingError = ref('')
const deletingId = ref(null)
const formError = ref('')
const manualSlug = ref(false)
const fieldErrors = reactive({})

const form = reactive({
  name: '',
  slug: '',
  logo_url: '',
  description: '',
  status: 'active',
})

const normalize = (value) => String(value ?? '').trim().toLowerCase()

const slugify = (value) =>
  String(value ?? '')
    .trim()
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '')

const displayBrands = computed(() => (Array.isArray(brands.value) ? brands.value : []))

const filteredBrands = computed(() => {
  const query = normalize(search.value)

  return displayBrands.value.filter((brand) => {
    const matchesStatus = statusFilter.value === 'all' || brand?.status === statusFilter.value
    const matchesKeyword =
      !query ||
      [brand?.name, brand?.slug, brand?.description, brand?.logo_url]
        .some((field) => normalize(field).includes(query))

    return matchesStatus && matchesKeyword
  })
})

const stats = computed(() => {
  const total = displayBrands.value.length
  const active = displayBrands.value.filter((brand) => brand?.status === 'active').length
  const inactive = total - active

  return { total, active, inactive }
})

const clearFieldErrors = () => {
  Object.keys(fieldErrors).forEach((key) => {
    delete fieldErrors[key]
  })
}

const setFieldErrors = (errors = {}) => {
  clearFieldErrors()

  Object.entries(errors).forEach(([key, value]) => {
    fieldErrors[key] = Array.isArray(value) ? value[0] : value
  })
}

const resetForm = () => {
  form.name = ''
  form.slug = ''
  form.logo_url = ''
  form.description = ''
  form.status = 'active'
  manualSlug.value = false
  editingId.value = null
}

watch(
  () => form.name,
  (value) => {
    if (!manualSlug.value) {
      form.slug = slugify(value)
    }
  }
)

const loadBrands = async () => {
  loadingError.value = ''

  try {
    await brandStore.fetchAll()
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được danh sách thương hiệu.'
  }
}

const openCreateModal = () => {
  resetForm()
  formError.value = ''
  clearFieldErrors()
  showModal.value = true
}

const openEditModal = (brand) => {
  resetForm()
  form.name = brand?.name ?? ''
  form.slug = brand?.slug ?? ''
  form.logo_url = brand?.logo_url ?? ''
  form.description = brand?.description ?? ''
  form.status = brand?.status ?? 'active'
  manualSlug.value = true
  editingId.value = brand?.id ?? null
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

const handleSubmit = async () => {
  saving.value = true
  formError.value = ''
  clearFieldErrors()

  try {
    const payload = {
      name: form.name.trim(),
      slug: form.slug.trim(),
      logo_url: form.logo_url.trim() || null,
      description: form.description.trim() || null,
      status: form.status,
    }

    if (editingId.value) {
      await brandStore.update(editingId.value, payload)
    } else {
      await brandStore.create(payload)
    }

    closeModal()
  } catch (error) {
    formError.value = error.response?.data?.message || 'Không lưu được thương hiệu.'
    setFieldErrors(error.response?.data?.errors)
  } finally {
    saving.value = false
  }
}

const handleToggleStatus = async (brand) => {
  if (!brand || brandLoading.value) {
    return
  }

  const nextStatus = brand.status === 'active' ? 'inactive' : 'active'
  const previousStatus = brand.status

  brand.status = nextStatus
  loadingError.value = ''

  try {
    await brandStore.update(brand.id, { status: nextStatus })
  } catch (error) {
    brand.status = previousStatus
    loadingError.value = error.response?.data?.message || 'Không cập nhật được trạng thái thương hiệu.'
  }
}

const handleDelete = async (brand) => {
  if (!brand || deletingId.value) {
    return
  }

  if (!window.confirm(`Xóa thương hiệu "${brand.name}"?`)) {
    return
  }

  deletingId.value = brand.id
  loadingError.value = ''

  try {
    await brandStore.remove(brand.id)
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không xóa được thương hiệu.'
  } finally {
    deletingId.value = null
  }
}

onMounted(loadBrands)
</script>

<template>
  <div class="admin-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Quản lý thương hiệu</p>
        <h1>Danh sách thương hiệu</h1>
        <p class="subtitle">Quản lý thương hiệu, logo, trạng thái hiển thị và thông tin mô tả trong cùng một màn hình.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="openCreateModal">
            <i class="bi bi-plus-lg"></i>
            Thêm thương hiệu
          </button>

          <button type="button" class="secondary-action" @click="search = ''; statusFilter = 'all'">
            <i class="bi bi-arrow-counterclockwise"></i>
            Xóa bộ lọc
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <article class="stat-card">
          <span class="stat-icon stat-icon-total">
            <i class="bi bi-award"></i>
          </span>
          <div>
            <strong>{{ stats.total }}</strong>
            <span>Tổng thương hiệu</span>
          </div>
        </article>

        <article class="stat-card">
          <span class="stat-icon stat-icon-active">
            <i class="bi bi-check2-circle"></i>
          </span>
          <div>
            <strong>{{ stats.active }}</strong>
            <span>Đang hoạt động</span>
          </div>
        </article>

        <article class="stat-card">
          <span class="stat-icon stat-icon-inactive">
            <i class="bi bi-slash-circle"></i>
          </span>
          <div>
            <strong>{{ stats.inactive }}</strong>
            <span>Tạm ẩn</span>
          </div>
        </article>
      </div>
    </section>

    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input
          v-model.trim="search"
          type="search"
          placeholder="Tìm theo tên, slug, mô tả..."
        />
      </div>

      <select v-model="statusFilter" class="filter-select">
        <option value="all">Tất cả trạng thái</option>
        <option value="active">Hoạt động</option>
        <option value="inactive">Tạm ẩn</option>
      </select>

      <div class="table-chip">
        <i class="bi bi-funnel"></i>
        <span>{{ filteredBrands.length }} kết quả</span>
      </div>
    </section>

    <div v-if="brandLoading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải danh sách thương hiệu...</p>
    </div>

    <div v-else-if="loadingError" class="state-card error-state">
      <i class="bi bi-exclamation-triangle"></i>
      <p>{{ loadingError }}</p>
      <button type="button" class="secondary-action" @click="loadBrands">Thử lại</button>
    </div>

    <div v-else class="table-card">
      <div class="table-responsive">
        <table class="table align-middle admin-table mb-0">
          <thead>
            <tr>
              <th>Thương hiệu</th>
              <th>Slug</th>
              <th>Trạng thái</th>
              <th>Cập nhật</th>
              <th class="text-end">Thao tác</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="brand in filteredBrands" :key="brand.id">
              <td>
                <div class="brand-cell">
                  <div class="brand-logo" :class="{ placeholder: !brand.logo_url }">
                    <img v-if="brand.logo_url" :src="brand.logo_url" :alt="brand.name" />
                    <i v-else class="bi bi-award"></i>
                  </div>

                  <div class="brand-text">
                    <strong>{{ brand.name }}</strong>
                    <small>{{ brand.description || 'Chưa có mô tả' }}</small>
                  </div>
                </div>
              </td>
              <td>
                <code class="slug-chip">{{ brand.slug }}</code>
              </td>
              <td>
                <button
                  type="button"
                  class="status-pill"
                  :class="brand.status === 'active' ? 'is-active' : 'is-inactive'"
                  :disabled="brandLoading"
                  @click="handleToggleStatus(brand)"
                  :title="brand.status === 'active' ? 'Hoạt động' : 'Tạm ẩn'"
                  :aria-label="brand.status === 'active' ? 'Hoạt động' : 'Tạm ẩn'"
                >
                  <i :class="brand.status === 'active' ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
                </button>
              </td>
              <td>{{ formatDate(brand.updated_at || brand.created_at) }}</td>
              <td>
                <div class="action-group">
                  <button type="button" class="action-btn action-edit" @click="openEditModal(brand)" title="Chỉnh sửa">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button
                    type="button"
                    class="action-btn action-delete"
                    :disabled="deletingId === brand.id"
                    @click="handleDelete(brand)"
                    title="Xóa"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!filteredBrands.length">
              <td colspan="5">
                <div class="empty-state">
                  <i class="bi bi-award"></i>
                  <p>Không có thương hiệu phù hợp.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <teleport to="body">
      <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
        <div class="modal-card">
          <div class="modal-head">
            <div>
              <p>{{ editingId ? 'Chỉnh sửa thương hiệu' : 'Thêm thương hiệu' }}</p>
              <h3>{{ form.name || 'Xem trước thương hiệu' }}</h3>
            </div>

            <button type="button" class="icon-close" @click="closeModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div v-if="formError" class="alert alert-danger mb-0">
            {{ formError }}
          </div>

          <div class="modal-body">
            <form class="modal-form" @submit.prevent="handleSubmit">
              <div class="form-row">
                <label>
                  <span>Tên thương hiệu</span>
                  <input v-model.trim="form.name" type="text" class="control" :class="{ invalid: fieldErrors.name }" />
                  <small v-if="fieldErrors.name" class="field-error">{{ fieldErrors.name }}</small>
                </label>

                <label>
                  <span>Slug</span>
                  <input
                    v-model.trim="form.slug"
                    type="text"
                    class="control"
                    :class="{ invalid: fieldErrors.slug }"
                    @input="manualSlug = true"
                  />
                  <small v-if="fieldErrors.slug" class="field-error">{{ fieldErrors.slug }}</small>
                </label>
              </div>

              <div class="form-row">
                <label>
                  <span>Logo URL</span>
                  <input v-model.trim="form.logo_url" type="url" class="control" :class="{ invalid: fieldErrors.logo_url }" />
                  <small v-if="fieldErrors.logo_url" class="field-error">{{ fieldErrors.logo_url }}</small>
                </label>

                <label>
                  <span>Trạng thái</span>
                  <select v-model="form.status" class="control" :class="{ invalid: fieldErrors.status }">
                    <option value="active">Hoạt động</option>
                    <option value="inactive">Tạm ẩn</option>
                  </select>
                  <small v-if="fieldErrors.status" class="field-error">{{ fieldErrors.status }}</small>
                </label>
              </div>

              <label class="full-width">
                <span>Mô tả</span>
                <textarea
                  v-model.trim="form.description"
                  rows="4"
                  class="control"
                  :class="{ invalid: fieldErrors.description }"
                  placeholder="Mô tả ngắn về thương hiệu..."
                ></textarea>
                <small v-if="fieldErrors.description" class="field-error">{{ fieldErrors.description }}</small>
              </label>

              <div class="form-actions">
                <button type="button" class="secondary-action" @click="closeModal">Hủy</button>
                <button type="submit" class="primary-action" :disabled="saving">
                  <span v-if="saving" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                  <span>{{ editingId ? 'Lưu thay đổi' : 'Tạo thương hiệu' }}</span>
                </button>
              </div>
            </form>

            <aside class="preview-card">
              <div class="preview-logo" :class="{ empty: !form.logo_url }">
                <img v-if="form.logo_url" :src="form.logo_url" :alt="form.name || 'Brand preview'" />
                <i v-else class="bi bi-award"></i>
              </div>

              <div class="preview-meta">
                <h4>{{ form.name || 'Tên thương hiệu' }}</h4>
                <p>{{ form.slug || 'slug-thuong-hieu' }}</p>
                <span class="preview-status" :class="form.status === 'active' ? 'is-active' : 'is-inactive'">
                  {{ form.status === 'active' ? 'Hoạt động' : 'Tạm ẩn' }}
                </span>
              </div>

              <p class="preview-description">
                {{ form.description || 'Phần mô tả thương hiệu sẽ hiển thị ở đây.' }}
              </p>
            </aside>
          </div>
        </div>
      </div>
    </teleport>
  </div>
</template>

<style scoped>
.admin-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-card {
  padding: 24px;
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background:
    radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 30%),
    linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.9fr);
  gap: 18px;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 32px;
  font-weight: 900;
  line-height: 1.1;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 18px;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.hero-stats .stat-card {
  width: 100%;
  height: 100%;
  min-height: 96px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  border: 1px solid #edf2f7;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.92);
  box-shadow: none;
}

.hero-stats .stat-card strong {
  display: block;
  margin: 0;
  color: #020617;
  font-size: 24px;
  font-weight: 900;
  line-height: 1;
}

.hero-stats .stat-card span:last-child {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
  line-height: 1.3;
}

.hero-stats .stat-icon {
  width: 44px;
  height: 44px;
  display: inline-grid;
  place-items: center;
  flex: 0 0 auto;
  border-radius: 14px;
  color: #ffffff;
  font-size: 18px;
}

.hero-stats .stat-icon i {
  line-height: 1;
}

.stat-icon-total {
  background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
}

.stat-icon-active {
  background: linear-gradient(135deg, #10b981 0%, #22c55e 100%);
}

.stat-icon-inactive {
  background: linear-gradient(135deg, #64748b 0%, #475569 100%);
}

.eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.subtitle {
  margin: 8px 0 0;
  color: #64748b;
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
  border: 1px solid transparent;
}

.primary-action {
  border: none;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
}

.secondary-action {
  border: 1px solid #dbe3ef;
  color: #334155;
  background: #ffffff;
}

.toolbar-card {
  padding: 16px;
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.search-box {
  min-width: min(100%, 460px);
  height: 46px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 10px;
  flex: 1;
}

.search-box i {
  color: #64748b;
}

.search-box input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: #0f172a;
  font-size: 14px;
}

.filter-select {
  min-width: 180px;
  height: 46px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
  color: #0f172a;
  font-weight: 600;
}

.table-chip {
  min-height: 38px;
  padding: 0 14px;
  border-radius: 999px;
  background: #eff6ff;
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}

.table-chip i {
  font-size: 13px;
}

.state-card,
.table-card {
  border: 1px solid #e5e9f1;
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.05);
}

.state-card {
  min-height: 240px;
  display: grid;
  place-items: center;
  gap: 12px;
  color: #64748b;
}

.state-card i {
  font-size: 26px;
  color: #ef4444;
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  min-width: 980px;
}

.admin-table thead th {
  height: 56px;
  color: #0f172a;
  background: #f8fbff;
  border-bottom: 1px solid #edf0f5;
  font-size: 14px;
  font-weight: 800;
  white-space: nowrap;
}

.admin-table tbody td {
  height: 78px;
  color: #0f172a;
  border-bottom: 1px solid #edf0f5;
  font-size: 15px;
  white-space: nowrap;
  vertical-align: middle;
}

.admin-table tbody tr:last-child td {
  border-bottom: 0;
}

.admin-table th:first-child,
.admin-table td:first-child {
  padding-left: 22px;
}

.admin-table th:last-child,
.admin-table td:last-child {
  padding-right: 22px;
}

.brand-cell {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-logo,
.preview-logo {
  width: 52px;
  height: 52px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: #f1f5f9;
  overflow: hidden;
}

.brand-logo img,
.preview-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.brand-logo.placeholder,
.preview-logo.empty {
  color: #64748b;
}

.brand-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.brand-text strong {
  font-weight: 800;
}

.brand-text small {
  color: #64748b;
}

.slug-chip {
  display: inline-flex;
  padding: 6px 10px;
  border-radius: 999px;
  background: #eef4ff;
  color: #1d4ed8;
  font-size: 13px;
  font-weight: 700;
}

.status-pill {
  width: 42px;
  height: 42px;
  display: inline-grid;
  place-items: center;
  border: 0;
  border-radius: 999px;
  font-size: 24px;
  line-height: 1;
}

.status-pill.is-active {
  color: #15803d;
  background: #ecfdf5;
}

.status-pill.is-inactive {
  color: #b45309;
  background: #fff7ed;
}

.action-group {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.action-btn {
  width: 40px;
  height: 40px;
  display: inline-grid;
  place-items: center;
  border-radius: 12px;
  border: 0;
  text-decoration: none;
}

.action-edit {
  color: #7c3aed;
  background: #f3e8ff;
}

.action-delete {
  color: #dc2626;
  background: #fee2e2;
}

.action-delete:disabled {
  opacity: 0.6;
  cursor: wait;
}

.empty-state {
  min-height: 180px;
  display: grid;
  place-items: center;
  gap: 10px;
  color: #64748b;
  text-align: center;
}

.empty-state i {
  font-size: 28px;
  color: #2563eb;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1050;
  display: grid;
  place-items: center;
  padding: 20px;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(4px);
}

.modal-card {
  width: min(100%, 1040px);
  border-radius: 22px;
  background: #ffffff;
  overflow: hidden;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
}

.modal-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
  padding: 20px 22px 16px;
  border-bottom: 1px solid #edf0f5;
}

.modal-head p {
  margin: 0 0 4px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.modal-head h3 {
  margin: 0;
  color: #0f172a;
  font-size: 24px;
  font-weight: 850;
}

.icon-close {
  width: 38px;
  height: 38px;
  display: inline-grid;
  place-items: center;
  border: 0;
  border-radius: 12px;
  background: #f1f5f9;
  color: #475569;
}

.modal-body {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) 320px;
}

.modal-form {
  padding: 20px 22px 22px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.full-width {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.modal-form label {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.modal-form label span {
  color: #334155;
  font-size: 14px;
  font-weight: 700;
}

.control {
  width: 100%;
  min-height: 44px;
  padding: 11px 14px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #0f172a;
  outline: none;
}

textarea.control {
  min-height: 118px;
  resize: vertical;
}

.control:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.control.invalid {
  border-color: #ef4444;
}

.field-error {
  color: #dc2626;
  font-size: 12px;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 4px;
}

.preview-card {
  padding: 22px;
  border-left: 1px solid #edf0f5;
  background: linear-gradient(180deg, #f8fbff, #ffffff);
}

.preview-logo {
  width: 100%;
  height: 180px;
  border-radius: 18px;
  background: linear-gradient(135deg, #eff6ff, #ffffff);
  font-size: 42px;
  color: #2563eb;
}

.preview-meta {
  margin-top: 16px;
}

.preview-meta h4 {
  margin: 0 0 6px;
  color: #0f172a;
  font-size: 20px;
  font-weight: 850;
}

.preview-meta p {
  margin: 0 0 10px;
  color: #64748b;
}

.preview-status {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
}

.preview-status.is-active {
  color: #15803d;
  background: #ecfdf5;
}

.preview-status.is-inactive {
  color: #b45309;
  background: #fff7ed;
}

.preview-description {
  margin: 16px 0 0;
  color: #475569;
  line-height: 1.6;
}

@media (max-width: 1199.98px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .hero-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .modal-body {
    grid-template-columns: 1fr;
  }

  .preview-card {
    border-left: 0;
    border-top: 1px solid #edf0f5;
  }
}

@media (max-width: 992px) {
  .toolbar-card {
    align-items: stretch;
  }

  .filter-select {
    width: 100%;
    min-width: 0;
  }
}

@media (max-width: 767.98px) {
  .toolbar-card,
  .form-row {
    grid-template-columns: 1fr;
    flex-direction: column;
    align-items: stretch;
  }

  .hero-card {
    padding: 20px;
  }

  .hero-copy h1 {
    font-size: 24px;
  }

  .hero-actions {
    flex-direction: column;
  }

  .hero-stats {
    grid-template-columns: 1fr;
  }

  .search-box,
  .filter-select,
  .table-chip,
  .primary-action,
  .secondary-action {
    width: 100%;
    max-width: none;
  }

  .modal-card {
    max-height: 92vh;
    overflow: auto;
  }

  .modal-head {
    padding: 18px;
  }

  .modal-form,
  .preview-card {
    padding: 18px;
  }

  .form-actions {
    flex-direction: column-reverse;
  }
}
</style>
