<script setup>
import {computed, onMounted, ref} from 'vue'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import {orderService} from '@/services/orderService'
import {productService} from '@/services/productService'
import {formatDate} from '@/utils/formatDate'
import {formatMoney, toNumber, unwrapList} from '@/utils/reportHelpers'
import {useClientPagination} from '@/composables/useClientPagination.js'

const loading = ref(true)
const errorMessage = ref('')
const products = ref([])
const orders = ref([])

const unwrapOrderItems = (order) => {
  const sources = [order?.orderItems, order?.order_items, order?.items]

  for (const source of sources) {
    if (Array.isArray(source) && source.length > 0) {
      return source
    }
  }

  return []
}

const getVariants = (product) => {
  return product?.productVariants || product?.product_variants || []
}

const getVariantFirstImage = (variant) => {
  const firstImage =
      variant?.productVariantImages?.[0] ??
      variant?.product_variant_images?.[0] ??
      variant?.images?.[0] ??
      null

  return (
    firstImage?.image_url ||
    firstImage?.imageUrl ||
    firstImage?.url ||
    firstImage?.image ||
    firstImage?.image_path ||
    variant?.thumbnail_url ||
    variant?.thumbnailUrl ||
    variant?.image ||
    ''
  )
}

const getVariantLabel = (variant) => {
  const parts = [variant?.color, variant?.storage, variant?.ram]
      .map((value) => String(value ?? '').trim())
      .filter(Boolean)

  return parts.length ? parts.join(' · ') : variant?.sku || 'Biến thể'
}

const variantRows = computed(() => {
  return products.value.flatMap((product) => {
    return getVariants(product).map((variant) => ({
      id: variant.id,
      productId: product.id,
      productName: product.name,
      brandName: product.brand?.name || 'Chưa có',
      categoryName: product.category?.name || 'Chưa có',
      variantLabel: getVariantLabel(variant),
      sku: variant.sku || 'N/A',
      stock: toNumber(variant.quantity),
      status: variant.status || 'inactive',
      updatedAt: variant.updated_at || variant.created_at || product.updated_at || product.created_at,
      image: getVariantFirstImage(variant) || product.thumbnail_url || product.thumbnailUrl || product.image || '/images/default-product.png',
    }))
  })
})

const lowStockVariants = computed(() =>
  variantRows.value
      .filter((variant) => variant.stock <= 5)
      .sort((left, right) => left.stock - right.stock),
)

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems: paginatedLowStockVariants,
  pageStart,
  pageEnd,
} = useClientPagination(lowStockVariants, {
  defaultPageSize: 5,
  pageSizeOptions: [5, 10],
})

const topSellingVariants = computed(() => {
  const salesMap = new Map()
  const variantMap = new Map()

  variantRows.value.forEach((variant) => {
    variantMap.set(variant.id, variant)
  })

  orders.value
      .filter((order) => order?.order_status === 'completed')
      .forEach((order) => {
        unwrapOrderItems(order).forEach((item) => {
          const variantId = Number(item?.product_variant_id ?? item?.productVariant?.id ?? item?.product_variant?.id)
          const key = Number.isInteger(variantId) && variantId > 0
              ? variantId
              : `${item?.product_name || item?.name || 'product'}-${item?.variant_name || item?.variantName || ''}`

          const current = salesMap.get(key) || {
            productName: item?.product_name || item?.product?.name || item?.productVariant?.product?.name || 'Sản phẩm',
            variantLabel: item?.variant_name || item?.variantName || getVariantLabel(item?.productVariant || item?.product_variant || item?.variant || {}),
            sold: 0,
            revenue: 0,
            stock: variantMap.get(variantId)?.stock ?? 0,
          }

          current.sold += toNumber(item?.quantity)
          current.revenue += toNumber(item?.total_price)
          salesMap.set(key, current)
        })
      })

  return [...salesMap.values()]
      .sort((left, right) => right.revenue - left.revenue)
      .slice(0, 8)
})

const categoryStats = computed(() => {
  const map = new Map()

  products.value.forEach((product) => {
    const key = product?.category?.name || 'Chưa phân loại'
    map.set(key, (map.get(key) || 0) + 1)
  })

  return [...map.entries()]
      .map(([label, count]) => ({label, count}))
      .sort((left, right) => right.count - left.count)
})

const stats = computed(() => [
  {label: 'Sản phẩm', value: products.value.length, desc: 'Tổng danh mục sản phẩm', icon: 'bi bi-box-seam', tone: 'blue'},
  {label: 'Đang bán', value: products.value.filter((product) => product.status === 'active').length, desc: 'Sản phẩm hoạt động', icon: 'bi bi-check2-circle', tone: 'green'},
  {label: 'Nổi bật', value: products.value.filter((product) => product.is_featured).length, desc: 'Sản phẩm featured', icon: 'bi bi-stars', tone: 'orange'},
  {label: 'Biến thể', value: variantRows.value.length, desc: 'Tổng biến thể hiện có', icon: 'bi bi-layers', tone: 'slate'},
])

