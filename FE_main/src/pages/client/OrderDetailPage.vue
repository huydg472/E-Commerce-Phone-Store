<script setup>
import {computed, onMounted, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useOrderStore} from '@/stores/orderStore'
import {formatCurrency} from '@/utils/formatCurrency'
import {formatDate} from '@/utils/formatDate'

const route = useRoute()
const router = useRouter()
const orderStore = useOrderStore()

const pageLoading = ref(true)
const errorMessage = ref('')

const statusMap = {
  pending: {label: 'Chờ xác nhận', className: 'pending'},
  confirmed: {label: 'Đã xác nhận', className: 'confirmed'},
  shipping: {label: 'Đang giao', className: 'shipping'},
  completed: {label: 'Hoàn thành', className: 'completed'},
  cancelled: {label: 'Đã hủy', className: 'cancelled'},
}

const paymentStatusMap = {
  unpaid: {label: 'Chưa thanh toán', className: 'unpaid'},
  paid: {label: 'Đã thanh toán', className: 'paid'},
  failed: {label: 'Thanh toán thất bại', className: 'failed'},
  refunded: {label: 'Đã hoàn tiền', className: 'refunded'},
}

const order = computed(() => {
  return orderStore.item ?? null
})

const orderItems = computed(() => {
  const source = order.value?.orderItems ?? order.value?.order_items ?? []
  return Array.isArray(source) ? source : []
})

const totalValue = computed(() => Number(order.value?.total_amount ?? 0))

const loadOrder = async () => {
  pageLoading.value = true
  errorMessage.value = ''

  try {
    await orderStore.fetchById(route.params.id)
  } catch (error) {
    if (error.response?.status === 401) {
      await router.replace({name: 'login'})
      return
    }

    if (error.response?.status === 403) {
      await router.replace({name: 'forbidden'})
      return
    }

    errorMessage.value = error.response?.data?.message || 'Không tải được chi tiết đơn hàng.'
  } finally {
    pageLoading.value = false
  }
}

const goBack = () => {
  router.push({name: 'orders.history'})
}

watch(
    () => route.params.id,
    () => {
      void loadOrder()
    }
)

onMounted(loadOrder)
</script>

