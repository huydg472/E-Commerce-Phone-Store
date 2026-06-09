<script setup>
import {computed, onMounted} from 'vue'
import {storeToRefs} from 'pinia'
import {useRouter} from 'vue-router'
import {useDashboardStore} from '@/stores/dashboardStore'

const router = useRouter()
const dashboardStore = useDashboardStore()
const {loading, error, lastUpdated} = storeToRefs(dashboardStore)

const numberFormatter = new Intl.NumberFormat('vi-VN')
const revenueFormatter = new Intl.NumberFormat('vi-VN', {
  maximumFractionDigits: 0,
})

const paymentMethodMap = {
  cod: 'COD',
  bank_transfer: 'Chuyển khoản',
  vnpay: 'VNPAY',
  momo: 'MoMo',
}

const orderStatusMap = {
  pending: 'Chờ xác nhận',
  confirmed: 'Đã xác nhận',
  processing: 'Đang xử lý',
  shipping: 'Đang giao',
  completed: 'Hoàn thành',
  cancelled: 'Đã hủy',
}

const paymentStatusMap = {
  pending: 'Chưa thanh toán',
  paid: 'Đã thanh toán',
  failed: 'Thất bại',
  cancelled: 'Đã hủy',
  refunded: 'Hoàn tiền',
}

const formatNumber = (value) => numberFormatter.format(Math.round(Number(value) || 0))

const formatCurrency = (value) => `${revenueFormatter.format(Math.round(Number(value) || 0))} đ`

const formatDateTime = (value) => {
  if (!value) {
    return 'Vừa xong'
  }

  return new Intl.DateTimeFormat('vi-VN', {
    hour: '2-digit',
    minute: '2-digit',
    day: '2-digit',
    month: '2-digit',
    year: '2-digit',
  }).format(new Date(value))
}

const formatMethod = (value) => paymentMethodMap[value] || value || 'Không rõ'

const formatOrderStatus = (value) => orderStatusMap[value] || value || 'Không rõ'

const formatPaymentStatus = (value) => paymentStatusMap[value] || value || 'Không rõ'

const monthBuckets = (orders) => {
  const buckets = []
  const now = new Date()

  for (let index = 5; index >= 0; index -= 1) {
    const date = new Date(now.getFullYear(), now.getMonth() - index, 1)

    buckets.push({
      key: `${date.getFullYear()}-${date.getMonth()}`,
      label: `T${date.getMonth() + 1}`,
      total: 0,
    })
  }

  orders.forEach((order) => {
    const sourceDate = order.ordered_at || order.completed_at || order.created_at

    if (!sourceDate) {
      return
    }

    const date = new Date(sourceDate)
    const bucket = buckets.find((item) => item.key === `${date.getFullYear()}-${date.getMonth()}`)

    if (bucket) {
      bucket.total += Number(order.total_amount || 0)
    }
  })

  const maxTotal = Math.max(...buckets.map((item) => item.total), 1)

  return buckets.map((item) => ({
    ...item,
    height: Math.max(16, Math.round((item.total / maxTotal) * 100)),
    amount: formatCurrency(item.total),
  }))
}

const topProducts = computed(() => {
  const productMap = new Map()

  dashboardStore.orders.forEach((order) => {
    const orderItems = Array.isArray(order?.orderItems)
        ? order.orderItems
        : Array.isArray(order?.order_items)
            ? order.order_items
            : []

    orderItems.forEach((item) => {
      const variant = item.productVariant || item.product_variant || {}
      const product = variant.product || variant.product || {}
      const key = variant.id || item.product_variant_id || `${item.product_name}-${item.variant_name}`
      const current = productMap.get(key) || {
        name: product.name || item.product_name || 'Sản phẩm',
        variant: item.variant_name || variant.name || 'Biến thể',
        sold: 0,
        revenue: 0,
      }

      current.sold += Number(item.quantity || 0)
      current.revenue += Number(item.total_price || 0)
      productMap.set(key, current)
    })
  })

  const items = [...productMap.values()]
      .sort((left, right) => right.revenue - left.revenue)
      .slice(0, 4)

  const maxRevenue = Math.max(...items.map((item) => item.revenue), 1)

  return items.map((item) => ({
    ...item,
    percent: Math.max(18, Math.round((item.revenue / maxRevenue) * 100)),
  }))
})

