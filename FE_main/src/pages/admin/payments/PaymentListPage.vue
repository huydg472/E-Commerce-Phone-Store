<script setup>
import {computed, onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import PaymentStatusBadge from '@/components/payment/PaymentStatusBadge.vue'
import {useClientPagination} from '@/composables/useClientPagination.js'
import {usePaymentStore} from '@/stores/paymentStore'
import {formatCurrency} from '@/utils/formatCurrency'
import {formatDate} from '@/utils/formatDate'

const router = useRouter()
const paymentStore = usePaymentStore()

const pageLoading = ref(true)
const errorMessage = ref('')
const searchQuery = ref('')
const statusFilter = ref('all')
const methodFilter = ref('all')

const methodMap = {
  cod: 'COD',
  bank_transfer: 'Chuyển khoản',
  momo: 'MoMo',
  vnpay: 'VNPay',
}

const statusMap = {
  pending: 'Chờ thanh toán',
  paid: 'Đã thanh toán',
  failed: 'Thất bại',
  cancelled: 'Đã hủy',
  refunded: 'Đã hoàn tiền',
  unpaid: 'Chưa thanh toán',
}

const payments = computed(() => (Array.isArray(paymentStore.items) ? paymentStore.items : []))

const filteredPayments = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return payments.value.filter((payment) => {
    const order = payment.order || {}
    const customer = order.user || {}
    const matchesQuery = !query
        || [
          payment.transaction_code,
          payment.payment_method,
          payment.payment_status,
          order.order_code,
          order.receiver_name,
          customer.name,
          customer.email,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(query))

    const matchesStatus = statusFilter.value === 'all' || payment.payment_status === statusFilter.value
    const matchesMethod = methodFilter.value === 'all' || payment.payment_method === methodFilter.value

    return matchesQuery && matchesStatus && matchesMethod
  })
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems: paginatedPayments,
  pageStart,
  pageEnd,
} = useClientPagination(filteredPayments, {
  defaultPageSize: 5,
  pageSizeOptions: [5, 10],
})

const stats = computed(() => {
  const total = payments.value.length
  const paid = payments.value.filter((item) => item.payment_status === 'paid').length
  const pending = payments.value.filter((item) => item.payment_status === 'pending').length
  const failed = payments.value.filter((item) => item.payment_status === 'failed').length

  return [
    {label: 'Tổng thanh toán', value: total, icon: 'bi-cash-stack', color: 'blue'},
    {label: 'Đã thanh toán', value: paid, icon: 'bi-check-circle-fill', color: 'green'},
    {label: 'Chờ xử lý', value: pending, icon: 'bi-hourglass-split', color: 'orange'},
    {label: 'Thất bại', value: failed, icon: 'bi-x-circle-fill', color: 'slate'},
  ]
})

const loadPage = async () => {
  pageLoading.value = true
  errorMessage.value = ''

  try {
    await paymentStore.fetchAll()
  } catch (error) {
    if (error.response?.status === 401) {
      await router.replace({name: 'login'})
      return
    }

    if (error.response?.status === 403) {
      await router.replace({name: 'forbidden'})
      return
    }

    errorMessage.value = error.response?.data?.message || 'Không tải được dữ liệu thanh toán.'
  } finally {
    pageLoading.value = false
  }
}

const goDetail = (id) => {
  router.push({name: 'admin.payments.show', params: {id}})
}

const removePayment = async (payment) => {
  if (!window.confirm(`Xóa thanh toán #${payment.id}?`)) {
    return
  }

  try {
    await paymentStore.remove(payment.id)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không xóa được thanh toán.'
  }
}

const paymentMethodLabel = (method) => methodMap[method] || method || 'Không rõ'
const paymentStatusLabel = (status) => statusMap[status] || statusMap.unpaid

onMounted(loadPage)
</script>

