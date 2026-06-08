<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useOrderStore } from '@/stores/orderStore'
import { formatCurrency } from '@/utils/formatCurrency'
import { formatDate } from '@/utils/formatDate'

const router = useRouter()
const orderStore = useOrderStore()
const { items: orders, loading: orderLoading } = storeToRefs(orderStore)

const search = ref('')
const selectedStatus = ref('all')
const selectedPayment = ref('all')
const loadingError = ref('')

const orderStatusMap = {
  pending: { label: 'Chờ xác nhận', className: 'pending' },
  confirmed: { label: 'Đã xác nhận', className: 'confirmed' },
  processing: { label: 'Đang xử lý', className: 'processing' },
  shipping: { label: 'Đang giao', className: 'shipping' },
  completed: { label: 'Hoàn thành', className: 'completed' },
  cancelled: { label: 'Đã hủy', className: 'cancelled' },
}

const paymentStatusMap = {
  unpaid: { label: 'Chưa thanh toán', className: 'unpaid' },
  pending: { label: 'Chờ thanh toán', className: 'pending' },
  paid: { label: 'Đã thanh toán', className: 'paid' },
  failed: { label: 'Thất bại', className: 'failed' },
  refunded: { label: 'Đã hoàn tiền', className: 'refunded' },
}

const paymentMethodMap = {
  cod: 'COD',
  bank_transfer: 'Chuyển khoản',
  momo: 'MoMo',
  vnpay: 'VNPay',
}

const normalize = (value) => String(value ?? '').trim().toLowerCase()