<template>
  <main class="order-detail-page">
    <div class="detail-backdrop">
      <section class="detail-modal">
      <div class="breadcrumb-wrap">
        <RouterLink to="/">Trang chủ</RouterLink>
        <span>/</span>
        <RouterLink :to="{ name: 'orders.history' }">Đơn hàng của tôi</RouterLink>
        <span>/</span>
        <strong>Chi tiết đơn hàng</strong>
      </div>

      <div v-if="pageLoading" class="detail-loading">
        <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
      </div>

      <p v-else-if="errorMessage" class="error-message">
        {{ errorMessage }}
      </p>

      <template v-else-if="order">
        <div class="page-header">
          <div>
            <h1>Chi tiết đơn hàng</h1>
            <p>Mã đơn hàng: <strong>{{ order.order_code || `#${order.id}` }}</strong></p>
          </div>

          <div class="header-actions">
            <span class="status-badge" :class="statusMap[order.order_status]?.className || 'pending'">
              {{ statusMap[order.order_status]?.label || order.order_status }}
            </span>
            <button type="button" class="back-btn" @click="goBack">Quay lại danh sách</button>
          </div>
        </div>

        <div class="detail-layout">
          <section class="detail-card">
            <h2>Thông tin đơn hàng</h2>

            <div class="info-grid">
              <div>
                <span>Ngày đặt</span>
                <strong>{{ formatDate(order.ordered_at || order.created_at) }}</strong>
              </div>
              <div>
                <span>Thanh toán</span>
                <strong class="payment-status" :class="paymentStatusMap[order.payment_status]?.className || 'unpaid'">
                  {{ paymentStatusMap[order.payment_status]?.label || order.payment_status }}
                </strong>
              </div>
              <div>
                <span>Người nhận</span>
                <strong>{{ order.receiver_name }}</strong>
              </div>
              <div>
                <span>Số điện thoại</span>
                <strong>{{ order.receiver_phone }}</strong>
              </div>
            </div>

            <div class="address-box">
              <span>Địa chỉ giao hàng</span>
              <p>{{ order.shipping_address_text }}</p>
            </div>

            <div v-if="order.note" class="note-box">
              <span>Ghi chú</span>
              <p>{{ order.note }}</p>
            </div>
          </section>

          <section class="detail-card">
            <h2>Sản phẩm</h2>

            <div v-if="orderItems.length" class="item-list">
              <article v-for="item in orderItems" :key="item.id" class="item-row">
                <img
                    :src="item.productVariant?.product?.thumbnail_url || item.productVariant?.product?.image || '/images/default-product.png'"
                    :alt="item.product_name"
                />

                <div class="item-info">
                  <h3>{{ item.product_name }}</h3>
                  <p>{{ item.variant_name }}</p>
                  <p>Mã SKU: {{ item.sku || 'N/A' }}</p>
                </div>

                <div class="item-meta">
                  <span>{{ item.quantity }} x {{ formatCurrency(item.unit_price) }}</span>
                  <strong>{{ formatCurrency(item.total_price) }}</strong>
                </div>
              </article>
            </div>

            <div v-else class="empty-state">
              <i class="bi bi-bag-x"></i>
              <p>Đơn hàng này chưa có danh sách sản phẩm chi tiết.</p>
            </div>
          </section>

          <aside class="detail-card summary-card">
            <h2>Tóm tắt</h2>

            <div class="summary-line">
              <span>Tạm tính</span>
              <strong>{{ formatCurrency(order.subtotal || 0) }}</strong>
            </div>

            <div class="summary-line">
              <span>Phí vận chuyển</span>
              <strong>{{ formatCurrency(order.shipping_fee || 0) }}</strong>
            </div>

            <div class="summary-line">
              <span>Giảm giá</span>
              <strong class="discount">{{ formatCurrency(order.discount_amount || 0) }}</strong>
            </div>

            <div class="summary-total">
              <span>Tổng cộng</span>
              <strong>{{ formatCurrency(totalValue) }}</strong>
            </div>
          </aside>
        </div>
      </template>
      </section>
    </div>
  </main>
</template>

<style scoped>
.order-detail-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 32%),
    radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.06), transparent 30%),
    #eff4fb;
  color: #111827;
}

.detail-backdrop {
  min-height: 100vh;
  width: 100%;
  display: block;
  padding: 0;
}

.detail-modal {
  width: 100%;
  min-height: 100vh;
  margin: 0 auto;
  padding: 28px 32px 36px;
  border-radius: 0;
  border: 0;
  background: rgba(255, 255, 255, 0.98);
  box-shadow: none;
  overflow: auto;
}

.detail-modal::-webkit-scrollbar {
  width: 10px;
}

.detail-modal::-webkit-scrollbar-thumb {
  border-radius: 999px;
  background: rgba(148, 163, 184, 0.55);
}

.breadcrumb-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6b7280;
  margin-bottom: 14px;
}

.breadcrumb-wrap a {
  color: inherit;
  text-decoration: none;
}

.breadcrumb-wrap strong {
  color: #2563eb;
}

.detail-loading {
  min-height: 240px;
  display: grid;
  place-items: center;
}

.error-message {
  margin: 0;
  color: #dc2626;
  font-weight: 600;
}

.page-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
  margin-bottom: 16px;
}

.page-header h1 {
  margin: 0 0 6px;
  font-size: 28px;
  font-weight: 850;
}

.page-header p {
  margin: 0;
  color: #64748b;
}

.header-actions {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
}

.status-badge {
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
}

.status-badge.shipping {
  background: #dbeafe;
  color: #2563eb;
}

.status-badge.completed {
  background: #dcfce7;
  color: #16a34a;
}

.status-badge.pending {
  background: #ffedd5;
  color: #f97316;
}

.status-badge.cancelled {
  background: #f1f5f9;
  color: #64748b;
}

