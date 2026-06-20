<script setup>
import {computed, onMounted, ref, watch} from 'vue'
import {storeToRefs} from 'pinia'
import {useRouter} from 'vue-router'
import {useDashboardStore} from '@/stores/dashboardStore'
import StatisticCard from '@/components/dashboard/StatisticCard.vue'
import RevenueChart from '@/components/dashboard/RevenueChart.vue'
import TopProducts from '@/components/dashboard/TopProducts.vue'
import RecentOrders from '@/components/dashboard/RecentOrders.vue'
import DashboardActivityFeed from '@/components/dashboard/DashboardActivityFeed.vue'

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
  unpaid: 'Chưa thanh toán',
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

const selectedDashboardPeriod = ref('month')


const dashboardRevenueTitle = computed(() => {
  if (selectedDashboardPeriod.value === '30d') {
    return 'Doanh thu 30 ngày gần đây'
  }

  if (selectedDashboardPeriod.value === '7d') {
    return 'Doanh thu 7 ngày gần đây'
  }

  return 'Doanh thu tháng này'
})

const dashboardTopProductsTitle = computed(() => {
  if (selectedDashboardPeriod.value === '30d') {
    return 'Sản phẩm bán chạy 30 ngày gần đây'
  }

  if (selectedDashboardPeriod.value === '7d') {
    return 'Sản phẩm bán chạy 7 ngày gần đây'
  }

  return 'Sản phẩm bán chạy tháng này'
})

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

const dashboardStats = computed(() => stats.value.map((item) => ({
  title: item.title,
  value: item.value,
  percent: '0%',
  icon: item.icon.replace('bi ', ''),
  variant: item.tone === 'green' ? 'success' : item.tone === 'purple' ? 'purple' : item.tone === 'warning' ? 'warning' : 'primary',
})))

const dashboardRevenueSeries = computed(() => {
  return Array.isArray(dashboardStore.analytics.revenueSeries)
    ? dashboardStore.analytics.revenueSeries.map((item) => ({
      day: item.day || item.label || '',
      label: item.label || item.day || '',
      percent: Number(item.percent || 0),
    }))
    : []
})

const dashboardTopProducts = computed(() => {
  const items = Array.isArray(dashboardStore.analytics.topProducts) ? dashboardStore.analytics.topProducts : []

  return items.map((product, index) => ({
    id: `${product.id ?? product.name ?? 'product'}-${index}`,
    rank: index + 1,
    thumbClass: index === 0 ? 'phone-graphite' : index === 1 ? 'phone-titanium' : index === 2 ? 'phone-purple' : 'phone-green',
    name: product.name,
    sold: formatNumber(product.sold),
  }))
})

const dashboardRecentOrders = computed(() => {
  const items = Array.isArray(dashboardStore.analytics.recentOrders) ? dashboardStore.analytics.recentOrders : []

  return items.map((order, index) => ({
    code: order.code || `#${order.id}`,
    customer: order.customer,
    product: order.product,
    total: formatCurrency(order.total),
    status:
        order.status === 'shipping'
            ? 'shipping'
            : order.status === 'completed'
                ? 'completed'
                : order.status === 'cancelled'
                    ? 'cancelled'
                    : 'pending',
    date: formatDateTime(order.date),
    thumbClass: index === 0 ? 'phone-graphite' : index === 1 ? 'phone-titanium' : index === 2 ? 'phone-purple' : 'phone-green',
  }))
})

const refreshDashboard = () => {
  dashboardStore.fetchDashboard()
  dashboardStore.fetchAnalytics(selectedDashboardPeriod.value)
}

const goToOrders = () => {
  router.push({name: 'admin.orders.index'})
}

onMounted(() => {
  dashboardStore.fetchDashboard()
  dashboardStore.fetchAnalytics(selectedDashboardPeriod.value)
})

watch(selectedDashboardPeriod, (period) => {
  dashboardStore.fetchAnalytics(period).catch(() => {})
})
</script>
<template>
  <div class="dashboard-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">TỔNG QUAN QUẢN TRỊ</p>
        <h1>Tổng quan cửa hàng</h1>
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
        <StatisticCard
            v-for="item in dashboardStats"
            :key="item.title"
            :title="item.title"
            :value="item.value"
            :percent="item.percent"
            :icon="item.icon"
            :variant="item.variant"
        />
      </div>
    </section>

    <section class="dashboard-grid">
      <RevenueChart
          v-model:period="selectedDashboardPeriod"
          :title="dashboardRevenueTitle"
          :items="dashboardRevenueSeries"
      />
      <DashboardActivityFeed :activities="activities"/>
    </section>

    <section class="dashboard-grid bottom-grid">
      <TopProducts
          v-model:period="selectedDashboardPeriod"
          :title="dashboardTopProductsTitle"
          :products="dashboardTopProducts"
      />
      <RecentOrders :orders="dashboardRecentOrders"/>
    </section>
  </div>
</template>

<style>
.dashboard-page {
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
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.95fr);
  gap: 18px;
}

.hero-copy {
  min-width: 0;
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
  line-height: 1.1;
  font-weight: 900;
}

.hero-description {
  margin: 10px 0 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.7;
  max-width: 760px;
}

.hero-updated {
  margin: 16px 0 0;
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
  gap: 16px;
  align-content: start;
}

.stat-card {
  min-height: 112px;
  padding: 18px 20px;
  border: 1px solid #e6ecf4;
  border-radius: 22px;
  background: #ffffff;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.05);
  display: grid;
  grid-template-columns: 58px minmax(0, 1fr);
  align-items: center;
  column-gap: 16px;
  transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
  border-color: #dbe5f3;
  box-shadow: 0 18px 34px rgba(15, 23, 42, 0.08);
}

.stat-icon {
  width: 58px;
  height: 58px;
  border-radius: 18px;
  position: relative;
  overflow: hidden;
  display: grid;
  place-items: center;
  color: #ffffff;
  font-size: 22px;
  flex-shrink: 0;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.16);
}

.stat-icon i {
  display: flex;
  width: 100%;
  height: 100%;
  align-items: center;
  justify-content: center;
  line-height: 1;
  font-style: normal;
  transform: translateY(0);
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
  display: flex;
  flex-direction: column;
  justify-content: center;
  min-height: 58px;
}

.stat-content strong {
  display: block;
  color: #0f172a;
  font-size: 30px;
  line-height: 1.06;
  font-weight: 900;
  letter-spacing: -0.02em;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.stat-content span {
  display: block;
  margin-top: 4px;
  color: #475569;
  font-size: 15px;
  font-weight: 800;
}

.stat-content small {
  display: block;
  margin-top: 6px;
  color: #94a3b8;
  font-size: 13px;
  line-height: 1.45;
}

.stat-card--revenue .stat-content strong {
  font-size: 26px;
  letter-spacing: -0.03em;
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
    min-height: 96px;
    padding: 16px 18px;
    border-radius: 18px;
    grid-template-columns: 52px minmax(0, 1fr);
    column-gap: 14px;
  }

  .stat-content strong {
    font-size: 28px;
  }

  .stat-card--revenue .stat-content strong {
    font-size: 24px;
  }

  .stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    font-size: 20px;
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