const toNumber = (value) => {
  const numericValue = Number(value)
  return Number.isFinite(numericValue) ? numericValue : 0
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
      subtotal: toNumber(order.subtotal),
      shippingFee: toNumber(order.shipping_fee),
      discountAmount: toNumber(order.discount_amount),
      itemCount: items.reduce((sum, item) => sum + toNumber(item?.quantity), 0),
      orderedAt: formatDate(order.ordered_at || order.created_at),
      updatedAt: formatDate(order.updated_at || order.created_at),
      thumbnail:
        product?.thumbnail_url ||
        product?.thumbnailUrl ||
        product?.image ||
        '/images/default-product.png',
      firstProductName: firstItem?.product_name || product?.name || 'Sản phẩm',
      firstVariantName: firstItem?.variant_name || '',
      items,
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

const stats = computed(() => {
  const total = displayOrders.value.length
  const pending = displayOrders.value.filter((order) => order.orderStatus === 'pending').length
  const shipping = displayOrders.value.filter((order) => order.orderStatus === 'shipping').length
  const completed = displayOrders.value.filter((order) => order.orderStatus === 'completed').length

  return { total, pending, shipping, completed }
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
  router.push({ name: 'admin.orders.show', params: { id: order.id } })
}

const handleStatusChange = async (order, nextStatus) => {
  if (!order || !nextStatus) {
    return
  }

  const previousStatus = order.raw?.order_status
  if (order.raw) {
    order.raw.order_status = nextStatus
  }
  order.orderStatus = nextStatus
  loadingError.value = ''

  try {
    await orderStore.update(order.id, {
      order_status: nextStatus,
    })
  } catch (error) {
    if (order.raw) {
      order.raw.order_status = previousStatus
    }
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
        <p class="subtitle">Theo dõi đơn hàng, trạng thái thanh toán, phương thức thanh toán và tiến độ xử lý trên một màn hình.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="loadOrders">
            <i class="bi bi-arrow-clockwise"></i>
            Tải lại
          </button>

          <button type="button" class="secondary-action" @click="search = ''; selectedStatus = 'all'; selectedPayment = 'all'">
            <i class="bi bi-arrow-counterclockwise"></i>
            Xóa bộ lọc
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <article class="stat-card">
          <span class="stat-icon stat-icon-total">
            <i class="bi bi-receipt"></i>
          </span>
          <div>
            <strong>{{ stats.total }}</strong>
            <span>Tổng đơn</span>
          </div>
        </article>

        <article class="stat-card">
          <span class="stat-icon stat-icon-featured">
            <i class="bi bi-hourglass-split"></i>
          </span>
          <div>
            <strong>{{ stats.pending }}</strong>
            <span>Chờ xác nhận</span>
          </div>
        </article>

        <article class="stat-card">
          <span class="stat-icon stat-icon-total">
            <i class="bi bi-truck"></i>
          </span>
          <div>
            <strong>{{ stats.shipping }}</strong>
            <span>Đang giao</span>
          </div>
        </article>

        <article class="stat-card">
          <span class="stat-icon stat-icon-active">
            <i class="bi bi-check2-circle"></i>
          </span>
          <div>
            <strong>{{ stats.completed }}</strong>
            <span>Hoàn thành</span>
          </div>
        </article>
      </div>
    </section>

    <div class="toolbar">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input
          v-model.trim="search"
          type="search"
          placeholder="Tìm theo mã đơn, khách hàng, số điện thoại, sản phẩm..."
        />
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

    <div v-else class="table-card">
      <div class="table-responsive">
        <table class="table align-middle admin-table mb-0">
          <colgroup>
            <col class="col-order-code" />
            <col class="col-customer" />
            <col class="col-product" />
            <col class="col-total" />
            <col class="col-payment-status" />
            <col class="col-payment-method" />
            <col class="col-order-status" />
            <col class="col-date" />
            <col class="col-actions" />
          </colgroup>
          <thead>
            <tr>
              <th>Mã đơn</th>
              <th>Khách hàng</th>
              <th>Sản phẩm</th>
              <th class="text-end">Tổng tiền</th>
              <th>Trạng thái thanh toán</th>
              <th>Phương thức</th>
              <th>Trạng thái</th>
              <th>Ngày đặt</th>
              <th class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in filteredOrders" :key="order.id">
              <td>
                <div class="order-code-cell">
                  <strong>{{ order.code }}</strong>
                  <small>{{ order.itemCount }} sản phẩm</small>
                </div>
              </td>
              <td>
                <div class="customer-cell">
                  <strong>{{ order.customer }}</strong>
                  <small>{{ order.phone || 'Chưa có số điện thoại' }}</small>
                </div>
              </td>
              <td>
                <div class="product-cell">
                  <img :src="order.thumbnail" :alt="order.firstProductName" />
                  <div class="product-meta">
                    <strong>{{ order.firstProductName }}</strong>
                    <small v-if="order.firstVariantName">Phiên bản: {{ order.firstVariantName }}</small>
                    <small v-else>Chưa có chi tiết biến thể</small>
                  </div>
                </div>
              </td>
              <td class="text-end fw-semibold">{{ formatCurrency(order.total) }}</td>
              <td>
                <div class="payment-stack">
                  <span class="payment-pill" :class="paymentStatusMap[order.paymentStatus]?.className || 'unpaid'">
                    {{ paymentStatusMap[order.paymentStatus]?.label || order.paymentStatus }}
                  </span>
                </div>
              </td>
              <td>
                <span class="payment-method">{{ paymentMethodMap[order.paymentMethod] || order.paymentMethod }}</span>
              </td>
              <td>
                <select
                  :value="order.orderStatus"
                  class="status-select"
                  :class="orderStatusMap[order.orderStatus]?.className || 'pending'"
                  @change="handleStatusChange(order, $event.target.value)"
                >
                  <option value="pending">Chờ xác nhận</option>
                  <option value="confirmed">Đã xác nhận</option>
                  <option value="processing">Đang xử lý</option>
                  <option value="shipping">Đang giao</option>
                  <option value="completed">Hoàn thành</option>
                  <option value="cancelled">Đã hủy</option>
                </select>
              </td>
              <td>
                <div class="date-stack">
                  <strong>{{ order.orderedAt }}</strong>
                  <small>Cập nhật {{ order.updatedAt }}</small>
                </div>
              </td>
              <td>
                <div class="action-group">
                  <button type="button" class="action-btn action-view" @click="handleViewDetail(order)" title="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!filteredOrders.length">
              <td colspan="9">
                <div class="empty-state">
                  <i class="bi bi-bag-x"></i>
                  <p>Không có đơn hàng phù hợp.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
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
  border-radius: 16px;
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

.stat-icon-featured {
  background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
}

.primary-action,
.secondary-action {
  min-height: 44px;
  padding: 0 16px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1px solid transparent;
  font-size: 14px;
  font-weight: 800;
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

.toolbar {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: center;
}

.search-box {
  flex: 1;
  min-width: 0;
  max-width: 620px;
  height: 42px;
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
}

.filter-row {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.filter-select {
  min-width: 188px;
  height: 42px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
  color: #0f172a;
  font-weight: 600;
}

.state-card,
.table-card {
  border: 1px solid #e5e9f1;
  border-radius: 16px;
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
  min-width: 1360px;
  table-layout: fixed;
}

.col-order-code {
  width: 9%;
}

.col-customer {
  width: 12%;
}

.col-product {
  width: 25%;
}

.col-total {
  width: 9%;
}

.col-payment-status {
  width: 13%;
}

.col-payment-method {
  width: 9%;
}

.col-order-status {
  width: 10%;
}

.col-date {
  width: 8%;
}

.col-actions {
  width: 5%;
}

.admin-table thead th {
  height: 50px;
  padding-left: 10px;
  padding-right: 10px;
  color: #0f172a;
  background: #f8fbff;
  border-bottom: 1px solid #edf0f5;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
  text-align: left;
}

.admin-table tbody td {
  height: 74px;
  padding-left: 10px;
  padding-right: 10px;
  color: #0f172a;
  border-bottom: 1px solid #edf0f5;
  font-size: 14px;
  white-space: normal;
  vertical-align: middle;
  overflow: hidden;
}

.admin-table thead th.text-end,
.admin-table tbody td.text-end {
  text-align: right;
}

.admin-table thead th.text-center,
.admin-table tbody td.text-center {
  text-align: center;
}

.admin-table tbody tr:last-child td {
  border-bottom: 0;
}

.admin-table th:first-child,
.admin-table td:first-child {
  padding-left: 16px;
}

.admin-table th:last-child,
.admin-table td:last-child {
  padding-right: 16px;
}

.order-code-cell,
.customer-cell,
.date-stack {
  display: flex;
  flex-direction: column;
  gap: 4px;
  min-width: 0;
}

.order-code-cell strong,
.customer-cell strong,
.product-cell strong,
.date-stack strong {
  font-weight: 800;
  overflow-wrap: anywhere;
}

.order-code-cell strong {
  font-size: 14px;
}

.order-code-cell small,
.customer-cell small,
.product-cell small,
.date-stack small,
.payment-stack small {
  color: #64748b;
  overflow-wrap: anywhere;
}

.product-cell {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  max-width: 100%;
  min-width: 0;
}

.product-cell img {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  object-fit: cover;
  background: #f1f5f9;
}

.payment-stack {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.payment-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: fit-content;
  padding: 5px 10px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 800;
}

.payment-pill.unpaid,
.payment-pill.pending {
  color: #b45309;
  background: #fff7ed;
}

.payment-pill.paid {
  color: #15803d;
  background: #ecfdf5;
}

.payment-pill.failed {
  color: #dc2626;
  background: #fee2e2;
}

.payment-pill.refunded {
  color: #7c3aed;
  background: #f3e8ff;
}

.status-select {
  width: 100%;
  max-width: 126px;
  min-width: 0;
  height: 32px;
  padding: 0 7px;
  border: 1px solid #dbe3ef;
  border-radius: 9px;
  background: #ffffff;
  font-size: 12px;
  font-weight: 700;
  line-height: 1.2;
}

.status-select.pending {
  color: #c2410c;
  background: #fff7ed;
}

.status-select.confirmed {
  color: #7c3aed;
  background: #f5f3ff;
}

.status-select.processing {
  color: #2563eb;
  background: #eff6ff;
}

.status-select.shipping {
  color: #0f766e;
  background: #ecfeff;
}

.status-select.completed {
  color: #15803d;
  background: #ecfdf5;
}

.status-select.cancelled {
  color: #dc2626;
  background: #fef2f2;
}

.action-group {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.action-btn {
  width: 36px;
  height: 36px;
  display: inline-grid;
  place-items: center;
  border: 0;
  border-radius: 10px;
}

.action-view {
  color: #2563eb;
  background: #eaf2ff;
}

.empty-state {
  min-height: 160px;
  display: grid;
  place-items: center;
  gap: 10px;
  color: #64748b;
  text-align: center;
}

.product-meta {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
  white-space: normal;
  overflow: hidden;
}

.product-meta strong {
  display: block;
  line-height: 1.35;
}

.product-meta small {
  line-height: 1.35;
}

.payment-method {
  color: #475569;
  font-weight: 700;
  white-space: normal;
  overflow-wrap: anywhere;
  line-height: 1.25;
  font-size: 13px;
}

.payment-stack,
.status-select,
.action-group {
  white-space: nowrap;
}

.empty-state i {
  font-size: 28px;
  color: #2563eb;
}

@media (max-width: 1199.98px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .hero-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .toolbar {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    max-width: none;
  }
}

@media (max-width: 767.98px) {
  .toolbar,
  .filter-row {
    flex-direction: column;
    align-items: stretch;
  }

  .hero-card {
    padding: 20px;
  }

  .hero-copy h1 {
    font-size: 24px;
  }

  .hero-actions,
  .hero-stats {
    grid-template-columns: 1fr;
    flex-direction: column;
  }

  .primary-action,
  .secondary-action {
    width: 100%;
  }

  .search-box,
  .filter-select {
    width: 100%;
    max-width: none;
  }
}
</style>
