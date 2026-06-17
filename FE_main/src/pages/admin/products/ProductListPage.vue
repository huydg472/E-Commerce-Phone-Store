<script setup>
import {computed, onMounted, ref} from 'vue'
import {storeToRefs} from 'pinia'
import {useRouter} from 'vue-router'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import {useProductStore} from '@/stores/productStore.js'
import {useBrandStore} from '@/stores/brandStore.js'
import {useCategoryStore} from '@/stores/categoryStore.js'
import {useClientPagination} from '@/composables/useClientPagination.js'
import {formatDate} from '@/utils/formatDate.js'

const router = useRouter()
const productStore = useProductStore()
const brandStore = useBrandStore()
const categoryStore = useCategoryStore()

const {items: productItems, loading: productLoading} = storeToRefs(productStore)
const {items: brands} = storeToRefs(brandStore)
const {items: categories} = storeToRefs(categoryStore)

const search = ref('')
const selectedStatus = ref('')
const selectedBrand = ref('')
const selectedCategory = ref('')
const loadingError = ref('')
const deletingId = ref(null)

const normalize = (value) => {
  return String(value ?? '')
      .trim()
      .toLowerCase()
}

const displayProducts = computed(() => (Array.isArray(productItems.value) ? productItems.value : []))

const filteredProducts = computed(() => {
  const query = normalize(search.value)

  return displayProducts.value.filter((product) => {
    const name = normalize(product?.name)
    const slug = normalize(product?.slug)
    const brandName = normalize(product?.brand?.name)
    const categoryName = normalize(product?.category?.name)
    const description = normalize(product?.short_description)

    const matchesSearch =
        !query ||
        [name, slug, brandName, categoryName, description].some((field) => field.includes(query))

    const matchesStatus = !selectedStatus.value || product?.status === selectedStatus.value
    const matchesBrand = !selectedBrand.value || String(product?.brand_id) === String(selectedBrand.value)
    const matchesCategory =
        !selectedCategory.value || String(product?.category_id) === String(selectedCategory.value)

    return matchesSearch && matchesStatus && matchesBrand && matchesCategory
  })
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems: paginatedProducts,
  pageStart,
  pageEnd,
} = useClientPagination(filteredProducts, {
  defaultPageSize: 5,
  pageSizeOptions: [5, 10],
})

const stats = computed(() => {
  const total = displayProducts.value.length
  const active = displayProducts.value.filter((product) => product?.status === 'active').length
  const inactive = total - active

  return {total, active, inactive}
})

const isLoading = computed(() => productLoading.value && !displayProducts.value.length)

const productCountLabel = computed(() => {
  if (!filteredProducts.value.length) {
    return 'Không có sản phẩm phù hợp.'
  }

  return `Đang hiển thị ${filteredProducts.value.length} sản phẩm`
})

const resetFilters = () => {
  search.value = ''
  selectedStatus.value = ''
  selectedBrand.value = ''
  selectedCategory.value = ''
}

const refreshProducts = async () => {
  loadingError.value = ''

  try {
    await productStore.fetchAll({
      per_page: 1000,
      sort: 'id_asc',
    })
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được danh sách sản phẩm.'
  }
}

const loadAuxiliaryData = async () => {
  try {
    await Promise.all([
      brandStore.fetchAll(),
      categoryStore.fetchAll(),
    ])
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được dữ liệu bộ lọc.'
  }
}

const handleToggleStatus = async (product) => {
  const nextStatus = product?.status === 'active' ? 'inactive' : 'active'
  const previousStatus = product?.status

  product.status = nextStatus
  loadingError.value = ''

  try {
    await productStore.update(product.id, {status: nextStatus})

    const matchedProduct = displayProducts.value.find((item) => item.id === product.id)
    if (matchedProduct) {
      matchedProduct.status = nextStatus
    }
  } catch (error) {
    product.status = previousStatus
    loadingError.value = error.response?.data?.message || 'Không cập nhật được trạng thái sản phẩm.'
  }
}

const handleDelete = async (product) => {
  if (!product || deletingId.value) {
    return
  }

  if (!window.confirm(`Xóa sản phẩm "${product.name}"?`)) {
    return
  }

  deletingId.value = product.id
  loadingError.value = ''

  try {
    await productStore.remove(product.id)
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không xóa được sản phẩm.'
  } finally {
    deletingId.value = null
  }
}

const openDetail = (product) => {
  router.push({name: 'admin.products.show', params: {id: product.id}})
}

const openEdit = (product) => {
  router.push({name: 'admin.products.edit', params: {id: product.id}})
}

onMounted(async () => {
  await Promise.all([refreshProducts(), loadAuxiliaryData()])
})
</script>

<template>
  <div class="admin-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Quản lý sản phẩm</p>
        <h1>Danh sách sản phẩm</h1>
        <p class="subtitle">
          Quản lý toàn bộ sản phẩm trong hệ thống, theo dõi trạng thái, phân loại và cập nhật nhanh ngay trên một màn
          hình.
        </p>

        <div class="hero-actions">
          <RouterLink to="/admin/products/create" class="primary-action">
            <i class="bi bi-plus-lg"></i>
            Thêm sản phẩm
          </RouterLink>

          <button type="button" class="secondary-action" @click="resetFilters">
            <i class="bi bi-arrow-counterclockwise"></i>
            Xóa bộ lọc
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <article class="stat-card">
          <span class="stat-icon stat-icon-total">
            <i class="bi bi-box-seam"></i>
          </span>
          <div>
            <strong>{{ stats.total }}</strong>
            <span>Tổng sản phẩm</span>
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
        <input v-model.trim="search" type="search" placeholder="Tìm theo tên, slug, thương hiệu, danh mục..."/>
      </div>

      <div class="filter-row">
        <select v-model="selectedStatus" class="filter-select">
          <option value="">Tất cả trạng thái</option>
          <option value="active">Đang hoạt động</option>
          <option value="inactive">Tạm ẩn</option>
        </select>

        <select v-model="selectedBrand" class="filter-select">
          <option value="">Tất cả thương hiệu</option>
          <option v-for="brand in brands" :key="brand.id" :value="String(brand.id)">
            {{ brand.name }}
          </option>
        </select>

        <select v-model="selectedCategory" class="filter-select">
          <option value="">Tất cả danh mục</option>
          <option v-for="category in categories" :key="category.id" :value="String(category.id)">
            {{ category.name }}
          </option>
        </select>
      </div>
    </section>

    <section v-if="loadingError && !displayProducts.length" class="state-card error-state">
      <i class="bi bi-exclamation-triangle"></i>
      <p>{{ loadingError }}</p>
      <button type="button" class="secondary-action" @click="refreshProducts">Thử lại</button>
    </section>

    <section v-else-if="isLoading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải danh sách sản phẩm...</p>
    </section>

    <section v-else class="table-card">
      <div class="table-header">
        <div>
          <h2>Kho sản phẩm</h2>
          <p>{{ productCountLabel }}</p>
        </div>
        <div class="table-chip">
          <i class="bi bi-funnel"></i>
          <span>{{ filteredProducts.length }} kết quả</span>
        </div>
      </div>

      <div v-if="loadingError" class="inline-alert">
        <i class="bi bi-exclamation-circle"></i>
        <span>{{ loadingError }}</span>
      </div>

      <div class="table-responsive">
        <table class="table align-middle admin-table mb-0">
          <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>Thương hiệu</th>
            <th>Danh mục</th>
            <th>Trạng thái</th>
            <th>Cập nhật</th>
            <th>Thao tác</th>
          </tr>
          </thead>

          <tbody>
          <tr v-for="product in paginatedProducts" :key="product.id">
            <td>
              <div class="product-cell">
                <div class="product-thumb">
                  <img
                      :src="product.thumbnail_url || '/images/default-product.png'"
                      :alt="product.name"
                  />
                </div>
                <div class="product-meta">
                  <strong>{{ product.name }}</strong>
                  <span>{{ product.slug }}</span>
                </div>
              </div>
            </td>
            <td>
              <div class="info-stack">
                <strong>{{ product.brand?.name || 'Chưa có' }}</strong>
                <span>{{ product.brand?.slug || ' ' }}</span>
              </div>
            </td>
            <td>
              <div class="info-stack">
                <strong>{{ product.category?.name || 'Chưa có' }}</strong>
                <span>{{ product.category?.slug || ' ' }}</span>
              </div>
            </td>
            <td>
              <button
                  type="button"
                  class="status-pill"
                  :class="product.status === 'active' ? 'is-active' : 'is-inactive'"
                  @click="handleToggleStatus(product)"
                  :title="product.status === 'active' ? 'Hoạt động' : 'Tạm ẩn'"
                  :aria-label="product.status === 'active' ? 'Hoạt động' : 'Tạm ẩn'"
              >
                <i :class="product.status === 'active' ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
              </button>
            </td>
            <td>{{ formatDate(product.updated_at || product.created_at) }}</td>
            <td>
              <div class="action-group">
                <button type="button" class="action-btn action-view" title="Xem chi tiết" @click="openDetail(product)">
                  <i class="bi bi-eye"></i>
                </button>
                <button type="button" class="action-btn action-edit" title="Chỉnh sửa" @click="openEdit(product)">
                  <i class="bi bi-pencil"></i>
                </button>
                <button
                    type="button"
                    class="action-btn action-delete"
                    title="Xóa"
                    :disabled="deletingId === product.id"
                    @click="handleDelete(product)"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="!filteredProducts.length">
            <td colspan="6">
              <div class="empty-state">
                <i class="bi bi-box"></i>
                <p>Không có sản phẩm phù hợp với bộ lọc hiện tại.</p>
                <button type="button" class="secondary-action" @click="resetFilters">Xóa bộ lọc</button>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

    </section>

    <ListPaginationControls
        :current-page="currentPage"
        :total-pages="totalPages"
        :page-size="pageSize"
        :total-items="filteredProducts.length"
        :page-start="pageStart"
        :page-end="pageEnd"
        item-label="sản phẩm"
        @update:currentPage="currentPage = $event"
        @update:pageSize="pageSize = $event"
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
  grid-template-columns: repeat(3, minmax(0, 1fr));
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

.stat-icon-inactive {
  background: linear-gradient(135deg, #64748b 0%, #475569 100%);
}

.toolbar-card {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.search-box {
  height: 46px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 10px;
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

.filter-row {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
}

.filter-select {
  min-height: 46px;
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
  width: 100%;
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
  width: 100%;
  overflow-x: auto;
}

.admin-table {
  min-width: 1140px;
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

.product-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.product-thumb {
  width: 54px;
  height: 54px;
  border-radius: 14px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  flex-shrink: 0;
}

.product-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-meta,
.info-stack {
  display: flex;
  flex-direction: column;
  gap: 2px;
  min-width: 0;
}

.product-meta strong,
.info-stack strong {
  color: #0f172a;
  font-weight: 800;
  line-height: 1.35;
  white-space: normal;
}

.product-meta span,
.info-stack span {
  color: #64748b;
  font-size: 12px;
  line-height: 1.3;
  display: block;
  white-space: normal;
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
  justify-content: flex-start;
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

.action-view {
  background: #eff6ff;
  color: #2563eb;
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

@media (max-width: 1200px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .filter-row {
    grid-template-columns: repeat(2, minmax(0, 1fr));
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

  .filter-row {
    grid-template-columns: 1fr;
  }
}
</style>