const loadReport = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const [productsResponse, ordersResponse] = await Promise.all([
      productService.getAll({per_page: 1000, sort: 'id_asc'}),
      orderService.getAll({per_page: 1000}),
    ])

    products.value = unwrapList(productsResponse)
    orders.value = unwrapList(ordersResponse)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không tải được báo cáo sản phẩm.'
  } finally {
    loading.value = false
  }
}

onMounted(loadReport)
</script>

<template>
  <div class="report-page">
    <section class="page-head">
      <div>
        <p class="eyebrow">Báo cáo</p>
        <h1>Báo cáo sản phẩm</h1>
        <p class="subtitle">Theo dõi tình trạng sản phẩm, biến thể và các biến thể sắp hết hàng.</p>
      </div>

      <div class="page-actions">
        <button type="button" class="secondary-action" @click="loadReport">
          <i class="bi bi-arrow-clockwise"></i>
          Làm mới
        </button>
      </div>
    </section>

    <section v-if="errorMessage" class="notice-card error">
      <i class="bi bi-exclamation-triangle"></i>
      <span>{{ errorMessage }}</span>
    </section>

    <section v-else-if="loading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải báo cáo sản phẩm...</p>
    </section>

    <template v-else>
      <section class="stats-grid">
        <article v-for="item in stats" :key="item.label" class="stat-card">
          <span class="stat-icon" :class="`tone-${item.tone}`">
            <i :class="item.icon"></i>
          </span>
          <div>
            <strong>{{ item.value }}</strong>
            <span>{{ item.label }}</span>
            <small>{{ item.desc }}</small>
          </div>
        </article>
      </section>

      <section class="content-grid">
        <article class="panel">
          <div class="panel-head">
            <div>
              <h2>Top sản phẩm bán chạy</h2>
              <p>Tổng hợp từ các đơn đã hoàn tất.</p>
            </div>
          </div>

          <div v-if="topSellingVariants.length" class="top-seller-list">
            <article v-for="item in topSellingVariants" :key="item.productName + item.variantLabel" class="seller-item">
              <img :src="item.image" :alt="item.productName" />
              <div class="seller-main">
                <strong>{{ item.productName }}</strong>
                <span>Phiên bản: {{ item.variantLabel }}</span>
                <small>Kho còn {{ item.stock }}</small>
              </div>
              <div class="seller-meta">
                <strong>{{ item.sold }}</strong>
                <span>{{ formatMoney(item.revenue) }}</span>
              </div>
            </article>
          </div>

          <div v-else class="empty-state">
            <i class="bi bi-box"></i>
            <p>Chưa có dữ liệu bán hàng để thống kê.</p>
          </div>
        </article>

        <article class="panel">
          <div class="panel-head">
            <div>
              <h2>Danh mục sản phẩm</h2>
              <p>Số lượng sản phẩm theo danh mục.</p>
            </div>
          </div>

          <div class="category-list">
            <div v-for="item in categoryStats" :key="item.label" class="category-row">
              <div>
                <strong>{{ item.label }}</strong>
                <small>{{ item.count }} sản phẩm</small>
              </div>
              <div class="category-badge">{{ item.count }}</div>
            </div>
          </div>
        </article>
      </section>

      <section class="panel">
        <div class="panel-head">
          <div>
            <h2>Biến thể sắp hết hàng</h2>
            <p>Ưu tiên nhập thêm các biến thể có số lượng nhỏ hơn hoặc bằng 5.</p>
          </div>
        </div>

        <div class="table-wrap">
          <table class="report-table">
            <thead>
            <tr>
              <th>Sản phẩm</th>
              <th>Biến thể</th>
              <th>SKU</th>
              <th>Tồn kho</th>
              <th>Trạng thái</th>
              <th>Cập nhật</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="variant in paginatedLowStockVariants" :key="variant.id">
              <td>
                <div class="product-cell">
                  <img :src="variant.image" :alt="variant.productName" />
                  <div>
                    <strong>{{ variant.productName }}</strong>
                    <small>{{ variant.brandName }} · {{ variant.categoryName }}</small>
                  </div>
                </div>
              </td>
              <td>{{ variant.variantLabel }}</td>
              <td>{{ variant.sku }}</td>
              <td>
                <span class="stock-pill" :class="{ low: variant.stock <= 5 }">{{ variant.stock }}</span>
              </td>
              <td>
                <span class="status-pill" :class="variant.status === 'active' ? 'active' : 'inactive'">
                  {{ variant.status === 'active' ? 'Đang bán' : 'Tạm ẩn' }}
                </span>
              </td>
              <td>{{ formatDate(variant.updatedAt) }}</td>
            </tr>

            <tr v-if="!lowStockVariants.length">
              <td colspan="6">
                <div class="empty-state">
                  <i class="bi bi-check2-circle"></i>
                  <p>Không có biến thể nào đang ở mức tồn kho thấp.</p>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <ListPaginationControls
            v-if="lowStockVariants.length"
            :current-page="currentPage"
            :total-pages="totalPages"
            :page-size="pageSize"
            :total-items="lowStockVariants.length"
            :page-start="pageStart"
            :page-end="pageEnd"
            item-label="biến thể"
            @update:currentPage="currentPage = $event"
            @update:pageSize="pageSize = $event"
        />
      </section>
    </template>
  </div>
