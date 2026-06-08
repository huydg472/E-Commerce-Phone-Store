<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useOrderStore } from '@/stores/orderStore'
import { formatCurrency } from '@/utils/formatCurrency'
import { formatDate } from '@/utils/formatDate'

const route = useRoute()
const router = useRouter()
const orderStore = useOrderStore()

const pageLoading = ref(true)
const errorMessage = ref('')
const saving = ref(false)

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

const form = reactive({
  order_status: 'pending',
  note: '',
})

const order = computed(() => orderStore.item)

const orderItems = computed(() => {
  const source = order.value?.orderItems ?? order.value?.order_items ?? []
  return Array.isArray(source) ? source : []
})

const payment = computed(() => order.value?.payment ?? null)
const shippingAddress = computed(() => order.value?.shippingAddress ?? order.value?.shipping_address ?? null)
const customer = computed(() => order.value?.user ?? null)
const totalValue = computed(() => Number(order.value?.total_amount ?? 0))

const paymentMethodLabel = computed(() => {
  return paymentMethodMap[payment.value?.payment_method] || payment.value?.payment_method || 'COD'
})

const statusSteps = computed(() => {
  const steps = ['pending', 'confirmed', 'processing', 'shipping', 'completed']
  const current = order.value?.order_status || 'pending'
  const currentIndex = steps.indexOf(current)
  const cancelled = current === 'cancelled'

  return steps.map((step, index) => ({
    key: step,
    label: orderStatusMap[step]?.label || step,
    done: !cancelled && currentIndex >= index,
    current: !cancelled && current === step,
  })).concat(cancelled ? [{
    key: 'cancelled',
    label: orderStatusMap.cancelled.label,
    done: true,
    current: true,
  }] : [])
})

const shippingAddressText = computed(() => {
  const parts = [
    shippingAddress.value?.address_detail,
    shippingAddress.value?.ward,
    shippingAddress.value?.district,
    shippingAddress.value?.province,
  ].filter(Boolean)

  if (parts.length) {
    return parts.join(', ')
  }

  return order.value?.shipping_address_text || 'Chưa có địa chỉ giao hàng'
})

const loadOrder = async () => {
  pageLoading.value = true
  errorMessage.value = ''

  try {
    await orderStore.fetchById(route.params.id)
  } catch (error) {
    if (error.response?.status === 401) {
      await router.replace({ name: 'login' })
      return
    }

    if (error.response?.status === 403) {
      await router.replace({ name: 'forbidden' })
      return
    }

    errorMessage.value = error.response?.data?.message || 'Không tải được chi tiết đơn hàng.'
  } finally {
    pageLoading.value = false
  }
}

const syncForm = () => {
  form.order_status = order.value?.order_status || 'pending'
  form.note = order.value?.note || payment.value?.note || ''
}

const goBack = () => {
  router.push({ name: 'admin.orders.index' })
}

const saveChanges = async () => {
  if (!order.value || saving.value) {
    return
  }

  saving.value = true
  errorMessage.value = ''

  try {
    await orderStore.update(order.value.id, {
      order_status: form.order_status,
      note: form.note.trim() || null,
    })
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không cập nhật được đơn hàng.'
  } finally {
    saving.value = false
  }
}

const quickSetStatus = async (status) => {
  form.order_status = status
  await saveChanges()
}

watch(order, (value) => {
  if (value) {
    syncForm()
  }
})

watch(
  () => route.params.id,
  () => {
    void loadOrder()
  }
)

onMounted(loadOrder)
</script>

