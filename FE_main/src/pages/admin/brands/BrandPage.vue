<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import {storeToRefs} from 'pinia'
import {useBrandStore} from '@/stores/brandStore'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import {useClientPagination} from '@/composables/useClientPagination.js'
import BrandForm from '@/components/brand/BrandForm.vue'
import BrandTable from '@/components/brand/BrandTable.vue'

const brandStore = useBrandStore()
const {items: brands, loading: brandLoading} = storeToRefs(brandStore)

const search = ref('')
const statusFilter = ref('all')
const typeFilter = ref('all')
const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const loadingError = ref('')
const deletingId = ref(null)
const formError = ref('')
const fieldErrors = reactive({})

const normalize = (value) => String(value ?? '').trim().toLowerCase()

const displayBrands = computed(() => (Array.isArray(brands.value) ? brands.value : []))

const filteredBrands = computed(() => {
  const query = normalize(search.value)

  return displayBrands.value.filter((brand) => {
    const matchesStatus = statusFilter.value === 'all' || brand?.status === statusFilter.value
    const matchesType = typeFilter.value === 'all' || brand?.type === typeFilter.value
    const matchesKeyword =
        !query ||
        [brand?.name, brand?.slug, brand?.description, brand?.logo_url]
            .some((field) => normalize(field).includes(query))

    return matchesStatus && matchesType && matchesKeyword
  })
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems: paginatedBrands,
  pageStart,
  pageEnd,
} = useClientPagination(filteredBrands, {
  defaultPageSize: 5,
  pageSizeOptions: [5, 10],
})

const stats = computed(() => {
  const total = displayBrands.value.length
  const active = displayBrands.value.filter((brand) => brand?.status === 'active').length
  const inactive = total - active

  return {total, active, inactive}
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

const selectedBrand = computed(() => displayBrands.value.find((brand) => brand?.id === editingId.value) || null)

const loadBrands = async () => {
  loadingError.value = ''

  try {
    await brandStore.fetchAll()
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được danh sách thương hiệu.'
  }
}

const openCreateModal = () => {
  formError.value = ''
  clearFieldErrors()
  editingId.value = null
  showModal.value = true
}

const openEditModal = (brand) => {
  editingId.value = brand?.id ?? null
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
    await brandStore.update(brand.id, {status: nextStatus})
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
        <p class="subtitle">Quản lý thương hiệu, logo, loại thương hiệu, trạng thái hiển thị và mô tả trong cùng một màn
          hình.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="openCreateModal">
            <i class="bi bi-plus-lg"></i>
            Thêm thương hiệu
          </button>

          <button type="button" class="secondary-action" @click="search = ''; statusFilter = 'all'; typeFilter = 'all'">
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

      <select v-model="typeFilter" class="filter-select">
        <option value="all">Tất cả loại</option>
        <option value="phone">Điện thoại</option>
        <option value="accessory">Phụ kiện</option>
      </select>

      <div class="table-chip">
        <i class="bi bi-funnel"></i>
        <span>{{ filteredBrands.length }} kết quả</span>
      </div>
    </section>

    <div v-if="loadingError && !paginatedBrands.length" class="state-card error-state">
      <i class="bi bi-exclamation-triangle"></i>
      <p>{{ loadingError }}</p>
      <button type="button" class="secondary-action" @click="loadBrands">Thử lại</button>
    </div>

    <BrandTable
        v-else
        :brands="paginatedBrands"
        :loading="brandLoading"
        :deleting-id="deletingId"
        @edit="openEditModal"
        @delete="handleDelete"
        @toggle="handleToggleStatus"
    />

    <ListPaginationControls
        v-if="!loadingError"
        :current-page="currentPage"
        :total-pages="totalPages"
        :page-size="pageSize"
        :total-items="filteredBrands.length"
        :page-start="pageStart"
        :page-end="pageEnd"
        item-label="thương hiệu"
        @update:currentPage="currentPage = $event"
        @update:pageSize="pageSize = $event"
    />

    <BrandForm
        :visible="showModal"
        :brand="selectedBrand"
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

.hero-card {
  padding: 24px;
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 30%),
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
  display: grid;
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
  letter-spacing: 0.08em;
}

.subtitle {
  max-width: 760px;
  margin: 8px 0 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.7;
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

.state-card {
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

@media (max-width: 1199.98px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .hero-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
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
  .toolbar-card {
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
}
</style>
