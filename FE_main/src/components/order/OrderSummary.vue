<script setup>
import {computed} from 'vue'
import {formatCurrency} from '@/utils/formatCurrency'
import {toNumberPrice} from '@/utils/productCardHelpers'

const props = defineProps({
  items: {
    type: Array,
    default: () => [],
  },
  itemCount: {
    type: Number,
    default: 0,
  },
  subtotal: {
    type: [Number, String],
    default: 0,
  },
  discount: {
    type: [Number, String],
    default: 0,
  },
  shipping: {
    type: [Number, String],
    default: 0,
  },
  total: {
    type: [Number, String],
    default: 0,
  },
  buttonLabel: {
    type: String,
    default: 'Tiến hành thanh toán',
  },
  loading: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['submit-order'])

const subtotalValue = computed(() => toNumberPrice(props.subtotal))
const discountValue = computed(() => toNumberPrice(props.discount))
const shippingValue = computed(() => toNumberPrice(props.shipping))
const totalValue = computed(() => toNumberPrice(props.total))

const shippingText = computed(() => {
  if (shippingValue.value <= 0) {
    return 'Miễn phí'
  }

  return formatCurrency(shippingValue.value)
})
</script>

<template>
  <aside class="summary-card">
    <h2>Tóm tắt đơn hàng</h2>

    <div v-if="items.length" class="summary-products">
      <div
          v-for="item in items"
          :key="item.id"
          class="summary-product"
      >
        <div class="product-left">
          <div class="product-thumb">
            <img
                :src="item.image"
                :alt="item.name"
            />
          </div>

          <div class="product-info">
            <h3>{{ item.name }}</h3>
            <p v-if="item.color">Màu: {{ item.color }}</p>
            <p v-if="item.version">Phiên bản: {{ item.version }}</p>
            <span>Số lượng: {{ item.quantity }}</span>
          </div>
        </div>

        <strong class="product-price">{{ item.price }}</strong>
      </div>
    </div>

    <div v-else class="empty-products">
      <i class="bi bi-bag-x"></i>
      <p>Chưa có sản phẩm nào trong đơn hàng.</p>
    </div>

    <div class="summary-pricing">
      <div class="price-row">
        <span>Tạm tính</span>
        <strong>{{ formatCurrency(subtotalValue) }}</strong>
      </div>

      <div class="price-row">
        <span>Giảm giá</span>
        <strong class="discount">{{
            discountValue > 0 ? `-${formatCurrency(discountValue)}` : formatCurrency(0)
          }}</strong>
      </div>

      <div class="price-row">
        <span>
          Phí vận chuyển
          <i class="bi bi-info-circle"></i>
        </span>
        <strong class="free">{{ shippingText }}</strong>
      </div>
    </div>

    <div class="summary-total">
      <div>
        <span>Tổng cộng</span>
      </div>

      <div class="total-right">
        <strong>{{ formatCurrency(totalValue) }}</strong>
        <p>(Đã bao gồm VAT)</p>
      </div>
    </div>

    <button
        type="button"
        class="order-btn"
        :disabled="disabled || loading || !items.length"
        @click="emit('submit-order')"
    >
      <span v-if="loading" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
      <i v-else class="bi bi-lock"></i>
      {{ loading ? 'Đang xử lý...' : buttonLabel }}
    </button>

    <RouterLink to="/cart" class="back-cart-btn">
      <i class="bi bi-arrow-left"></i>
      <i class="bi bi-cart3"></i>
      Quay lại giỏ hàng
    </RouterLink>

    <div class="agree-note">
      <i class="bi bi-shield-check"></i>
      <span>
        Bằng việc đặt hàng, bạn đồng ý với
        <a href="javascript:void(0)">điều khoản mua hàng</a>
      </span>
    </div>
  </aside>
</template>

<style scoped>
.summary-card {
  padding: 18px 18px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #ffffff;
}

.summary-card h2 {
  margin: 0 0 16px;
  color: #0f172a;
  font-size: 20px;
  font-weight: 800;
}

.summary-products {
  border-bottom: 1px solid #e5e7eb;
}

.summary-product {
  padding: 12px 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.summary-product + .summary-product {
  border-top: 1px solid #eef2f7;
}

.product-left {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.product-thumb {
  width: 76px;
  height: 76px;
  border-radius: 10px;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  overflow: hidden;
}

.product-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.product-info {
  min-width: 0;
}

.product-info h3 {
  margin: 0 0 6px;
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
  line-height: 1.4;
}

.product-info p,
.product-info span {
  display: block;
  margin: 0;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  line-height: 1.5;
}

.product-price {
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
  white-space: nowrap;
}

.empty-products {
  padding: 18px 0;
  border-bottom: 1px solid #e5e7eb;
  text-align: center;
  color: #64748b;
}

.empty-products i {
  display: block;
  margin-bottom: 8px;
  color: #0d6efd;
  font-size: 32px;
}

.summary-pricing {
  padding: 16px 0;
  border-bottom: 1px solid #e5e7eb;
}

.price-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.price-row + .price-row {
  margin-top: 12px;
}

.price-row span {
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
}

.price-row strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
}

.discount {
  color: #ef4444;
}

.free {
  color: #16a34a;
}

.summary-total {
  padding: 16px 0 14px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.summary-total span {
  color: #0f172a;
  font-size: 16px;
  font-weight: 800;
}

.total-right {
  text-align: right;
}

.total-right strong {
  color: #0d6efd;
  font-size: 18px;
  font-weight: 900;
}

.total-right p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
}

.order-btn,
.back-cart-btn {
  width: 100%;
  height: 44px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 800;
  text-decoration: none;
}

.order-btn {
  margin-top: 14px;
  border: none;
  background: #0d6efd;
  color: #ffffff;
}

.order-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.back-cart-btn {
  margin-top: 12px;
  border: 1px solid #0d6efd;
  background: #ffffff;
  color: #0d6efd;
}

.agree-note {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid #eef2f7;
  display: flex;
  align-items: flex-start;
  gap: 8px;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  line-height: 1.5;
}

.agree-note i {
  color: #64748b;
  font-size: 15px;
  margin-top: 1px;
}

.agree-note a {
  color: #0d6efd;
  text-decoration: none;
  font-weight: 700;
}
</style>