const recentOrders = computed(() => {
  return dashboardStore.orders.slice(0, 5).map((order) => ({
    id: order.order_code || `#${order.id}`,
    customer: order.receiver_name || order.user?.name || 'Khách hàng',
    total: formatCurrency(order.total_amount),
    statusKey: order.order_status || 'pending',
    status: formatOrderStatus(order.order_status),
    paymentStatusKey: order.payment?.payment_status || order.payment_status || 'pending',
    paymentStatus: formatPaymentStatus(order.payment?.payment_status || order.payment_status),
    paymentMethod: formatMethod(order.payment?.payment_method || order.payment_method),
    time: formatDateTime(order.ordered_at || order.created_at),
  }))
})

const revenueSeries = computed(() => monthBuckets(dashboardStore.orders))

const activities = computed(() => {
  const items = []

  const latestOrder = dashboardStore.orders[0]
  if (latestOrder) {
    items.push({
      icon: 'bi bi-bag-check',
      title: `Đơn hàng ${latestOrder.order_code || `#${latestOrder.id}`}`,
      detail: `${latestOrder.receiver_name || latestOrder.user?.name || 'Khách hàng'} · ${formatCurrency(latestOrder.total_amount)}`,
      time: formatDateTime(latestOrder.ordered_at || latestOrder.created_at),
      tone: 'blue',
    })
  }

  const latestPayment = dashboardStore.payments[0]
  if (latestPayment) {
    items.push({
      icon: 'bi bi-credit-card',
      title: `Thanh toán ${formatPaymentStatus(latestPayment.payment_status)}`,
      detail: `${formatMethod(latestPayment.payment_method)} · ${formatCurrency(latestPayment.amount)}`,
      time: formatDateTime(latestPayment.created_at),
      tone: 'orange',
    })
  }

  const latestProduct = dashboardStore.latestProducts[0]
  if (latestProduct) {
    items.push({
      icon: 'bi bi-phone',
      title: `Sản phẩm ${latestProduct.name}`,
      detail: `${latestProduct.brand?.name || 'Chưa có thương hiệu'} · ${latestProduct.category?.name || 'Chưa có danh mục'}`,
      time: formatDateTime(latestProduct.updated_at || latestProduct.created_at),
      tone: 'green',
    })
  }

  const latestBrand = dashboardStore.brands[0]
  if (latestBrand) {
    items.push({
      icon: 'bi bi-award',
      title: `Thương hiệu ${latestBrand.name}`,
      detail: latestBrand.status === 'active' ? 'Đang hoạt động' : 'Tạm ẩn',
      time: formatDateTime(latestBrand.updated_at || latestBrand.created_at),
      tone: 'slate',
    })
  }

  return items
})

const stats = computed(() => [
  {
    title: 'Tổng doanh thu',
    value: formatCurrency(dashboardStore.revenue),
    icon: 'bi bi-cash-stack',
    tone: 'blue',
    desc: `${formatNumber(dashboardStore.completedOrders)} đơn hoàn tất`,
  },
  {
    title: 'Tổng đơn hàng',
    value: formatNumber(dashboardStore.totalOrders),
    icon: 'bi bi-receipt',
    tone: 'orange',
    desc: `${formatNumber(dashboardStore.pendingOrders)} đơn chờ xác nhận`,
  },
  {
    title: 'Sản phẩm hoạt động',
    value: formatNumber(dashboardStore.activeProducts),
    icon: 'bi bi-box-seam',
    tone: 'green',
    desc: `${formatNumber(dashboardStore.featuredProducts)} sản phẩm nổi bật`,
  },
  {
    title: 'Người dùng',
    value: formatNumber(dashboardStore.totalUsers),
    icon: 'bi bi-people',
    tone: 'slate',
    desc: `${formatNumber(dashboardStore.totalBrands)} thương hiệu, ${formatNumber(dashboardStore.totalCategories)} danh mục`,
  },
])

const refreshDashboard = () => {
  dashboardStore.fetchDashboard()
}

const goToOrders = () => {
  router.push({name: 'admin.orders.index'})
}

