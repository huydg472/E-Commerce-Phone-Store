<script setup>
import {computed, onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import {reportService} from '@/services/reportService'
import {formatDate} from '@/utils/formatDate'
import {
  formatMoney,
  formatMonthKey,
  formatMonthLabel,
  isInLastDays,
  toNumber,
} from '@/utils/reportHelpers'

const router = useRouter()

const loading = ref(true)
const errorMessage = ref('')
const selectedWindow = ref('180')
const orders = ref([])
const payments = ref([])

const windowOptions = [
  {label: '30 ngày', value: '30', days: 30},
  {label: '90 ngày', value: '90', days: 90},
  {label: '180 ngày', value: '180', days: 180},
  {label: 'Tất cả', value: 'all', days: Infinity},
]

const currentWindow = computed(() => {
  return windowOptions.find((item) => item.value === selectedWindow.value) || windowOptions[2]
})

const filteredOrders = computed(() => {
  if (currentWindow.value.value === 'all') {
    return orders.value
  }

  return orders.value.filter((order) => {
    const sourceDate = order?.completed_at || order?.ordered_at || order?.created_at
    return isInLastDays(sourceDate, currentWindow.value.days)
  })
})

const completedOrders = computed(() =>
  filteredOrders.value.filter((order) => order?.order_status === 'completed'),
)

const cancelledOrders = computed(() =>
  filteredOrders.value.filter((order) => order?.order_status === 'cancelled'),
)

const revenueTotal = computed(() =>
  completedOrders.value.reduce((sum, order) => sum + toNumber(order?.total_amount), 0),
)

const averageOrderValue = computed(() => {
  if (!completedOrders.value.length) {
    return 0
  }

  return revenueTotal.value / completedOrders.value.length
})

const paidPayments = computed(() => {
  return payments.value.filter((payment) => String(payment?.payment_status || '').toLowerCase() === 'paid').length
})

const paymentStatusStats = computed(() => {
  const buckets = [
    {key: 'paid', label: 'Đã thanh toán', count: 0, color: 'success'},
    {key: 'pending', label: 'Chờ thanh toán', count: 0, color: 'warning'},
    {key: 'failed', label: 'Thất bại', count: 0, color: 'danger'},
    {key: 'refunded', label: 'Đã hoàn tiền', count: 0, color: 'info'},
  ]

  payments.value.forEach((payment) => {
    const key = String(payment?.payment_status || 'pending').toLowerCase()
    const bucket = buckets.find((item) => item.key === key)
    if (bucket) {
      bucket.count += 1
    }
  })

  return buckets
})

const monthlyRevenueSeries = computed(() => {
  const monthKeys = Array.from({length: 6}, (_, index) => {
    const date = new Date()
    date.setDate(1)
    date.setMonth(date.getMonth() - (5 - index))
    return formatMonthKey(date)
  })

  const monthMap = new Map(monthKeys.map((key) => [key, {amount: 0, count: 0}]))

  completedOrders.value.forEach((order) => {
    const key = formatMonthKey(order?.completed_at || order?.ordered_at || order?.created_at)
    if (!monthMap.has(key)) {
      return
    }

    const current = monthMap.get(key)
    current.amount += toNumber(order?.total_amount)
    current.count += 1
  })

  const values = [...monthMap.values()].map((item) => item.amount)
  const maxValue = Math.max(...values, 1)

  return monthKeys.map((key) => {
    const month = monthMap.get(key) || {amount: 0, count: 0}
    return {
      key,
      label: formatMonthLabel(`${key}-01`),
      amount: month.amount,
      count: month.count,
      height: Math.max(18, Math.round((month.amount / maxValue) * 100)),
    }
  })
})

const revenueByDay = computed(() => {
  const dayMap = new Map()

  completedOrders.value.forEach((order) => {
    const key = formatDate(order?.completed_at || order?.ordered_at || order?.created_at)
    const current = dayMap.get(key) || {label: key, amount: 0, count: 0}
    current.amount += toNumber(order?.total_amount)
    current.count += 1
    dayMap.set(key, current)
  })

  return [...dayMap.values()]
      .sort((left, right) => right.amount - left.amount)
      .slice(0, 5)
})

const stats = computed(() => [
  {
    label: 'Doanh thu',
    value: formatMoney(revenueTotal.value),
    desc: `${completedOrders.value.length} đơn hoàn tất`,
    icon: 'bi bi-cash-stack',
    tone: 'blue',
  },
  {
    label: 'Đơn hoàn tất',
    value: String(completedOrders.value.length),
    desc: `${filteredOrders.value.length} đơn trong kỳ`,
    icon: 'bi bi-bag-check',
    tone: 'green',
  },
  {
    label: 'Giá trị TB',
    value: formatMoney(averageOrderValue.value),
    desc: 'Trên mỗi đơn hoàn tất',
    icon: 'bi bi-graph-up-arrow',
    tone: 'orange',
  },
  {
    label: 'Đơn huỷ',
    value: String(cancelledOrders.value.length),
    desc: `${paidPayments.value} giao dịch đã trả`,
    icon: 'bi bi-x-circle',
    tone: 'slate',
  },
])

const loadReport = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await reportService.revenue()
    const payload = response.data?.data ?? response.data ?? {}

    orders.value = Array.isArray(payload.orders) ? payload.orders : []
    payments.value = Array.isArray(payload.payments) ? payload.payments : []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không tải được báo cáo doanh thu.'
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
        <h1>Báo cáo doanh thu</h1>
        <p class="subtitle">Theo dõi doanh thu, đơn hoàn tất và hiệu quả thanh toán theo từng giai đoạn.</p>
      </div>

      <div class="page-actions">
        <select v-model="selectedWindow" class="range-select">
          <option v-for="item in windowOptions" :key="item.value" :value="item.value">{{ item.label }}</option>
        </select>
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
      <p>Đang tải báo cáo doanh thu...</p>
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
              <h2>Doanh thu 6 tháng gần nhất</h2>
              <p>Số liệu chỉ tính trên các đơn đã hoàn tất.</p>
            </div>
          </div>

          <div class="bar-chart">
            <div v-for="item in monthlyRevenueSeries" :key="item.key" class="bar-item">
              <span :style="{height: `${item.height}%`}"></span>
              <strong>{{ formatMoney(item.amount) }}</strong>
              <small>{{ item.label || item.key }}</small>
            </div>
          </div>
        </article>

        <article class="panel">
          <div class="panel-head">
            <div>
              <h2>Trạng thái thanh toán</h2>
              <p>Đếm theo dữ liệu thanh toán hiện có.</p>
            </div>
          </div>

          <div class="status-list">
            <div v-for="item in paymentStatusStats" :key="item.key" class="status-row">
              <div>
                <strong>{{ item.label }}</strong>
                <small>{{ item.count }} giao dịch</small>
              </div>
              <div class="status-pill" :class="item.color">{{ item.count }}</div>
            </div>
          </div>
        </article>
      </section>

      <section class="panel">
        <div class="panel-head">
          <div>
            <h2>Top ngày doanh thu</h2>
            <p>Top 5 ngày có doanh thu cao nhất trong kỳ lọc.</p>
          </div>
        </div>

        <div class="table-wrap">
          <table class="report-table">
            <thead>
            <tr>
              <th>Ngày</th>
              <th>Số đơn</th>
              <th>Doanh thu</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in revenueByDay" :key="item.label">
              <td>{{ item.label }}</td>
              <td>{{ item.count }}</td>
              <td><strong>{{ formatMoney(item.amount) }}</strong></td>
            </tr>

            <tr v-if="!revenueByDay.length">
              <td colspan="3">
                <div class="empty-state">
                  <i class="bi bi-graph-up"></i>
                  <p>Chưa có dữ liệu doanh thu trong khoảng thời gian này.</p>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="action-row">
        <button type="button" class="secondary-action" @click="router.push({name: 'admin.orders.index'})">
          <i class="bi bi-receipt"></i>
          Xem đơn hàng
        </button>
        <button type="button" class="secondary-action" @click="router.push({name: 'admin.payments.index'})">
          <i class="bi bi-credit-card"></i>
          Xem thanh toán
        </button>
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

.range-select,
.secondary-action {
  min-height: 44px;
  padding: 0 14px;
  border-radius: 12px;
  font-size: 14px;
  font-weight: 800;
}

.range-select {
  border: 1px solid #dbe3ef;
  background: #ffffff;
}

.secondary-action {
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
  display: inline-flex;
  align-items: center;
  gap: 8px;
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
  grid-template-columns: minmax(0, 1.3fr) minmax(0, 0.9fr);
  gap: 18px;
}

.panel {
  padding: 20px;
}

.panel-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
  margin-bottom: 18px;
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

.bar-chart {
  min-height: 280px;
  display: grid;
  grid-template-columns: repeat(6, minmax(0, 1fr));
  gap: 12px;
  align-items: end;
}

.bar-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.bar-item span {
  width: 100%;
  min-height: 18px;
  border-radius: 16px 16px 6px 6px;
  background: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%);
  box-shadow: 0 10px 24px rgba(37, 99, 235, 0.18);
}

.bar-item strong {
  color: #0f172a;
  font-size: 12px;
  font-weight: 800;
  text-align: center;
}

.bar-item small {
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
}

.status-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.status-row {
  padding: 14px 16px;
  border: 1px solid #eef2f7;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.status-row strong {
  display: block;
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
}

.status-row small {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 12px;
}

.status-pill {
  min-width: 44px;
  min-height: 32px;
  padding: 0 12px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 900;
}

.status-pill.success { background: #ecfdf5; color: #15803d; }
.status-pill.warning { background: #fff7ed; color: #c2410c; }
.status-pill.danger { background: #fef2f2; color: #dc2626; }
.status-pill.info { background: #eff6ff; color: #2563eb; }

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

.action-row {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
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

  .bar-chart {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}
</style>
