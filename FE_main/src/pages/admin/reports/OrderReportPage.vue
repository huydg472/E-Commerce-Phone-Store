<script setup>
import {computed, onMounted, ref} from 'vue'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import {orderService} from '@/services/orderService'
import {paymentService} from '@/services/paymentService'
import {formatDate} from '@/utils/formatDate'
import {formatMoney, toNumber, unwrapList} from '@/utils/reportHelpers'
import {useClientPagination} from '@/composables/useClientPagination.js'

const loading = ref(true)
const errorMessage = ref('')
const orders = ref([])
const payments = ref([])

const unwrapOrderItems = (order) => {
  const sources = [order?.orderItems, order?.order_items, order?.items]

  for (const source of sources) {
    if (Array.isArray(source) && source.length > 0) {
      return source
    }
  }

  return []
}

const statusStats = computed(() => {
  const buckets = [
    {key: 'pending', label: 'Chờ xác nhận', count: 0, color: 'warning'},
    {key: 'confirmed', label: 'Đã xác nhận', count: 0, color: 'info'},
    {key: 'shipping', label: 'Đang giao', count: 0, color: 'primary'},
    {key: 'completed', label: 'Hoàn thành', count: 0, color: 'success'},
    {key: 'cancelled', label: 'Đã huỷ', count: 0, color: 'danger'},
  ]

  orders.value.forEach((order) => {
    const bucket = buckets.find((item) => item.key === order?.order_status)
    if (bucket) {
      bucket.count += 1
    }
  })

  return buckets
})

const paymentMethodStats = computed(() => {
  const buckets = [
    {key: 'cod', label: 'COD', count: 0},
    {key: 'bank_transfer', label: 'Chuyển khoản', count: 0},
    {key: 'momo', label: 'MoMo', count: 0},
    {key: 'vnpay', label: 'VNPay', count: 0},
  ]

  orders.value.forEach((order) => {
    const key = String(order?.payment?.payment_method || order?.payment_method || 'cod').toLowerCase()
    const bucket = buckets.find((item) => item.key === key)
    if (bucket) {
      bucket.count += 1
    }
  })

  return buckets
})