onMounted(() => {
  dashboardStore.fetchDashboard()
})
</script>

<template>
  <div class="dashboard-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">TỔNG QUAN QUẢN TRỊ</p>
        <h1>Dashboard dữ liệu thật</h1>
        <p class="hero-description">
          Toàn bộ số liệu được tổng hợp trực tiếp từ sản phẩm, đơn hàng, thanh toán, thương hiệu và người dùng trong hệ
          thống.
        </p>

        <p v-if="lastUpdated" class="hero-updated">
          Cập nhật lúc {{ formatDateTime(lastUpdated) }}
        </p>

        <div class="hero-actions">
          <button type="button" class="primary-action" :disabled="loading" @click="refreshDashboard">
            <i :class="loading ? 'bi bi-arrow-repeat spin' : 'bi bi-arrow-repeat'"></i>
            {{ loading ? 'Đang tải' : 'Tải lại' }}
          </button>

          <button type="button" class="secondary-action" @click="goToOrders">
            <i class="bi bi-bag-check"></i>
            Xem đơn hàng
          </button>
        </div>

        <p v-if="error" class="hero-error">
          {{ error }}
        </p>
      </div>

      <div class="hero-stats">
        <article v-for="item in stats" :key="item.title" class="stat-card">
          <div class="stat-icon" :class="`tone-${item.tone}`">
            <i :class="item.icon"></i>
          </div>

          <div class="stat-content">
            <strong>{{ item.value }}</strong>
            <span>{{ item.title }}</span>
            <small>{{ item.desc }}</small>
          </div>
        </article>
      </div>
    </section>

    <section class="dashboard-grid">
      <div class="dashboard-card sales-card">
        <div class="card-head">
          <div>
            <h4>Doanh thu bán hàng</h4>
            <p>Thống kê 6 tháng gần nhất từ đơn hàng thật trong hệ thống</p>
          </div>

          <button type="button" class="card-action" @click="router.push({ name: 'admin.reports.revenue' })">
            Xem báo cáo
          </button>
        </div>

        <div class="chart-box">
          <div v-for="item in revenueSeries" :key="item.key" class="bar-item">
            <span :style="{ height: item.height + '%' }"></span>
            <small>{{ item.label }}</small>
            <strong>{{ item.amount }}</strong>
          </div>
        </div>
      </div>

      <div class="dashboard-card activity-card">
        <div class="card-head">
          <div>
            <h4>Hoạt động gần đây</h4>
            <p>Dữ liệu được lấy từ bản ghi mới nhất trong hệ thống</p>
          </div>
        </div>

        <div v-if="activities.length" class="activity-list">
          <article v-for="item in activities" :key="item.title + item.time" class="activity-item">
            <div class="activity-icon" :class="`tone-${item.tone}`">
              <i :class="item.icon"></i>
            </div>

            <div class="activity-content">
              <strong>{{ item.title }}</strong>
              <span>{{ item.detail }}</span>
              <small>{{ item.time }}</small>
            </div>
          </article>
        </div>

        <div v-else class="empty-state">
          Chưa có dữ liệu hoạt động.
        </div>
      </div>
    </section>

    <section class="dashboard-grid bottom-grid">
      <div class="dashboard-card">
        <div class="card-head">
          <div>
            <h4>Sản phẩm bán chạy</h4>
            <p>Tổng hợp từ số lượng bán ra và giá trị đơn hàng</p>
          </div>
        </div>

        <div v-if="topProducts.length" class="product-list">
          <article v-for="product in topProducts" :key="product.name + product.variant" class="product-item">
            <div class="product-main">
              <div>
                <strong>{{ product.name }}</strong>
                <span>{{ product.variant }}</span>
              </div>

              <p>{{ formatCurrency(product.revenue) }}</p>
            </div>

            <div class="product-meta">
              <span>Đã bán {{ formatNumber(product.sold) }}</span>
            </div>

          </article>
        </div>

        <div v-else class="empty-state">
          Chưa có đơn hàng để thống kê sản phẩm bán chạy.
        </div>
      </div>

      <div class="dashboard-card">
        <div class="card-head">
          <div>
            <h4>Đơn hàng gần đây</h4>
            <p>Danh sách đơn mới nhất kèm trạng thái thanh toán</p>
          </div>

          <button type="button" class="card-action" @click="goToOrders">
            Tất cả
          </button>
        </div>

        <div v-if="recentOrders.length" class="order-list">
          <article v-for="order in recentOrders" :key="order.id" class="order-item">
            <div class="order-main">
              <strong>{{ order.id }}</strong>
              <span>{{ order.customer }}</span>
              <small>{{ order.time }}</small>
            </div>

            <div class="order-money">
              {{ order.total }}
            </div>

            <div class="order-meta">
              <span class="status-badge"
                    :class="`status-${order.paymentStatus === 'Đã thanh toán' ? 'success' : 'warning'}`">
                {{ order.paymentStatus }}
              </span>

              <span class="method-badge">
                {{ order.paymentMethod }}
              </span>

              <span class="status-chip" :class="`order-${order.statusKey}`">
                {{ order.status }}
              </span>
            </div>
          </article>
        </div>

        <div v-else class="empty-state">
          Chưa có đơn hàng nào.
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.dashboard-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.hero-card {
  padding: 24px;
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.12), transparent 34%),
  linear-gradient(180deg, rgba(255, 255, 255, 0.96), rgba(243, 248, 255, 0.92));
  box-shadow: 0 14px 36px rgba(37, 99, 235, 0.08);
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.95fr);
  gap: 18px;
}