<template>
  <div class="admin-page">
    <div class="page-head">
      <div>
        <p class="eyebrow">Chi tiết đơn hàng</p>
        <h1>{{ order?.order_code || `#${order?.id ?? ''}` }}</h1>
        <p class="subtitle">Theo dõi trạng thái, thanh toán và danh sách sản phẩm của đơn.</p>
      </div>

      <button type="button" class="secondary-action" @click="goBack">
        <i class="bi bi-arrow-left"></i>
        Quay lại
      </button>
    </div>

    <div v-if="pageLoading" class="state-card">
      <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
      <p>Đang tải chi tiết đơn hàng...</p>
    </div>

    <p v-else-if="errorMessage" class="error-banner">
      {{ errorMessage }}
    </p>

    <template v-else-if="order">
      <div class="hero-card">
        <div class="hero-main">
          <span class="status-badge" :class="orderStatusMap[order.order_status]?.className || 'pending'">
            {{ orderStatusMap[order.order_status]?.label || order.order_status }}
          </span>
          <div class="hero-line">
            <strong>Mã đơn:</strong>
            <span>{{ order.order_code || `#${order.id}` }}</span>
          </div>
          <div class="hero-line">
            <strong>Đặt lúc:</strong>
            <span>{{ formatDate(order.ordered_at || order.created_at) }}</span>
          </div>
        </div>

        <div class="hero-meta">
          <div class="meta-chip">
            <span>Thanh toán</span>
            <strong :class="paymentStatusMap[order.payment_status]?.className || 'unpaid'">
              {{ paymentStatusMap[order.payment_status]?.label || order.payment_status || 'Chưa thanh toán' }}
            </strong>
          </div>
          <div class="meta-chip">
            <span>Phương thức</span>
            <strong>{{ paymentMethodLabel }}</strong>
          </div>
          <div class="meta-chip">
            <span>Tổng tiền</span>
            <strong>{{ formatCurrency(totalValue) }}</strong>
          </div>
        </div>
      </div>

      <div class="detail-layout">
        <section class="detail-card">
          <h2>Thông tin đơn</h2>

          <div class="info-grid">
            <div>
              <span>Khách hàng</span>
              <strong>{{ customer?.name || order.receiver_name || 'Chưa có tên' }}</strong>
              <small>{{ customer?.email || 'Chưa có email' }}</small>
            </div>
            <div>
              <span>Số điện thoại</span>
              <strong>{{ order.receiver_phone || customer?.phone || 'Chưa có số điện thoại' }}</strong>
              <small>{{ customer?.username || 'Chưa có username' }}</small>
            </div>
            <div>
              <span>Tạm tính</span>
              <strong>{{ formatCurrency(order.subtotal || 0) }}</strong>
            </div>
            <div>
              <span>Phí vận chuyển</span>
              <strong>{{ formatCurrency(order.shipping_fee || 0) }}</strong>
            </div>
            <div>
              <span>Giảm giá</span>
              <strong class="discount">{{ formatCurrency(order.discount_amount || 0) }}</strong>
            </div>
            <div>
              <span>Trạng thái giao</span>
              <strong>{{ orderStatusMap[order.order_status]?.label || order.order_status }}</strong>
            </div>
          </div>

          <div class="address-box">
            <span>Địa chỉ giao hàng</span>
            <p>{{ shippingAddressText }}</p>
          </div>

          <div v-if="order.note || payment?.note" class="note-box">
            <span>Ghi chú</span>
            <p>{{ order.note || payment?.note }}</p>
          </div>

          <div v-if="shippingAddress" class="shipping-box">
            <span>Địa chỉ lưu hệ thống</span>
            <p>
              {{ shippingAddress.receiver_name }} - {{ shippingAddress.receiver_phone }}
            </p>
            <p class="muted">
              {{ [shippingAddress.address_detail, shippingAddress.ward, shippingAddress.district, shippingAddress.province].filter(Boolean).join(', ') || 'Không có dữ liệu' }}
            </p>
          </div>
        </section>

        <section class="detail-card">
          <h2>Sản phẩm</h2>

          <div v-if="orderItems.length" class="item-list">
            <article v-for="item in orderItems" :key="item.id" class="item-row">
              <img
                :src="item.productVariant?.product?.thumbnail_url || item.productVariant?.product?.thumbnailUrl || item.productVariant?.product?.image || '/images/default-product.png'"
                :alt="item.product_name"
              />

              <div class="item-info">
                <h3>{{ item.product_name }}</h3>
                <p>{{ item.variant_name || 'Không có biến thể' }}</p>
                <p>SKU: {{ item.sku || 'N/A' }}</p>
                <p>Số lượng: {{ item.quantity }}</p>
              </div>

              <div class="item-meta">
                <span>{{ formatCurrency(item.unit_price) }} x {{ item.quantity }}</span>
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
          <h2>Tóm tắt & xử lý</h2>

          <div class="summary-line">
            <span>Đơn hàng</span>
            <strong>{{ order.order_code || `#${order.id}` }}</strong>
          </div>

          <div class="summary-line">
            <span>Thanh toán</span>
            <strong :class="paymentStatusMap[order.payment_status]?.className || 'unpaid'">
              {{ paymentStatusMap[order.payment_status]?.label || order.payment_status || 'Chưa thanh toán' }}
            </strong>
          </div>

          <div class="summary-line">
            <span>Trạng thái hiện tại</span>
            <strong>{{ orderStatusMap[form.order_status]?.label || form.order_status }}</strong>
          </div>

          <div class="timeline-box">
            <span>Dòng trạng thái</span>
            <div class="timeline">
              <div
                v-for="step in statusSteps"
                :key="step.key"
                class="timeline-step"
                :class="{ done: step.done, current: step.current }"
              >
                <i :class="step.done ? 'bi bi-check-circle-fill' : 'bi bi-circle'"></i>
                <span>{{ step.label }}</span>
              </div>
            </div>
          </div>

          <div class="form-stack">
            <label>
              <span>Đổi trạng thái</span>
              <select v-model="form.order_status" class="control">
                <option value="pending">Chờ xác nhận</option>
                <option value="confirmed">Đã xác nhận</option>
                <option value="processing">Đang xử lý</option>
                <option value="shipping">Đang giao</option>
                <option value="completed">Hoàn thành</option>
                <option value="cancelled">Đã hủy</option>
              </select>
            </label>

            <label>
              <span>Ghi chú nội bộ</span>
              <textarea
                v-model.trim="form.note"
                class="control"
                rows="4"
                placeholder="Ghi chú xử lý đơn hàng..."
              ></textarea>
            </label>

            <div class="quick-actions">
              <button type="button" class="quick-btn" @click="quickSetStatus('confirmed')">Xác nhận</button>
              <button type="button" class="quick-btn" @click="quickSetStatus('shipping')">Đang giao</button>
              <button type="button" class="quick-btn" @click="quickSetStatus('completed')">Hoàn thành</button>
              <button type="button" class="quick-btn danger" @click="quickSetStatus('cancelled')">Hủy</button>
            </div>

            <button type="button" class="primary-action" :disabled="saving" @click="saveChanges">
              <span v-if="saving" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
              <span>Lưu thay đổi</span>
            </button>
          </div>

          <div class="summary-total">
            <span>Tổng cộng</span>
            <strong>{{ formatCurrency(totalValue) }}</strong>
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
  letter-spacing: 0.06em;
}