const paymentStatusStats = computed(() => {
  const buckets = [
    {key: 'paid', label: 'Đã thanh toán', count: 0},
    {key: 'pending', label: 'Chờ thanh toán', count: 0},
    {key: 'failed', label: 'Thất bại', count: 0},
    {key: 'refunded', label: 'Đã hoàn tiền', count: 0},
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

const revenueTotal = computed(() =>
  orders.value
      .filter((order) => order?.order_status === 'completed')
      .reduce((sum, order) => sum + toNumber(order?.total_amount), 0),
)

const averageOrderValue = computed(() => {
  const completedOrders = orders.value.filter((order) => order?.order_status === 'completed')

  if (!completedOrders.length) {
    return 0
  }

  return revenueTotal.value / completedOrders.length
})

const topCustomers = computed(() => {
  const customerMap = new Map()

  orders.value.forEach((order) => {
    const key = order?.user?.name || order?.receiver_name || `Khách #${order?.user_id || order?.id}`
    const current = customerMap.get(key) || {
      name: key,
      orders: 0,
      revenue: 0,
    }

    current.orders += 1
    current.revenue += toNumber(order?.total_amount)
    customerMap.set(key, current)
  })

  return [...customerMap.values()]
      .sort((left, right) => right.revenue - left.revenue)
      .slice(0, 8)
})

const recentOrders = computed(() => {
  return [...orders.value]
      .sort((left, right) => new Date(right?.ordered_at || right?.created_at || 0) - new Date(left?.ordered_at || left?.created_at || 0))
      .map((order) => ({
        id: order.id,
        code: order.order_code || `#${order.id}`,
        customer: order.receiver_name || order.user?.name || 'Khách hàng',
        phone: order.receiver_phone || order.user?.phone || '',
        total: toNumber(order.total_amount),
        status: order.order_status || 'pending',
        paymentStatus: String(order?.payment?.payment_status || order?.payment_status || 'pending').toLowerCase(),
        paymentMethod: String(order?.payment?.payment_method || order?.payment_method || 'cod').toLowerCase(),
        itemCount: unwrapOrderItems(order).reduce((sum, item) => sum + toNumber(item?.quantity), 0),
        orderedAt: formatDate(order.ordered_at || order.created_at),
      }))
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems: paginatedRecentOrders,
  pageStart,
  pageEnd,
} = useClientPagination(recentOrders, {
  defaultPageSize: 8,
  pageSizeOptions: [8, 12],
})

const stats = computed(() => [
  {label: 'Tổng đơn', value: orders.value.length, desc: 'Toàn bộ đơn hàng', icon: 'bi bi-bag', tone: 'blue'},
  {label: 'Đang giao', value: statusStats.value.find((item) => item.key === 'shipping')?.count || 0, desc: 'Đơn đang xử lý giao hàng', icon: 'bi bi-truck', tone: 'orange'},
  {label: 'Hoàn thành', value: statusStats.value.find((item) => item.key === 'completed')?.count || 0, desc: 'Đơn đã chốt doanh thu', icon: 'bi bi-check-circle', tone: 'green'},
  {label: 'Doanh thu', value: formatMoney(revenueTotal.value), desc: `TB ${formatMoney(averageOrderValue.value)}/đơn`, icon: 'bi bi-cash-stack', tone: 'slate'},
])

const loadReport = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const [ordersResponse, paymentsResponse] = await Promise.all([
      orderService.getAll({per_page: 1000}),
      paymentService.getAll({per_page: 1000}),
    ])

    orders.value = unwrapList(ordersResponse)
    payments.value = unwrapList(paymentsResponse)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không tải được báo cáo đơn hàng.'
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
        <h1>Báo cáo đơn hàng</h1>
        <p class="subtitle">Tổng hợp trạng thái đơn, thanh toán và khách hàng mua nhiều nhất.</p>
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
      <p>Đang tải báo cáo đơn hàng...</p>
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
              <h2>Phân bổ trạng thái đơn</h2>
              <p>Tỷ trọng đơn theo trạng thái hiện tại.</p>
            </div>
          </div>

          <div class="status-list">
            <div v-for="item in statusStats" :key="item.key" class="status-row">
              <div>
                <strong>{{ item.label }}</strong>
                <small>{{ item.count }} đơn</small>
              </div>
              <div class="status-pill" :class="item.color">{{ item.count }}</div>
            </div>
          </div>
        </article>

        <article class="panel">
          <div class="panel-head">
            <div>
              <h2>Phương thức thanh toán</h2>
              <p>Thống kê cách khách thanh toán đơn hàng.</p>
            </div>
          </div>

          <div class="status-list">
            <div v-for="item in paymentMethodStats" :key="item.key" class="status-row">
              <div>
                <strong>{{ item.label }}</strong>
                <small>{{ item.count }} đơn</small>
              </div>
              <div class="status-pill info">{{ item.count }}</div>
            </div>
          </div>
        </article>
      </section>

      <section class="panel">
        <div class="panel-head">
          <div>
            <h2>Khách hàng mua nhiều nhất</h2>
            <p>Xếp theo doanh thu sinh ra từ từng khách.</p>
          </div>
        </div>

        <div class="table-wrap">
          <table class="report-table">
            <thead>
            <tr>
              <th>Khách hàng</th>
              <th>Số đơn</th>
              <th>Doanh thu</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="item in topCustomers" :key="item.name">
              <td>{{ item.name }}</td>
              <td>{{ item.orders }}</td>
              <td><strong>{{ formatMoney(item.revenue) }}</strong></td>
            </tr>

            <tr v-if="!topCustomers.length">
              <td colspan="3">
                <div class="empty-state">
                  <i class="bi bi-people"></i>
                  <p>Chưa có dữ liệu khách hàng để thống kê.</p>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="panel">
        <div class="panel-head">
          <div>
            <h2>Danh sách đơn gần đây</h2>
            <p>Đơn mới nhất theo thời gian tạo.</p>
          </div>
        </div>

        <div class="table-wrap">
          <table class="report-table">
            <thead>
            <tr>
              <th>Mã đơn</th>
              <th>Khách hàng</th>
              <th>Số SP</th>
              <th>Thanh toán</th>
              <th>Phương thức</th>
              <th>Trạng thái</th>
              <th>Ngày đặt</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="order in paginatedRecentOrders" :key="order.id">
              <td>{{ order.code }}</td>
              <td>
                <strong>{{ order.customer }}</strong>
                <small class="cell-muted">{{ order.phone || 'Chưa có' }}</small>
              </td>
              <td>{{ order.itemCount }}</td>
              <td>
                <span class="badge-pill" :class="order.paymentStatus === 'paid' ? 'paid' : 'pending'">
                  {{ order.paymentStatus === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                </span>
              </td>
              <td>{{ order.paymentMethod.toUpperCase() }}</td>
              <td>
                <span class="badge-pill" :class="order.status">
                  {{ statusStats.find((item) => item.key === order.status)?.label || order.status }}
                </span>
              </td>
              <td>{{ order.orderedAt }}</td>
            </tr>

            <tr v-if="!recentOrders.length">
              <td colspan="7">
                <div class="empty-state">
                  <i class="bi bi-bag"></i>
                  <p>Chưa có đơn hàng nào.</p>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <ListPaginationControls
            v-if="recentOrders.length"
            :current-page="currentPage"
            :total-pages="totalPages"
            :page-size="pageSize"
            :total-items="recentOrders.length"
            :page-start="pageStart"
            :page-end="pageEnd"
            item-label="đơn hàng"
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
  grid-template-columns: repeat(2, minmax(0, 1fr));
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

.status-pill.primary { background: #eff6ff; color: #2563eb; }
.status-pill.success { background: #ecfdf5; color: #15803d; }
.status-pill.warning { background: #fff7ed; color: #c2410c; }
.status-pill.info { background: #f5f3ff; color: #7c3aed; }
.status-pill.danger { background: #fef2f2; color: #dc2626; }

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

.badge-pill {
  min-height: 30px;
  padding: 0 12px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  font-weight: 900;
}

.badge-pill.paid,
.badge-pill.completed {
  background: #ecfdf5;
  color: #15803d;
}

.badge-pill.pending,
.badge-pill.confirmed,
.badge-pill.shipping {
  background: #eff6ff;
  color: #2563eb;
}

.badge-pill.cancelled {
  background: #fef2f2;
  color: #dc2626;
}

.cell-muted {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 12px;
  font-weight: 500;
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
  .stats-grid,
  .content-grid {
    grid-template-columns: 1fr 1fr;
  }
}

@media (max-width: 768px) {
  .page-head {
    flex-direction: column;
  }

  .stats-grid,
  .content-grid {
    grid-template-columns: 1fr;
  }
}
</style>
