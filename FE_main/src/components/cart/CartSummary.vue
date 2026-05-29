<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/utils/formatCurrency'
import { toNumberPrice } from '@/utils/productCardHelpers'

const props = defineProps({
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
})

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
  <div class="cart-summary">
    <div class="summary-card">
      <h2>Tóm tắt đơn hàng</h2>

      <div class="summary-row">
        <span>Tạm tính ({{ itemCount }} sản phẩm)</span>
        <strong>{{ formatCurrency(subtotalValue) }}</strong>
      </div>

      <div class="summary-row">
        <span>Giảm giá</span>
        <strong class="discount-text">
          {{ discountValue > 0 ? `-${formatCurrency(discountValue)}` : formatCurrency(0) }}
        </strong>
      </div>

      <div class="summary-row">
        <span>
          Phí vận chuyển
          <i class="bi bi-info-circle"></i>
        </span>

        <strong class="free-text">{{ shippingText }}</strong>
      </div>

      <div class="summary-total">
        <span>Tổng cộng</span>

        <div>
          <strong>{{ formatCurrency(totalValue) }}</strong>
          <p>(Đã bao gồm VAT)</p>
        </div>
      </div>

      <div class="coupon-form">
        <input
            type="text"
            class="form-control"
            placeholder="Nhập mã giảm giá"
        />

        <button class="btn btn-primary" type="button">
          Áp dụng
        </button>
      </div>

      <button class="checkout-btn" type="button">
        <i class="bi bi-lock"></i>
        Tiến hành thanh toán
      </button>

      <RouterLink to="/products" class="buy-more-btn">
        <i class="bi bi-card-list"></i>
        Mua thêm sản phẩm
      </RouterLink>
    </div>

    <div class="service-card">
      <div class="service-item">
        <i class="bi bi-shield-lock"></i>

        <div>
          <h4>Thanh toán an toàn, bảo mật</h4>
          <p>Cam kết bảo mật thông tin khách hàng</p>
        </div>
      </div>

      <div class="service-item">
        <i class="bi bi-truck"></i>

        <div>
          <h4>Miễn phí vận chuyển</h4>
          <p>Cho đơn hàng từ 500.000đ</p>
        </div>
      </div>

      <div class="service-item">
        <i class="bi bi-arrow-repeat"></i>

        <div>
          <h4>Đổi trả dễ dàng</h4>
          <p>Đổi trả trong 30 ngày nếu có lỗi</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.cart-summary {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.summary-card,
.service-card {
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #ffffff;
}

.summary-card {
  padding: 22px 24px;
}

.summary-card h2 {
  margin: 0 0 18px;
  color: #111827;
  font-size: 21px;
  font-weight: 800;
}

.summary-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 15px;
  color: #111827;
  font-size: 14px;
  font-weight: 700;
}

.summary-row strong {
  font-size: 15px;
  font-weight: 800;
  white-space: nowrap;
}

.discount-text {
  color: #dc2626;
}

.free-text {
  color: #16a34a;
}

.summary-total {
  margin-top: 2px;
  padding-top: 17px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: space-between;
  gap: 16px;
}

.summary-total span {
  color: #111827;
  font-size: 18px;
  font-weight: 800;
}

.summary-total div {
  text-align: right;
}

.summary-total strong {
  color: #0d6efd;
  font-size: 25px;
  font-weight: 900;
  white-space: nowrap;
}

.summary-total p {
  margin: 3px 0 0;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
}

.coupon-form {
  margin-top: 14px;
  display: grid;
  grid-template-columns: 1fr 112px;
  gap: 10px;
}

.coupon-form .form-control,
.coupon-form .btn {
  height: 44px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 700;
}

.coupon-form .form-control {
  border-color: #dbe3ef;
  color: #334155;
  box-shadow: none;
  padding-left: 14px;
}

.coupon-form .btn {
  background: #0d6efd;
}

.checkout-btn,
.buy-more-btn {
  width: 100%;
  height: 48px;
  margin-top: 14px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  text-decoration: none;
  font-size: 15px;
  font-weight: 800;
}

.checkout-btn {
  border: none;
  background: #0057ff;
  color: #ffffff;
}

.buy-more-btn {
  border: 1px solid #0d6efd;
  background: #ffffff;
  color: #0d6efd;
}

.service-card {
  padding: 18px 22px;
}

.service-item {
  display: grid;
  grid-template-columns: 38px 1fr;
  gap: 12px;
  align-items: center;
}

.service-item + .service-item {
  margin-top: 14px;
}

.service-item i {
  color: #0d6efd;
  font-size: 30px;
}

.service-item h4 {
  margin: 0 0 3px;
  color: #111827;
  font-size: 15px;
  font-weight: 800;
}

.service-item p {
  margin: 0;
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
}

@media (max-width: 576px) {
  .summary-card {
    padding: 20px 16px;
  }

  .coupon-form {
    grid-template-columns: 1fr;
  }

  .summary-total {
    flex-direction: column;
  }

  .summary-total div {
    text-align: left;
  }
}
</style>