.page-head h1 {
  margin: 0;
  color: #0f172a;
  font-size: 28px;
  font-weight: 850;
}

.subtitle {
  margin: 8px 0 0;
  color: #64748b;
}

.secondary-action,
.primary-action,
.quick-btn {
  min-width: 140px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 0 16px;
  border-radius: 12px;
  font-weight: 800;
  border: 1px solid transparent;
}

.secondary-action {
  color: #334155;
  background: #ffffff;
  border-color: #dbe3ef;
}

.primary-action {
  color: #ffffff;
  background: linear-gradient(135deg, #2563eb, #0ea5e9);
  box-shadow: 0 12px 28px rgba(37, 99, 235, 0.22);
}

.state-card {
  min-height: 220px;
  border: 1px solid #e5e9f1;
  border-radius: 16px;
  background: #ffffff;
  display: grid;
  place-items: center;
  gap: 12px;
  color: #64748b;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.05);
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
  padding: 16px 18px;
  border: 1px solid #e5e9f1;
  border-radius: 16px;
  background: linear-gradient(135deg, #ffffff, #f8fbff);
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.05);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.hero-main {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.status-badge {
  width: fit-content;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
}

.status-badge.pending {
  color: #c2410c;
  background: #fff7ed;
}

.status-badge.confirmed {
  color: #7c3aed;
  background: #f5f3ff;
}

.status-badge.processing {
  color: #2563eb;
  background: #eff6ff;
}

.status-badge.shipping {
  color: #0f766e;
  background: #ecfeff;
}

.status-badge.completed {
  color: #15803d;
  background: #ecfdf5;
}

.status-badge.cancelled {
  color: #dc2626;
  background: #fef2f2;
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
  gap: 8px;
  min-width: 420px;
}

.meta-chip {
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid #e5e9f1;
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

.meta-chip strong.unpaid,
.meta-chip strong.pending {
  color: #b45309;
}

.meta-chip strong.paid {
  color: #15803d;
}

.detail-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.06fr) minmax(0, 1fr) 320px;
  gap: 12px;
}

.detail-card {
  padding: 16px;
  border: 1px solid #e5e9f1;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.05);
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
.summary-line,
.summary-total,
.address-box,
.note-box,
.shipping-box,
.timeline-box {
  padding: 11px 12px;
  border: 1px solid #eef2f7;
  border-radius: 11px;
  background: #fafcff;
}

.info-grid span,
.summary-line span,
.summary-total span,
.address-box span,
.note-box span,
.shipping-box span,
.timeline-box span {
  display: block;
  margin-bottom: 6px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.info-grid strong,
.summary-line strong,
.summary-total strong {
  color: #111827;
}

.info-grid small,
.shipping-box .muted {
  display: block;
  margin-top: 4px;
  color: #94a3b8;
}

.discount {
  color: #dc2626;
}

.address-box,
.note-box,
.shipping-box {
  margin-top: 14px;
}

.address-box p,
.note-box p,
.shipping-box p {
  margin: 0;
  color: #111827;
  line-height: 1.6;
}

.shipping-box .muted {
  margin-top: 8px;
}

.item-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.item-row {
  display: grid;
  grid-template-columns: 64px minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
  padding: 11px;
  border: 1px solid #eef2f7;
  border-radius: 11px;
}

.item-row img {
  width: 64px;
  height: 64px;
  border-radius: 10px;
  object-fit: cover;
  background: #f3f4f6;
}

.item-info h3 {
  margin: 0 0 4px;
  font-size: 14px;
  font-weight: 800;
}

.item-info p {
  margin: 0 0 2px;
  color: #64748b;
  font-size: 12px;
}

.item-meta {
  text-align: right;
}

.item-meta span {
  display: block;
  margin-bottom: 4px;
  color: #64748b;
  font-size: 12px;
}

.item-meta strong {
  color: #0d6efd;
  font-size: 14px;
  font-weight: 850;
}

.summary-card {
  align-self: start;
}

.summary-line + .summary-line {
  margin-top: 8px;
}

.timeline-box {
  margin-top: 10px;
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.timeline-step {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #64748b;
  font-weight: 700;
}

.timeline-step i {
  color: #cbd5e1;
}

.timeline-step.done {
  color: #0f172a;
}

.timeline-step.done i {
  color: #2563eb;
}

.timeline-step.current {
  color: #2563eb;
}

.form-stack {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 10px;
}

.form-stack label {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-stack label span {
  color: #334155;
  font-size: 14px;
  font-weight: 700;
}

.control {
  width: 100%;
  min-height: 40px;
  padding: 10px 12px;
  border: 1px solid #dbe3ef;
  border-radius: 10px;
  background: #ffffff;
  color: #0f172a;
  outline: none;
}

textarea.control {
  min-height: 102px;
  resize: vertical;
}

.control:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.quick-actions {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.quick-btn {
  min-width: 0;
  border-color: #dbe3ef;
  color: #334155;
  background: #ffffff;
}

.quick-btn.danger {
  color: #dc2626;
  background: #fff5f5;
}

.summary-total {
  margin-top: 12px;
}

.summary-total strong {
  font-size: 20px;
}

.empty-state {
  padding: 24px;
  text-align: center;
  color: #64748b;
}

.empty-state i {
  font-size: 32px;
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

  .item-row {
    grid-template-columns: 1fr;
  }

  .item-meta {
    text-align: left;
  }

  .quick-actions {
    grid-template-columns: 1fr;
  }
}
</style>
