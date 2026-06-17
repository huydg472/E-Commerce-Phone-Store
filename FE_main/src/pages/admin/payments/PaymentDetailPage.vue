<script setup>
import {computed, onMounted, reactive, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import OrderItem from '@/components/order/OrderItem.vue'
import OrderStatusBadge from '@/components/order/OrderStatusBadge.vue'
import PaymentStatusBadge from '@/components/payment/PaymentStatusBadge.vue'
import {usePaymentStore} from '@/stores/paymentStore'
import {formatCurrency} from '@/utils/formatCurrency'
import {formatDate} from '@/utils/formatDate'

const route = useRoute()
const router = useRouter()
const paymentStore = usePaymentStore()

const pageLoading = ref(true)
const errorMessage = ref('')
const paymentMethodMap = {
  cod: 'COD',
  bank_transfer: 'Chuyển khoản',
  momo: 'MoMo',
  vnpay: 'VNPay',
}

const form = reactive({
  payment_status: 'pending',
  note: '',
})

const payment = computed(() => paymentStore.item)
const order = computed(() => payment.value?.order ?? null)
const orderItems = computed(() => {
  const source = order.value?.orderItems ?? order.value?.order_items ?? []
  return Array.isArray(source) ? source : []
})

const loadPage = async () => {
  pageLoading.value = true
  errorMessage.value = ''

  try {
    await paymentStore.fetchById(route.params.id)
  } catch (error) {
    if (error.response?.status === 401) {
      await router.replace({name: 'login'})
      return
    }

    if (error.response?.status === 403) {
      await router.replace({name: 'forbidden'})
      return
    }

    errorMessage.value = error.response?.data?.message || 'Không tải được chi tiết thanh toán.'
  } finally {
    pageLoading.value = false
  }
}

const syncForm = () => {
  form.payment_status = payment.value?.payment_status || 'pending'
  form.note = payment.value?.note || ''
}

const goBack = () => {
  router.push({name: 'admin.payments.index'})
}

const paymentMethodLabel = computed(() => {
  return paymentMethodMap[payment.value?.payment_method] || payment.value?.payment_method || 'Không rõ'
})

const totalAmount = computed(() => Number(payment.value?.amount ?? order.value?.total_amount ?? 0))

const orderSummary = computed(() => {
  return [
    {label: 'Mã đơn', value: order.value?.order_code || `#${order.value?.id ?? ''}`},
    {label: 'Khách hàng', value: order.value?.user?.name || order.value?.receiver_name || 'Không rõ'},
    {label: 'Email', value: order.value?.user?.email || 'Không rõ'},
    {label: 'Số điện thoại', value: order.value?.receiver_phone || order.value?.user?.phone || 'Không rõ'},
    {label: 'Trạng thái đơn', value: order.value?.order_status || 'pending'},
    {label: 'Ngày đặt', value: formatDate(order.value?.ordered_at || order.value?.created_at)},
  ]
})

watch(payment, (value) => {
  if (value) {
    syncForm()
  }
})

watch(
    () => route.params.id,
    () => {
      void loadPage()
    }
)

onMounted(loadPage)
</script>

<template>
  <div class="admin-page">
    <div class="page-head">
      <div>
        <p class="eyebrow">Chi tiết thanh toán</p>
        <h1>#{{ payment?.id || '' }}</h1>
        <p class="subtitle">Theo dõi thanh toán gắn với đơn hàng, phương thức và trạng thái xử lý.</p>
      </div>

      <button type="button" class="secondary-action" @click="goBack">
        <i class="bi bi-arrow-left"></i>
        Quay lại
      </button>
    </div>

    <div v-if="pageLoading" class="state-card">
      <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
      <p>Đang tải chi tiết thanh toán...</p>
    </div>

    <p v-else-if="errorMessage" class="error-banner">{{ errorMessage }}</p>

    <template v-else-if="payment">
      <div class="hero-card">
        <div class="hero-main">
          <PaymentStatusBadge :status="payment.payment_status"/>
          <div class="hero-line">
            <strong>Mã giao dịch:</strong>
            <span>{{ payment.transaction_code || 'Chưa có' }}</span>
          </div>
          <div class="hero-line">
            <strong>Phương thức:</strong>
            <span>{{ paymentMethodLabel }}</span>
          </div>
        </div>

        <div class="hero-meta">
          <div class="meta-chip">
            <span>Số tiền</span>
            <strong>{{ formatCurrency(totalAmount) }}</strong>
          </div>
          <div class="meta-chip">
            <span>Đơn hàng</span>
            <strong>{{ order?.order_code || `#${order?.id ?? ''}` }}</strong>
          </div>
          <div class="meta-chip">
            <span>Thanh toán lúc</span>
            <strong>{{ formatDate(payment.paid_at || payment.updated_at || payment.created_at) }}</strong>
          </div>
        </div>
      </div>

      <div class="detail-layout">
        <section class="detail-card">
          <h2>Thông tin thanh toán</h2>

          <div class="info-grid">
            <div>
              <span>Đơn hàng</span>
              <strong>{{ order?.order_code || `#${order?.id ?? ''}` }}</strong>
              <small>{{ order?.receiver_name || 'Không có người nhận' }}</small>
            </div>
            <div>
              <span>Khách hàng</span>
              <strong>{{ order?.user?.name || order?.receiver_name || 'Không rõ' }}</strong>
              <small>{{ order?.user?.email || 'Không có email' }}</small>
            </div>
            <div>
              <span>Phương thức</span>
              <strong>{{ paymentMethodLabel }}</strong>
            </div>
            <div>
              <span>Trạng thái</span>
              <PaymentStatusBadge :status="payment.payment_status"/>
            </div>
            <div>
              <span>Số tiền</span>
              <strong>{{ formatCurrency(totalAmount) }}</strong>
            </div>
            <div>
              <span>Mã giao dịch</span>
              <strong>{{ payment.transaction_code || 'Chưa có' }}</strong>
            </div>
          </div>

          <div class="note-box" v-if="payment.note">
            <span>Ghi chú thanh toán</span>
            <p>{{ payment.note }}</p>
          </div>

          <div class="note-box" v-if="order?.note">
            <span>Ghi chú đơn hàng</span>
            <p>{{ order.note }}</p>
          </div>
        </section>

        <section class="detail-card">
          <h2>Thông tin đơn hàng</h2>

          <div class="info-grid">
            <div v-for="item in orderSummary" :key="item.label">
              <span>{{ item.label }}</span>
              <strong v-if="item.label === 'Trạng thái đơn'">
                <OrderStatusBadge :status="item.value"/>
              </strong>
              <strong v-else>{{ item.value }}</strong>
            </div>
          </div>

          <div class="address-box">
            <span>Địa chỉ giao hàng</span>
            <p>{{ order?.shipping_address_text || 'Chưa có địa chỉ' }}</p>
          </div>
        </section>

        <aside class="detail-card summary-card">
          <h2>Sản phẩm trong đơn</h2>

          <div v-if="orderItems.length" class="item-list">
            <OrderItem v-for="item in orderItems" :key="item.id" :item="item"/>
          </div>

          <div v-else class="empty-state">
            <i class="bi bi-bag-x"></i>
            <p>Đơn hàng chưa có danh sách sản phẩm chi tiết.</p>
          </div>
        </aside>
      </div>
    </template>
  </div>
</template>

<style scoped>
.admin-page {
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
  font-size: 28px;
  font-weight: 900;
}

.subtitle {
  margin: 8px 0 0;
  color: #64748b;
  line-height: 1.6;
}

.secondary-action {
  min-height: 42px;
  padding: 0 14px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
  border: none;
  background: #ffffff;
  border: 1px solid #dbe3ef;
  color: #334155;
}

.state-card,
.hero-card,
.detail-card {
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.state-card {
  min-height: 220px;
  display: grid;
  place-items: center;
  gap: 12px;
  color: #64748b;
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

.hero-card {
  padding: 18px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  background: linear-gradient(135deg, #ffffff, #f8fbff);
}

.hero-main {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.hero-line {
  display: flex;
  gap: 8px;
  color: #475569;
}

.hero-line strong {
  color: #0f172a;
}

.hero-meta {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 10px;
  min-width: 440px;
}

.meta-chip {
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid #e5eaf3;
  background: #ffffff;
}

.meta-chip span {
  display: block;
  margin-bottom: 5px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.meta-chip strong {
  color: #0f172a;
}

.detail-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr) minmax(0, 1.2fr);
  gap: 12px;
}

.detail-card {
  padding: 16px;
}

.detail-card h2 {
  margin: 0 0 14px;
  font-size: 17px;
  font-weight: 850;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.info-grid div,
.summary-total,
.address-box,
.note-box {
  padding: 11px 12px;
  border: 1px solid #eef2f7;
  border-radius: 11px;
  background: #fafcff;
}

.info-grid span,
.summary-total span,
.address-box span,
.note-box span {
  display: block;
  margin-bottom: 6px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.info-grid strong,
.summary-total strong {
  color: #111827;
}

.info-grid small {
  display: block;
  margin-top: 4px;
  color: #94a3b8;
}

.note-box,
.address-box {
  margin-top: 14px;
}

.note-box p,
.address-box p {
  margin: 0;
  color: #111827;
  line-height: 1.6;
}

.summary-card {
  align-self: start;
}

.item-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.empty-state {
  min-height: 220px;
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

@media (max-width: 1199.98px) {
  .hero-card,
  .detail-layout {
    grid-template-columns: 1fr;
  }

  .hero-card {
    display: grid;
  }

  .hero-meta {
    min-width: 0;
  }
}

@media (max-width: 767.98px) {
  .page-head {
    flex-direction: column;
    align-items: stretch;
  }

  .secondary-action {
    width: 100%;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }
}
</style>