.hero-copy {
  min-width: 0;
}

.eyebrow {
  margin: 0;
  color: #2563eb;
  font-size: 14px;
  font-weight: 900;
  letter-spacing: 0.08em;
}

.hero-copy h1 {
  margin: 8px 0 10px;
  color: #0f172a;
  font-size: 32px;
  line-height: 1.08;
  font-weight: 900;
}

.hero-description {
  margin: 0;
  color: #5b6b84;
  font-size: 15px;
  line-height: 1.65;
  max-width: 760px;
}

.hero-updated {
  margin: 14px 0 0;
  color: #94a3b8;
  font-size: 13px;
  font-weight: 600;
}

.hero-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 18px;
}

.primary-action,
.secondary-action,
.card-action {
  border: 1px solid transparent;
  outline: none;
  transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
}

.primary-action,
.secondary-action {
  min-height: 44px;
  padding: 0 16px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
}

.primary-action {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  color: #ffffff;
  box-shadow: 0 14px 24px rgba(37, 99, 235, 0.18);
}

.primary-action:disabled {
  opacity: 0.7;
  cursor: progress;
}

.secondary-action {
  background: #ffffff;
  color: #0f172a;
  border-color: #d5deea;
}

.primary-action:hover:not(:disabled),
.secondary-action:hover,
.card-action:hover {
  transform: translateY(-1px);
}

.spin {
  animation: spin 0.9s linear infinite;
}

.hero-error {
  margin: 14px 0 0;
  color: #dc2626;
  font-size: 13px;
  font-weight: 600;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  align-content: start;
}

.stat-card {
  min-height: 90px;
  padding: 14px;
  border: 1px solid #edf2f7;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.stat-icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  color: #ffffff;
  font-size: 18px;
  flex-shrink: 0;
}