<template>
  <div class="admin-page">
    <div class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Quản lý thanh toán</p>
        <h1>Danh sách thanh toán</h1>
        <p class="subtitle">Theo dõi trạng thái thanh toán, phương thức và đơn hàng liên quan trong một màn hình.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="loadPage">
            <i class="bi bi-arrow-clockwise"></i>
            Tải lại
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <div v-for="stat in stats" :key="stat.label" class="stat-card">
          <div class="stat-icon" :class="stat.color">
            <i :class="`bi ${stat.icon}`"></i>
          </div>
          <div class="stat-content">
            <strong>{{ stat.value }}</strong>
            <span>{{ stat.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model.trim="searchQuery" type="text" placeholder="Tìm theo mã giao dịch, đơn hàng, khách..."/>
      </div>

      <select v-model="statusFilter" class="filter-select">
        <option value="all">Tất cả trạng thái</option>
        <option value="pending">Chờ thanh toán</option>
        <option value="paid">Đã thanh toán</option>
        <option value="failed">Thất bại</option>
        <option value="cancelled">Đã hủy</option>
        <option value="refunded">Đã hoàn tiền</option>
      </select>

      <select v-model="methodFilter" class="filter-select">
        <option value="all">Tất cả phương thức</option>
        <option value="cod">COD</option>
        <option value="bank_transfer">Chuyển khoản</option>
        <option value="momo">MoMo</option>
        <option value="vnpay">VNPay</option>
      </select>

      <div class="result-chip">
        <i class="bi bi-funnel"></i>
        {{ filteredPayments.length }} kết quả
      </div>
    </div>

    <p v-if="errorMessage" class="error-banner">{{ errorMessage }}</p>

    <div v-if="pageLoading" class="state-card">
      <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
      <p>Đang tải dữ liệu thanh toán...</p>
    </div>

    <div v-else class="table-card">
      <div v-if="!filteredPayments.length" class="empty-state">
        <i class="bi bi-cash-stack"></i>
        <p>Chưa có thanh toán nào phù hợp bộ lọc.</p>
      </div>

      <div v-else class="table-wrap">
        <table class="data-table">
          <colgroup>
            <col class="col-id"/>
            <col class="col-order"/>
            <col class="col-customer"/>
            <col class="col-method"/>
            <col class="col-status"/>
            <col class="col-amount"/>
            <col class="col-date"/>
            <col class="col-actions"/>
          </colgroup>

          <thead>
          <tr>
            <th>Mã TT</th>
            <th>Đơn hàng</th>
            <th>Khách hàng</th>
            <th>Phương thức</th>
            <th>Trạng thái</th>
            <th>Số tiền</th>
            <th>Ngày thanh toán</th>
            <th>Thao tác</th>
          </tr>
          </thead>

          <tbody>
          <tr v-for="payment in paginatedPayments" :key="payment.id">
            <td>
              <strong>#{{ payment.id }}</strong>
              <div class="muted">{{ payment.transaction_code || 'Không có mã GD' }}</div>
            </td>
            <td>
              <strong>{{ payment.order?.order_code || `#${payment.order_id}` }}</strong>
              <div class="muted">Đơn #{{ payment.order_id }}</div>
            </td>
            <td>
              <strong>{{ payment.order?.user?.name || payment.order?.receiver_name || 'Không rõ' }}</strong>
              <div class="muted">{{
                  payment.order?.user?.email || payment.order?.receiver_phone || 'Chưa có liên hệ'
                }}
              </div>
            </td>
            <td>
              <span class="method-pill">{{ paymentMethodLabel(payment.payment_method) }}</span>
            </td>
            <td>
              <PaymentStatusBadge :status="payment.payment_status"/>
            </td>
            <td>
              <strong>{{ formatCurrency(payment.amount || 0) }}</strong>
            </td>
            <td>
              <span>{{ formatDate(payment.paid_at || payment.updated_at || payment.created_at) }}</span>
            </td>
            <td>
              <div class="action-group">
                <button type="button" class="action-btn view" @click="goDetail(payment.id)">
                  <i class="bi bi-eye"></i>
                </button>
                <button type="button" class="action-btn danger" @click="removePayment(payment)">
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ListPaginationControls
        v-if="!pageLoading && !errorMessage"
        :current-page="currentPage"
        :total-pages="totalPages"
        :page-size="pageSize"
        :total-items="filteredPayments.length"
        :page-start="pageStart"
        :page-end="pageEnd"
        item-label="thanh toán"
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
.table-card {
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.hero-card {
  padding: 24px;
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
  gap: 18px;
  background: linear-gradient(135deg, #ffffff, #f4f8ff);
}

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 34px;
  font-weight: 900;
}

.subtitle {
  margin: 10px 0 0;
  color: #64748b;
  font-size: 15px;
  line-height: 1.6;
  max-width: 720px;
}

.hero-actions {
  margin-top: 18px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.primary-action {
  min-height: 44px;
  padding: 0 18px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  border: none;
  color: #ffffff;
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  box-shadow: 0 12px 26px rgba(37, 99, 235, 0.2);
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  align-content: start;
}

.stat-card {
  min-height: 104px;
  padding: 18px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 14px;
}

.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  color: #ffffff;
  font-size: 22px;
}

.stat-icon.blue {
  background: linear-gradient(135deg, #2563eb, #3b82f6);
}

.stat-icon.green {
  background: linear-gradient(135deg, #16a34a, #22c55e);
}

.stat-icon.orange {
  background: linear-gradient(135deg, #f59e0b, #fb923c);
}

.stat-icon.slate {
  background: linear-gradient(135deg, #475569, #64748b);
}

.stat-content strong {
  display: block;
  color: #0f172a;
  font-size: 26px;
  font-weight: 900;
  line-height: 1;
}

.stat-content span {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 14px;
  font-weight: 700;
}

.toolbar-card {
  padding: 14px;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 220px 220px auto;
  gap: 12px;
  align-items: center;
}

.search-box {
  min-height: 48px;
  padding: 0 16px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 10px;
}

.search-box i {
  color: #64748b;
  font-size: 18px;
}

.search-box input,
.filter-select {
  width: 100%;
  border: 0;
  outline: none;
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
  background: transparent;
}

.filter-select {
  min-height: 48px;
  padding: 0 16px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
}

.result-chip {
  min-height: 48px;
  padding: 0 16px;
  border-radius: 999px;
  background: #eef4ff;
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  white-space: nowrap;
}

.error-banner {
  margin: 0;
  padding: 14px 16px;
  border-radius: 14px;
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #b91c1c;
  font-weight: 700;
}

.state-card {
  min-height: 220px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
  display: grid;
  place-items: center;
  gap: 12px;
  color: #64748b;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.05);
}

.table-card {
  overflow: hidden;
}

.table-wrap {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  min-width: 1240px;
  border-collapse: collapse;
  table-layout: fixed;
}

.data-table th,
.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #edf2f7;
  text-align: left;
  vertical-align: middle;
}

.data-table th {
  color: #64748b;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}

.data-table td {
  color: #0f172a;
  font-size: 14px;
  font-weight: 600;
}

.muted {
  margin-top: 4px;
  color: #64748b;
  font-size: 12px;
  font-weight: 500;
}

.method-pill {
  display: inline-flex;
  align-items: center;
  min-height: 32px;
  padding: 0 12px;
  border-radius: 999px;
  background: #eff6ff;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
}

.action-group {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 36px;
  height: 36px;
  border: 0;
  border-radius: 12px;
  display: inline-grid;
  place-items: center;
}

.action-btn.view {
  background: #eef4ff;
  color: #2563eb;
}

.action-btn.danger {
  background: #fff1f2;
  color: #dc2626;
}

.empty-state {
  min-height: 240px;
  display: grid;
  place-items: center;
  text-align: center;
  color: #64748b;
}

.empty-state i {
  margin-bottom: 10px;
  font-size: 34px;
  color: #2563eb;
}

.col-id {
  width: 14%;
}

.col-order {
  width: 15%;
}

.col-customer {
  width: 18%;
}

.col-method {
  width: 12%;
}

.col-status {
  width: 14%;
}

.col-amount {
  width: 12%;
}

.col-date {
  width: 15%;
}

.col-actions {
  width: 10%;
}

@media (max-width: 1199.98px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .toolbar-card {
    grid-template-columns: 1fr;
  }
}
</style>
