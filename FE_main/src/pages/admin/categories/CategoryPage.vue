<script setup>
import {computed, onMounted, ref} from 'vue'
import {storeToRefs} from 'pinia'
import {useCategoryStore} from '@/stores/categoryStore.js'
import {useProductStore} from '@/stores/productStore.js'
import CategoryForm from '@/components/category/CategoryForm.vue'
import CategoryTable from '@/components/category/CategoryTable.vue'

const categoryStore = useCategoryStore()
const productStore = useProductStore()

const {items: categories, loading: categoryLoading} = storeToRefs(categoryStore)
const {items: products} = storeToRefs(productStore)

const search = ref('')
const selectedStatus = ref('all')
const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const loadingError = ref('')
const deletingId = ref(null)
const formError = ref('')
const fieldErrors = ref({})

const normalize = (value) => {
  return String(value ?? '')
      .trim()
      .toLowerCase()
}

const displayCategories = computed(() => (Array.isArray(categories.value) ? categories.value : []))
const displayProducts = computed(() => (Array.isArray(products.value) ? products.value : []))

const countsByCategory = computed(() => {
  return displayProducts.value.reduce((acc, product) => {
    const key = String(product?.category_id ?? '')
    acc[key] = (acc[key] || 0) + 1
    return acc
  }, {})
})

const filteredCategories = computed(() => {
  const query = normalize(search.value)

  return displayCategories.value.filter((category) => {
    const matchesStatus = selectedStatus.value === 'all' || category?.status === selectedStatus.value

    if (!matchesStatus) {
      return false
    }

    if (!query) {
      return true
    }

    return [category?.name, category?.slug, category?.description].some((field) => normalize(field).includes(query))
  })
})

const stats = computed(() => {
  const total = displayCategories.value.length
  const active = displayCategories.value.filter((category) => category?.status === 'active').length
  const inactive = total - active
  const withProducts = displayCategories.value.filter((category) => (countsByCategory.value[String(category.id)] || 0) > 0).length

  return {total, active, inactive, withProducts}
})

const clearFieldErrors = () => {
  fieldErrors.value = {}
}

const setFieldErrors = (errors = {}) => {
  const next = {}

  Object.entries(errors).forEach(([key, value]) => {
    next[key] = Array.isArray(value) ? value[0] : value
  })

  fieldErrors.value = next
}

const selectedCategory = computed(() => displayCategories.value.find((category) => category?.id === editingId.value) || null)

const loadData = async () => {
  loadingError.value = ''

  try {
    await Promise.all([
      categoryStore.fetchAll(),
      productStore.fetchAll({per_page: 1000, sort: 'latest'}),
    ])
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được danh sách danh mục.'
  }
}

const openCreateModal = () => {
  formError.value = ''
  clearFieldErrors()
  editingId.value = null
  showModal.value = true
}

const openEditModal = (category) => {
  editingId.value = category?.id ?? null
  formError.value = ''
  clearFieldErrors()
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  formError.value = ''
  clearFieldErrors()
  editingId.value = null
}