</template>

<style scoped>
.report-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.page-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.page-head h1 {
  margin: 0;
  color: #0f172a;
  font-size: 30px;
  font-weight: 900;
}

.subtitle {
  margin: 8px 0 0;
  color: #64748b;
  font-size: 14px;
}

.page-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.secondary-action {
  min-height: 44px;
  padding: 0 14px;
  border-radius: 12px;
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
}

.notice-card,
.state-card,
.panel,
.stat-card {
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.notice-card {
  min-height: 56px;
  padding: 0 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: #b91c1c;
}

.notice-card.error {
  background: #fff7f7;
  border-color: #fecaca;
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

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
}

.stat-card {
  min-height: 116px;
  padding: 16px;
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

.stat-card span {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.stat-card small {
  display: block;
  margin-top: 4px;
  color: #94a3b8;
  font-size: 12px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  color: #ffffff;
  font-size: 20px;
  flex-shrink: 0;
}

.tone-blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
.tone-green { background: linear-gradient(135deg, #16a34a, #22c55e); }
.tone-orange { background: linear-gradient(135deg, #f59e0b, #fb923c); }
.tone-slate { background: linear-gradient(135deg, #475569, #64748b); }

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 0.95fr);
  gap: 18px;
}

.panel {
  padding: 20px;
}

.panel-head {
  margin-bottom: 16px;
}

.panel-head h2 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
}

.panel-head p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 13px;
}

.top-seller-list,
.category-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.seller-item,
.category-row {
  padding: 14px 16px;
  border: 1px solid #eef2f7;
  border-radius: 16px;
  display: flex;
  align-items: center;
  gap: 12px;
  justify-content: space-between;
}

.seller-item img,
.product-cell img {
  width: 56px;
  height: 56px;
  border-radius: 14px;
  object-fit: cover;
  background: #f3f4f6;
  flex-shrink: 0;
}

.seller-main,
.product-cell div {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 3px;
  flex: 1;
}

.seller-main strong,
.product-cell strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 900;
}

.seller-main span,
.seller-main small,
.product-cell small {
  color: #64748b;
  font-size: 12px;
}

.seller-meta {
  text-align: right;
}

.seller-meta strong {
  display: block;
  color: #0f172a;
  font-size: 16px;
  font-weight: 900;
}

.seller-meta span {
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
}

.category-row strong {
  display: block;
  color: #0f172a;
  font-size: 14px;
  font-weight: 900;
}

.category-row small {
  color: #64748b;
  font-size: 12px;
}

.category-badge {
  min-width: 42px;
  min-height: 32px;
  padding: 0 12px;
  border-radius: 999px;
  background: #eff6ff;
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 900;
}

.table-wrap {
  overflow-x: auto;
}

.report-table {
  width: 100%;
  border-collapse: collapse;
}

.report-table th,
.report-table td {
  padding: 14px 0;
  border-bottom: 1px solid #eef2f7;
  text-align: left;
}

.report-table th {
  color: #64748b;
  font-size: 13px;
  font-weight: 800;
}

.report-table td {
  color: #0f172a;
  font-size: 14px;
  font-weight: 600;
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.stock-pill,
.status-pill {
  min-height: 30px;
  padding: 0 12px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 900;
}

.stock-pill {
  background: #ecfdf5;
  color: #15803d;
}

.stock-pill.low {
  background: #fff7ed;
  color: #c2410c;
}

.status-pill.active {
  background: #ecfdf5;
  color: #15803d;
}

.status-pill.inactive {
  background: #f1f5f9;
  color: #64748b;
}

.empty-state {
  min-height: 160px;
  display: grid;
  place-items: center;
  gap: 10px;
  color: #64748b;
  text-align: center;
}

.empty-state i {
  font-size: 34px;
  color: #2563eb;
}

@media (max-width: 1200px) {
  .content-grid,
  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 768px) {
  .page-head {
    flex-direction: column;
  }

  .content-grid,
  .stats-grid {
    grid-template-columns: 1fr;
  }
}
</style>
