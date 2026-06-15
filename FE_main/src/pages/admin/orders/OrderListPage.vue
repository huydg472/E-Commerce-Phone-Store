<script setup>
import {computed, onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import {storeToRefs} from 'pinia'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import OrderTable from '@/components/order/OrderTable.vue'
import {useOrderStore} from '@/stores/orderStore'
import {useDashboardStore} from '@/stores/dashboardStore'
import {useClientPagination} from '@/composables/useClientPagination.js'

const router = useRouter()
const orderStore = useOrderStore()
const dashboardStore = useDashboardStore()
const {items: orders, loading: orderLoading} = storeToRefs(orderStore)

const search = ref('')
const selectedStatus = ref('all')
const selectedPayment = ref('all')
const loadingError = ref('')

const normalize = (value) => String(value ?? '').trim().toLowerCase()
const toNumber = (value) => {
  const numericValue = Number(value)
  return Number.isFinite(numericValue) ? numericValue : 0
}

const orderStatusSteps = ['pending', 'confirmed', 'processing', 'shipping', 'completed']

const isSelectableOrderStatus = (currentStatus, targetStatus) => {
  if (currentStatus === targetStatus) {
    return true
  }

  if (targetStatus === 'cancelled') {
    return currentStatus !== 'completed' && currentStatus !== 'cancelled'
  }

  if (currentStatus === 'cancelled') {
    return targetStatus === 'pending'
  }

  const currentIndex = orderStatusSteps.indexOf(currentStatus)
  const targetIndex = orderStatusSteps.indexOf(targetStatus)

  if (currentIndex === -1 || targetIndex === -1) {
    return false
  }

  return Math.abs(targetIndex - currentIndex) === 1
}

const displayOrders = computed(() => {
  const source = Array.isArray(orders.value) ? orders.value : []

  return source.map((order) => {
    const items = Array.isArray(order?.orderItems) ? order.orderItems : Array.isArray(order?.order_items) ? order.order_items : []
    const firstItem = items[0] ?? null
    const variant = firstItem?.productVariant ?? firstItem?.product_variant ?? null
    const product = variant?.product ?? null

    return {
      raw: order,
      id: order.id,
      code: order.order_code || `#${order.id}`,
      customer: order.receiver_name || order.user?.name || 'Khách hàng',
      phone: order.receiver_phone || order.user?.phone || '',
      address: order.shipping_address_text || order.shippingAddress?.address_text || order.shippingAddress?.address || '',
      orderStatus: order.order_status || 'pending',
      paymentStatus: order.payment_status || order.payment?.payment_status || 'unpaid',
      paymentMethod: order.payment?.payment_method || 'cod',
      total: toNumber(order.total_amount),
      itemCount: items.reduce((sum, item) => sum + toNumber(item?.quantity), 0),
      orderedAt: new Date(order.ordered_at || order.created_at).toLocaleString('vi-VN'),
      updatedAt: new Date(order.updated_at || order.created_at).toLocaleString('vi-VN'),
      thumbnail: product?.thumbnail_url || product?.thumbnailUrl || product?.image || '/images/default-product.png',
      firstProductName: firstItem?.product_name || product?.name || 'Sản phẩm',
      firstVariantName: firstItem?.variant_name || '',
    }
  })
})

const filteredOrders = computed(() => {
  const query = normalize(search.value)

  return displayOrders.value.filter((order) => {
    const matchesStatus = selectedStatus.value === 'all' || order.orderStatus === selectedStatus.value
    const matchesPayment = selectedPayment.value === 'all' || order.paymentStatus === selectedPayment.value
    const matchesKeyword =
        !query ||
        [order.code, order.customer, order.phone, order.address, order.firstProductName, order.firstVariantName]
            .some((field) => normalize(field).includes(query))

    return matchesStatus && matchesPayment && matchesKeyword
  })
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems: paginatedOrders,
  pageStart,
  pageEnd,
} = useClientPagination(filteredOrders, {
  defaultPageSize: 5,
  pageSizeOptions: [5, 10],
})

const stats = computed(() => {
  const total = displayOrders.value.length
  const pending = displayOrders.value.filter((order) => order.orderStatus === 'pending').length
  const shipping = displayOrders.value.filter((order) => order.orderStatus === 'shipping').length
  const completed = displayOrders.value.filter((order) => order.orderStatus === 'completed').length

  return {total, pending, shipping, completed}
})

const loadOrders = async () => {
  loadingError.value = ''

  try {
    await orderStore.fetchAll()
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được danh sách đơn hàng.'
  }
}

const handleViewDetail = (order) => {
  router.push({name: 'admin.orders.show', params: {id: order.id}})
}

const handleStatusChange = async (order, nextStatus) => {
  if (!order || !nextStatus) return

  if (order.orderStatus === 'completed') {
    loadingError.value = 'Đơn hàng đã hoàn thành nên không thể thay đổi trạng thái.'
    return
  }

  if (!isSelectableOrderStatus(order.orderStatus, nextStatus)) {
    loadingError.value = 'Chỉ có thể chuyển trạng thái từng bước một.'
    return
  }

  const previousStatus = order.raw?.order_status
  if (order.raw) order.raw.order_status = nextStatus
  order.orderStatus = nextStatus
  loadingError.value = ''

  try {
    await orderStore.update(order.id, {order_status: nextStatus})
    await dashboardStore.fetchDashboard().catch(() => {})
  } catch (error) {
    if (order.raw) order.raw.order_status = previousStatus
    order.orderStatus = previousStatus
    loadingError.value = error.response?.data?.message || 'Không cập nhật được trạng thái đơn hàng.'
  }
}

onMounted(loadOrders)
</script>

<template>
  <div class="admin-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Quản lý đơn hàng</p>
        <h1>Danh sách đơn hàng</h1>
        <p class="subtitle">Theo dõi đơn hàng, trạng thái thanh toán, phương thức thanh toán và tiến độ xử lý trên một
          màn hình.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="loadOrders">
            <i class="bi bi-arrow-clockwise"></i>
            Tải lại
          </button>

          <button type="button" class="secondary-action"
                  @click="search = ''; selectedStatus = 'all'; selectedPayment = 'all'">
            <i class="bi bi-arrow-counterclockwise"></i>
            Xóa bộ lọc
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <article class="stat-card">
          <span class="stat-icon stat-icon-total"><i class="bi bi-receipt"></i></span>
          <div><strong>{{ stats.total }}</strong><span>Tổng đơn</span></div>
        </article>
        <article class="stat-card">
          <span class="stat-icon stat-icon-featured"><i class="bi bi-hourglass-split"></i></span>
          <div><strong>{{ stats.pending }}</strong><span>Chờ xác nhận</span></div>
        </article>
        <article class="stat-card">
          <span class="stat-icon stat-icon-total"><i class="bi bi-truck"></i></span>
          <div><strong>{{ stats.shipping }}</strong><span>Đang giao</span></div>
        </article>
        <article class="stat-card">
          <span class="stat-icon stat-icon-active"><i class="bi bi-check2-circle"></i></span>
          <div><strong>{{ stats.completed }}</strong><span>Hoàn thành</span></div>
        </article>
      </div>
    </section>

    <div class="toolbar">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model.trim="search" type="search"
               placeholder="Tìm theo mã đơn, khách hàng, số điện thoại, sản phẩm..."/>
      </div>

      <div class="filter-row">
        <select v-model="selectedStatus" class="filter-select">
          <option value="all">Tất cả trạng thái</option>
          <option value="pending">Chờ xác nhận</option>
          <option value="confirmed">Đã xác nhận</option>
          <option value="processing">Đang xử lý</option>
          <option value="shipping">Đang giao</option>
          <option value="completed">Hoàn thành</option>
          <option value="cancelled">Đã hủy</option>
        </select>

        <select v-model="selectedPayment" class="filter-select">
          <option value="all">Tất cả thanh toán</option>
          <option value="unpaid">Chưa thanh toán</option>
          <option value="pending">Chờ thanh toán</option>
          <option value="paid">Đã thanh toán</option>
          <option value="failed">Thất bại</option>
          <option value="refunded">Đã hoàn tiền</option>
        </select>
      </div>
    </div>

    <div v-if="orderLoading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải danh sách đơn hàng...</p>
    </div>

    <div v-else-if="loadingError" class="state-card error-state">
      <i class="bi bi-exclamation-triangle"></i>
      <p>{{ loadingError }}</p>
      <button type="button" class="secondary-action" @click="loadOrders">Thử lại</button>
    </div>

    <OrderTable
        v-else
        :orders="paginatedOrders"
        @view="handleViewDetail"
        @status-change="handleStatusChange"
    />

    <ListPaginationControls
        v-if="!orderLoading && !loadingError"
        :current-page="currentPage"
        :total-pages="totalPages"
        :page-size="pageSize"
        :total-items="filteredOrders.length"
        :page-start="pageStart"
        :page-end="pageEnd"
        item-label="đơn hàng"
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
  margin: 8px 0 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.7;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 18px;
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
  color: #ffffff;
  background: #2563eb;
}

.secondary-action {
  color: #334155;
  background: #ffffff;
  border-color: #dbe3ef;
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
  border-radius: 16px;
  color: #ffffff;
  font-size: 18px;
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

.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 0;
  max-width: 620px;
  height: 46px;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
}

.search-box i {
  color: #94a3b8;
}

.search-box input {
  width: 100%;
  border: 0;
  outline: none;
  background: transparent;
  color: #0f172a;
  font-size: 14px;
  font-weight: 500;
  line-height: 1.2;
}

.filter-row {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
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

.state-card {
  min-height: 240px;
  border: 1px solid #e5eaf3;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
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

@media (max-width: 1199.98px) {
  .hero-card {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 767.98px) {
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
  .primary-action,
  .secondary-action {
    width: 100%;
    max-width: none;
  }

  .toolbar {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
