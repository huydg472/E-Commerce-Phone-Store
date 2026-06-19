<script setup>
defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  detailLoading: {
    type: Boolean,
    default: false,
  },
  detailError: {
    type: String,
    default: '',
  },
  selectedOrder: {
    type: Object,
    default: null,
  },
  selectedOrderItems: {
    type: Array,
    default: () => [],
  },
  selectedOrderPayment: {
    type: Object,
    default: null,
  },
  selectedPaymentMethod: {
    type: String,
    default: '',
  },
  selectedPaymentStatus: {
    type: String,
    default: 'unpaid',
  },
  canRetryVnpayPayment: {
    type: Boolean,
    default: false,
  },
  retryLoading: {
    type: Boolean,
    default: false,
  },
  retryError: {
    type: String,
    default: '',
  },
  statusMap: {
    type: Object,
    required: true,
  },
})

defineEmits(['close', 'retry-vnpay'])
</script>

<template>
  <Teleport to="body">
    <div v-if="open" class="order-detail-overlay" @click.self="$emit('close')">
      <section class="order-detail-popup" role="dialog" aria-modal="true" aria-label="Chi tiết đơn hàng">
        <button type="button" class="popup-close" @click="$emit('close')">
          <i class="bi bi-x-lg"></i>
        </button>

        <div v-if="detailLoading" class="popup-loading">
          <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
          <p>Đang tải chi tiết đơn hàng...</p>
        </div>

        <p v-else-if="detailError" class="popup-error">
          {{ detailError }}
        </p>

        <template v-else-if="selectedOrder">
          <div class="popup-header">
            <div>
              <nav class="popup-breadcrumb">
                <span>Đơn hàng của tôi</span>
                <span>/</span>
                <strong>Chi tiết đơn hàng</strong>
              </nav>
              <h2>Chi tiết đơn hàng</h2>
              <p>Mã đơn hàng: <strong>{{ selectedOrder.order_code || `#${selectedOrder.id}` }}</strong></p>
            </div>

            <div class="popup-header-actions">
              <span class="status-badge" :class="statusMap[selectedOrder.order_status]?.className || 'pending'">
                {{ statusMap[selectedOrder.order_status]?.label || selectedOrder.order_status }}
              </span>
              <button
                  v-if="canRetryVnpayPayment"
                  type="button"
                  class="action-btn primary-btn retry-vnpay-btn"
                  :disabled="retryLoading"
                  @click="$emit('retry-vnpay')"
              >
                <span v-if="retryLoading" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                <i v-else class="bi bi-credit-card"></i>
                Thanh toán VNPay
              </button>
            </div>
          </div>

          <div class="popup-layout">
            <section class="detail-card">
              <h3>Thông tin đơn hàng</h3>
              <div class="popup-info-grid">
                <div>
                  <span>Ngày đặt</span>
                  <strong>{{ selectedOrder.ordered_at || selectedOrder.created_at }}</strong>
                </div>
                <div>
                  <span>Thanh toán</span>
                  <strong class="payment-status" :class="selectedOrder.payment_status === 'paid' ? 'paid' : 'unpaid'">
                    {{ selectedOrder.payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                  </strong>
                </div>
                <div>
                  <span>Người nhận</span>
                  <strong>{{ selectedOrder.receiver_name }}</strong>
                </div>
                <div>
                  <span>Số điện thoại</span>
                  <strong>{{ selectedOrder.receiver_phone }}</strong>
                </div>
              </div>

              <div class="address-box">
                <div class="address-box__head">
                  <span>Địa chỉ giao hàng</span>
                </div>
                <p>{{ selectedOrder.shipping_address_text || 'Chưa có địa chỉ' }}</p>
              </div>

              <div v-if="selectedOrder.note" class="note-box">
                <span>Ghi chú</span>
                <p>{{ selectedOrder.note }}</p>
              </div>

              <div v-if="selectedOrderPayment" class="payment-box">
                <span>Thanh toán</span>
                <p>
                  Phương thức:
                  <strong>{{ selectedPaymentMethod.toUpperCase() || 'N/A' }}</strong>
                </p>
                <p>
                  Trạng thái:
                  <strong :class="selectedPaymentStatus === 'paid' ? 'paid-text' : 'unpaid-text'">
                    {{ selectedPaymentStatus === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                  </strong>
                </p>
              </div>
            </section>

            <section class="detail-card">
              <h3>Sản phẩm</h3>
              <div v-if="selectedOrderItems.length" class="popup-item-list">
                <article v-for="item in selectedOrderItems" :key="item.id" class="popup-item-row">
                  <img :src="item.image || '/images/default-product.png'" :alt="item.product_name"/>
                  <div class="popup-item-info">
                    <h4>{{ item.product_name }}</h4>
                    <p>{{ item.variant_name }}</p>
                  </div>
                  <div class="popup-item-meta">
                    <span>{{ item.quantity }} x {{ item.unit_price }}</span>
                    <strong>{{ item.total_price }}</strong>
                  </div>
                </article>
              </div>
            </section>

            <aside class="detail-card summary-card popup-summary">
              <h3>Tóm tắt</h3>
              <div class="summary-line">
                <span>Tạm tính</span>
                <strong>{{ selectedOrder.subtotal || 0 }}</strong>
              </div>
              <div class="summary-line">
                <span>Phí vận chuyển</span>
                <strong>{{ selectedOrder.shipping_fee || 0 }}</strong>
              </div>
              <div class="summary-line">
                <span>Giảm giá</span>
                <strong class="discount">{{ selectedOrder.discount_amount || 0 }}</strong>
              </div>
              <div class="summary-total">
                <span>Tổng cộng</span>
                <strong>{{ selectedOrder.total_amount || 0 }}</strong>
              </div>

              <p v-if="retryError" class="retry-error">
                {{ retryError }}
              </p>
            </aside>
          </div>
        </template>
      </section>
    </div>
  </Teleport>
</template>