.status-badge.confirmed {
  background: #ede9fe;
  color: #7c3aed;
}

.back-btn {
  height: 36px;
  padding: 0 14px;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  background: #ffffff;
  color: #334155;
  font-weight: 700;
}

.detail-layout {
  display: grid;
  grid-template-columns: minmax(320px, 0.95fr) minmax(420px, 1.25fr) minmax(300px, 0.92fr);
  gap: 18px;
  align-items: start;
}

.detail-card {
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 18px;
  background: #ffffff;
  min-width: 0;
  box-shadow: 0 10px 26px rgba(15, 23, 42, 0.04);
}

.detail-card h2 {
  margin: 0 0 16px;
  font-size: 18px;
  font-weight: 850;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.info-grid div,
.summary-line,
.summary-total {
  padding: 12px 14px;
  border: 1px solid #eef2f7;
  border-radius: 10px;
  background: #fafcff;
}

.info-grid span,
.summary-line span,
.summary-total span {
  display: block;
  margin-bottom: 6px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.info-grid strong {
  color: #111827;
}

.payment-status.unpaid {
  color: #f97316;
}

.payment-status.paid {
  color: #16a34a;
}

.payment-status.failed {
  color: #dc2626;
}

.payment-status.refunded {
  color: #7c3aed;
}

.address-box,
.note-box {
  margin-top: 14px;
  padding: 14px;
  border: 1px solid #eef2f7;
  border-radius: 10px;
  background: #fafcff;
}

.address-box span,
.note-box span {
  display: block;
  margin-bottom: 6px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.address-box p,
.note-box p {
  margin: 0;
  color: #111827;
  line-height: 1.5;
}

.item-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.item-row {
  display: grid;
  grid-template-columns: 96px minmax(0, 1fr);
  gap: 16px;
  align-items: start;
  padding: 12px;
  border: 1px solid #eef2f7;
  border-radius: 14px;
  background: #ffffff;
}

.item-row img {
  width: 96px;
  height: 96px;
  border-radius: 14px;
  object-fit: cover;
  background: #f3f4f6;
  flex-shrink: 0;
}

.item-info h3 {
  margin: 0 0 5px;
  font-size: 15px;
  font-weight: 800;
  line-height: 1.35;
  word-break: normal;
}

.item-info p {
  margin: 0 0 2px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.45;
  word-break: normal;
}

.item-meta {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px 14px;
  margin-top: 4px;
  min-width: 0;
  grid-column: 2;
  text-align: left;
}

.item-meta span {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #64748b;
  font-size: 13px;
}

.item-meta strong {
  color: #0d6efd;
  font-size: 15px;
  font-weight: 850;
}

.item-meta strong:last-child {
  margin-left: auto;
  font-size: 16px;
}

.empty-state {
  padding: 24px;
  text-align: center;
  color: #64748b;
}

.empty-state i {
  font-size: 32px;
  color: #0d6efd;
}

.summary-card {
  align-self: start;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
}

.summary-line + .summary-line {
  margin-top: 10px;
}

.summary-line strong {
  color: #111827;
}

.summary-line .discount {
  color: #dc2626;
}

.summary-total {
  margin-top: 14px;
  border-color: #bfdbfe;
  background: linear-gradient(135deg, #eff6ff 0%, #e0f2fe 100%);
}

.summary-total strong {
  color: #0d6efd;
  font-size: 20px;
  font-weight: 900;
}

@media (max-width: 1200px) {
  .detail-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .order-detail-page {
    padding: 0;
  }

  .detail-backdrop {
    padding: 0;
  }

  .detail-modal {
    width: 100%;
    min-height: 100vh;
    padding: 16px;
  }

  .page-header {
    flex-direction: column;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }

  .item-row {
    grid-template-columns: 72px minmax(0, 1fr);
  }

  .item-row img {
    width: 72px;
    height: 72px;
  }

  .item-meta {
    grid-column: 1 / -1;
    text-align: left;
    align-self: auto;
    justify-content: flex-start;
  }
}
</style>