const handleSubmit = async (payload) => {
  saving.value = true
  formError.value = ''
  clearFieldErrors()

  try {
    if (editingId.value) {
      await categoryStore.update(editingId.value, payload)
    } else {
      await categoryStore.create(payload)
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
  loadingError.value = ''

  try {
    await categoryStore.update(category.id, {status: nextStatus})
  } catch (error) {
    category.status = previousStatus
    loadingError.value = error.response?.data?.message || 'Không cập nhật được trạng thái danh mục.'
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
  loadingError.value = ''

  try {
    await categoryStore.remove(category.id)
    await loadData()
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không xóa được danh mục.'
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
        <p class="eyebrow">Quản lý danh mục</p>
        <h1>Danh mục sản phẩm</h1>
        <p class="subtitle">
          Tổ chức danh mục rõ ràng để điều hướng sản phẩm, thống kê và phân nhóm trong admin dashboard.
        </p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="openCreateModal">
            <i class="bi bi-plus-lg"></i>
            Thêm danh mục
          </button>
          <button type="button" class="secondary-action" @click="search = ''; selectedStatus = 'all'">
            <i class="bi bi-arrow-counterclockwise"></i>
            Xóa bộ lọc
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <article class="stat-card">
          <span class="stat-icon stat-icon-total">
            <i class="bi bi-grid-3x3-gap"></i>
          </span>
          <div>
            <strong>{{ stats.total }}</strong>
            <span>Tổng danh mục</span>
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
          <span class="stat-icon stat-icon-featured">
            <i class="bi bi-box-seam"></i>
          </span>
          <div>
            <strong>{{ stats.withProducts }}</strong>
            <span>Có sản phẩm</span>
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
        <input v-model.trim="search" type="search" placeholder="Tìm theo tên, slug, mô tả..."/>
      </div>

      <select v-model="selectedStatus" class="filter-select">
        <option value="all">Tất cả trạng thái</option>
        <option value="active">Đang hoạt động</option>
        <option value="inactive">Tạm ẩn</option>
      </select>

      <div class="table-chip">
        <i class="bi bi-funnel"></i>
        <span>{{ filteredCategories.length }} kết quả</span>
      </div>
    </section>

    <section v-if="loadingError && !displayCategories.length" class="state-card error-state">
      <i class="bi bi-exclamation-triangle"></i>
      <p>{{ loadingError }}</p>
      <button type="button" class="secondary-action" @click="loadData">Thử lại</button>
    </section>

    <section v-else-if="categoryLoading && !displayCategories.length" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải danh mục...</p>
    </section>

    <CategoryTable
        v-else
        :categories="filteredCategories"
        :loading="categoryLoading"
        :deleting-id="deletingId"
        @edit="openEditModal"
        @delete="handleDelete"
        @toggle="handleToggleStatus"
    />

    <CategoryForm
        :visible="showModal"
        :category="selectedCategory"
        :saving="saving"
        :form-error="formError"
        :field-errors="fieldErrors"
        @close="closeModal"
        @submit="handleSubmit"
    />
  </div>
</template>

<style scoped>
.admin-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-card,
.toolbar-card,
.table-card,
.state-card {
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
}

.hero-card {
  padding: 24px;
  background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 30%),
  linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.9fr);
  gap: 18px;
}

.eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 32px;
  font-weight: 900;
  line-height: 1.1;
}

.subtitle {
  max-width: 760px;
  margin: 10px 0 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.7;
}

.hero-actions {
  margin-top: 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
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

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.stat-card {
  min-height: 96px;
  padding: 16px;
  border: 1px solid #edf2f7;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.9);
  display: flex;
  align-items: center;
  gap: 14px;
}

.stat-card strong {
  display: block;
  color: #0f172a;
  font-size: 24px;
  font-weight: 900;
  line-height: 1;
}

.stat-card span:last-child {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.hero-stats .stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  color: #ffffff;
  font-size: 18px;
  flex-shrink: 0;
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

.stat-icon-featured {
  background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
}

.stat-icon-inactive {
  background: linear-gradient(135deg, #64748b 0%, #475569 100%);
}

.toolbar-card {
  padding: 16px;
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
  font-weight: 500;
  line-height: 1.2;
}

.filter-select {
  min-width: 180px;
  height: 46px;
  padding: 0 42px 0 16px;
  border: 1px solid #dbe3ef;
  border-radius: 999px;
  background-color: #ffffff;
  background-image: url("data:image/svg+xml,%3Csvg width='14' height='14' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.5 7.5L10 12L14.5 7.5' stroke='%230f172a' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 16px center;
  background-size: 14px 14px;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
  color: #0f172a;
  font-size: 14px;
  font-weight: 600;
  line-height: 1.2;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  outline: none;
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

.state-card {
  min-height: 260px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  color: #475569;
}

.state-card.error-state {
  color: #dc2626;
}

.state-card i {
  font-size: 30px;
}

.table-card {
  overflow: hidden;
}

.table-header {
  padding: 18px 20px;
  border-bottom: 1px solid #eef2f7;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.table-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
}

.table-header p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 13px;
}

.inline-alert {
  padding: 12px 20px;
  border-bottom: 1px solid #fee2e2;
  background: #fff7f7;
  color: #b91c1c;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 700;
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  min-width: 1100px;
}

.admin-table thead th {
  padding: 16px 20px;
  border-bottom: 1px solid #e5eaf3;
  color: #64748b;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}

.admin-table tbody td {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
  color: #0f172a;
  font-size: 14px;
  vertical-align: middle;
}

.category-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.category-icon {
  width: 48px;
  height: 48px;
  border-radius: 14px;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  color: #2563eb;
  display: grid;
  place-items: center;
  font-size: 18px;
  flex-shrink: 0;
}

.category-cell strong {
  display: block;
  color: #0f172a;
  font-weight: 800;
}

.category-cell span {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 12px;
}

.slug-pill {
  min-height: 32px;
  padding: 0 10px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 800;
}

.slug-pill {
  background: #f8fafc;
  color: #334155;
}

.status-pill {
  width: 42px;
  height: 34px;
  padding: 0;
  border: 1px solid transparent;
  border-radius: 999px;
  position: relative;
  overflow: hidden;
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  font-weight: 800;
  transition: 0.2s ease;
}

.status-pill i {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  font-size: 16px;
  line-height: 1;
}

.status-pill.is-active {
  background: #ecfdf5;
  color: #15803d;
  border-color: #bbf7d0;
}

.status-pill.is-inactive {
  background: #fff7ed;
  color: #c2410c;
  border-color: #fed7aa;
}

.action-group {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.action-btn {
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.2s ease, opacity 0.2s ease;
}

.action-btn:hover {
  transform: translateY(-1px);
}

.action-edit {
  background: #f5f3ff;
  color: #7c3aed;
}

.action-delete {
  background: #fef2f2;
  color: #dc2626;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none;
}

.empty-state {
  padding: 46px 16px;
  text-align: center;
  color: #64748b;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
}

.empty-state i {
  font-size: 36px;
  color: #94a3b8;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1050;
  background: rgba(15, 23, 42, 0.45);
  display: grid;
  place-items: center;
  padding: 16px;
}

.modal-card {
  width: min(620px, 100%);
  max-height: calc(100vh - 32px);
  overflow: auto;
  border-radius: 22px;
  background: #ffffff;
  box-shadow: 0 24px 80px rgba(15, 23, 42, 0.22);
}

.modal-header {
  padding: 20px 20px 16px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  border-bottom: 1px solid #eef2f7;
}

.modal-kicker {
  margin: 0 0 4px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.modal-header h3 {
  margin: 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

.modal-close {
  width: 40px;
  height: 40px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #334155;
}

.modal-alert {
  margin: 16px 20px 0;
  padding: 12px 14px;
  border: 1px solid #fecaca;
  border-radius: 14px;
  background: #fff7f7;
  color: #b91c1c;
  font-size: 13px;
  font-weight: 700;
}

.modal-form {
  padding: 18px 20px 20px;
  display: grid;
  gap: 14px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field label {
  color: #0f172a;
  font-size: 13px;
  font-weight: 800;
}

.control {
  min-height: 44px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #0f172a;
  font-size: 14px;
  outline: none;
}

.textarea {
  min-height: 110px;
  padding-top: 12px;
  resize: vertical;
}

.control.invalid {
  border-color: #fca5a5;
  box-shadow: 0 0 0 3px rgba(252, 165, 165, 0.12);
}

.field-hint {
  color: #64748b;
  font-size: 12px;
}

.field-error {
  color: #dc2626;
  font-size: 12px;
  font-weight: 700;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 6px;
}

@media (max-width: 1200px) {
  .hero-card {
    grid-template-columns: 1fr;
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

@media (max-width: 768px) {
  .hero-card,
  .toolbar-card,
  .table-header {
    padding: 16px;
  }

  .hero-copy h1 {
    font-size: 28px;
  }

  .hero-stats {
    grid-template-columns: 1fr;
  }

  .modal-header,
  .modal-form {
    padding-left: 16px;
    padding-right: 16px;
  }

  .modal-actions {
    flex-direction: column-reverse;
  }

  .secondary-action,
  .primary-action {
    width: 100%;
    justify-content: center;
  }
}
</style>