.tone-blue {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.tone-green {
  background: linear-gradient(135deg, #22c55e, #16a34a);
}

.tone-orange {
  background: linear-gradient(135deg, #fb923c, #f97316);
}

.tone-slate {
  background: linear-gradient(135deg, #64748b, #475569);
}

.stat-content {
  min-width: 0;
}

.stat-content strong {
  display: block;
  color: #0f172a;
  font-size: 20px;
  line-height: 1;
  font-weight: 900;
  letter-spacing: -0.02em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stat-content span {
  display: block;
  margin-top: 3px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.stat-content small {
  display: block;
  margin-top: 3px;
  color: #94a3b8;
  font-size: 12px;
  line-height: 1.35;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.35fr) minmax(0, 1fr);
  gap: 20px;
}

.bottom-grid {
  grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
}

.dashboard-card {
  padding: 20px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.card-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 18px;
}

.card-head h4 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
  font-weight: 800;
}

.card-head p {
  margin: 5px 0 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.5;
}

.card-action {
  height: 38px;
  padding: 0 14px;
  border-radius: 10px;
  background: #eef5ff;
  color: #2563eb;
  font-size: 14px;
  font-weight: 700;
  flex-shrink: 0;
}

.chart-box {
  height: 260px;
  padding: 10px 6px 0;
  display: grid;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  align-items: end;
  gap: 14px;
}

.bar-item {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  align-items: center;
  gap: 8px;
}

.bar-item span {
  width: 100%;
  max-width: 40px;
  min-height: 16px;
  border-radius: 999px 999px 10px 10px;
  background: linear-gradient(180deg, #60a5fa, #2563eb);
}

.bar-item small {
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.bar-item strong {
  color: #0f172a;
  font-size: 12px;
  font-weight: 800;
  text-align: center;
}

.activity-list,
.product-list,
.order-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid #eef2f7;
}

.activity-item:last-child {
  padding-bottom: 0;
  border-bottom: none;
}

.activity-icon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  color: #ffffff;
  font-size: 18px;
  flex-shrink: 0;
}

.activity-content {
  min-width: 0;
}

.activity-content strong {
  display: block;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
}

.activity-content span {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.5;
}

.activity-content small {
  display: block;
  margin-top: 4px;
  color: #94a3b8;
  font-size: 12px;
}

.product-item,
.order-item {
  padding-bottom: 14px;
  border-bottom: 1px solid #eef2f7;
}

.product-item:last-child,
.order-item:last-child {
  padding-bottom: 0;
  border-bottom: none;
}

.product-main {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
}

.product-main strong,
.order-main strong {
  display: block;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
}

.product-main span,
.order-main span {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
}

.product-main p,
.order-money {
  margin: 0;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
  white-space: nowrap;
}

.product-meta,
.order-meta {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 10px;
  color: #64748b;
  font-size: 13px;
  flex-wrap: wrap;
}

.product-meta span:last-child {
  font-weight: 800;
  color: #0f172a;
}

.order-item {
  display: grid;
  grid-template-columns: minmax(0, 1fr) auto;
  gap: 12px;
}

.order-main {
  min-width: 0;
}

.order-main small {
  display: block;
  margin-top: 4px;
  color: #94a3b8;
  font-size: 12px;
}

.order-meta {
  grid-column: 1 / -1;
  justify-content: flex-start;
}

.status-badge,
.method-badge,
.status-chip {
  padding: 7px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
  white-space: nowrap;
}

.status-badge {
  background: #eef2ff;
  color: #2563eb;
}

.status-badge.status-success {
  background: #dcfce7;
  color: #15803d;
}

.status-badge.status-warning {
  background: #fff7ed;
  color: #c2410c;
}

.method-badge {
  background: #f1f5f9;
  color: #334155;
}

.status-chip.order-pending {
  background: #fff7ed;
  color: #c2410c;
}

.status-chip.order-confirmed,
.status-chip.order-processing,
.status-chip.order-shipping {
  background: #eff6ff;
  color: #2563eb;
}

.status-chip.order-completed {
  background: #dcfce7;
  color: #15803d;
}

.status-chip.order-cancelled {
  background: #fee2e2;
  color: #b91c1c;
}

.empty-state {
  padding: 18px;
  border-radius: 14px;
  background: #f8fafc;
  color: #64748b;
  font-size: 14px;
  text-align: center;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 1200px) {
  .hero-card,
  .dashboard-grid,
  .bottom-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 992px) {
  .hero-copy h1 {
    font-size: 28px;
  }

  .hero-stats {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .stat-content strong {
    font-size: 19px;
  }
}

@media (max-width: 640px) {
  .dashboard-page {
    gap: 16px;
  }

  .hero-card,
  .dashboard-card {
    padding: 18px;
    border-radius: 16px;
  }

  .hero-copy h1 {
    font-size: 24px;
  }

  .hero-stats {
    grid-template-columns: 1fr;
  }

  .stat-card {
    min-height: 86px;
  }

  .chart-box {
    height: 220px;
    gap: 10px;
  }

  .order-item {
    grid-template-columns: 1fr;
  }

  .order-money {
    white-space: normal;
  }

  .order-meta {
    justify-content: flex-start;
  }
}
</style>
