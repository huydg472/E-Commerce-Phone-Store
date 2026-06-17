<script setup>
const props = defineProps({
  modelValue: {
    type: String,
    default: 'cod',
  },
})

const emit = defineEmits(['update:modelValue'])

const paymentMethods = [
  {
    id: 'cod',
    title: 'Thanh toán khi nhận hàng',
    description: 'Thanh toán bằng tiền mặt khi nhận hàng',
    logo: 'cod',
  },
  {
    id: 'vnpay',
    title: 'VNPay',
    description: 'Thanh toán qua cổng VNPay',
    logo: 'vnpay',
  },
  {
    id: 'momo',
    title: 'MoMo',
    description: 'Thanh toán qua ví MoMo',
    logo: 'momo',
  },
]
</script>

<template>
  <div class="checkout-card">
    <div class="section-title">
      <i class="bi bi-credit-card-2-front"></i>
      <h2>Phương thức thanh toán</h2>
    </div>

    <div class="payment-list">
      <label
          v-for="method in paymentMethods"
          :key="method.id"
          class="payment-item"
      >
        <input
            class="form-check-input"
            type="radio"
            name="payment_method"
            :value="method.id"
            :checked="modelValue === method.id"
            @change="emit('update:modelValue', method.id)"
        />

        <div class="payment-icon" :class="method.logo ? `payment-icon--${method.logo}` : ''">
          <template v-if="method.logo === 'cod'">
            <span class="brand-logo brand-logo--cod">
              <span class="coin-stack">
                <span></span>
                <span></span>
                <span></span>
              </span>
              <span class="brand-logo__label">CASH</span>
            </span>
          </template>

          <template v-else-if="method.logo === 'vnpay'">
            <span class="brand-logo brand-logo--vnpay" aria-label="VNPay">
              <svg viewBox="0 0 120 40" class="vnpay-mark" role="img" aria-hidden="true">
                <defs>
                  <linearGradient id="vnpayBlue" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#0b4fd1"/>
                    <stop offset="100%" stop-color="#1d6bff"/>
                  </linearGradient>
                  <linearGradient id="vnpayRed" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#ef233c"/>
                    <stop offset="100%" stop-color="#ff4d4f"/>
                  </linearGradient>
                </defs>
                <g transform="translate(4 4)">
                  <rect x="0" y="6" width="18" height="18" rx="4" transform="rotate(45 9 15)" fill="url(#vnpayBlue)"/>
                  <path d="M5 15c0-4.4 3.6-8 8-8" fill="none" stroke="#6aa2ff" stroke-width="1.6" stroke-linecap="round"
                        opacity=".85"/>
                  <path d="M7 15c0-3.3 2.7-6 6-6" fill="none" stroke="#6aa2ff" stroke-width="1.4" stroke-linecap="round"
                        opacity=".75"/>
                  <path d="M9 15c0-2.2 1.8-4 4-4" fill="none" stroke="#6aa2ff" stroke-width="1.3" stroke-linecap="round"
                        opacity=".7"/>
                  <rect x="14.5" y="2.5" width="18" height="18" rx="4" transform="rotate(45 23.5 11.5)"
                        fill="url(#vnpayRed)"/>
                </g>
                <text x="47" y="18" class="vnpay-mark__main">VN</text>
                <text x="76" y="18" class="vnpay-mark__accent">PAY</text>
                <text x="47" y="30" class="vnpay-mark__tag">Chọn thanh toán đơn giản</text>
              </svg>
            </span>
          </template>

          <template v-else-if="method.logo === 'momo'">
            <span class="brand-logo brand-logo--momo">
              <span class="brand-logo__main">M</span>
            </span>
          </template>

          <i v-else :class="`bi ${method.icon}`"></i>
        </div>

        <div class="payment-content">
          <h3>{{ method.title }}</h3>
          <p>{{ method.description }}</p>
        </div>
      </label>
    </div>
  </div>
</template>

<style scoped>
.checkout-card {
  padding: 18px 20px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #ffffff;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}

.section-title i {
  color: #0d6efd;
  font-size: 20px;
}

.section-title h2 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
  font-weight: 800;
}

.payment-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.payment-item {
  display: grid;
  grid-template-columns: 20px 56px 1fr;
  gap: 12px;
  align-items: center;
  cursor: pointer;
  padding: 8px 10px 8px 4px;
  border-radius: 14px;
  transition: background-color 0.2s ease, transform 0.2s ease;
}

.payment-item:hover {
  background: #f8fafc;
}

.payment-item .form-check-input {
  margin-top: 6px;
  box-shadow: none;
}

.payment-item .form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.payment-icon {
  width: 56px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.payment-icon--cod,
.payment-icon--vnpay,
.payment-icon--momo {
  width: 56px;
  height: 40px;
  border-radius: 14px;
  overflow: hidden;
}

.payment-icon--cod {
  background: linear-gradient(135deg, #fff7ed 0%, #ffe8cc 100%);
  box-shadow: inset 0 0 0 1px rgba(194, 65, 12, 0.08);
}

.brand-logo {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 900;
  letter-spacing: -0.04em;
  line-height: 1;
}

.brand-logo--cod {
  flex-direction: column;
  gap: 2px;
  color: #c2410c;
  background: linear-gradient(135deg, #fff7ed 0%, #ffe8cc 100%);
}

.coin-stack {
  display: flex;
  align-items: center;
  gap: 2px;
  height: 16px;
}

.coin-stack span {
  display: block;
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.25);
}

.coin-stack span:nth-child(2) {
  transform: translateY(-2px);
  opacity: 0.92;
}

.coin-stack span:nth-child(3) {
  transform: translateY(-4px);
  opacity: 0.84;
}

.brand-logo__label {
  font-size: 9px;
  font-weight: 900;
  letter-spacing: 0.14em;
}

.brand-logo--vnpay {
  padding: 0;
  background: #ffffff;
}

.vnpay-mark {
  width: 100%;
  height: 100%;
}

.vnpay-mark__main {
  fill: #1957e8;
  font-size: 14px;
  font-weight: 900;
  letter-spacing: -0.06em;
}

.vnpay-mark__accent {
  fill: #ef233c;
  font-size: 14px;
  font-weight: 900;
  letter-spacing: -0.06em;
}

.vnpay-mark__tag {
  fill: #6b7280;
  font-size: 4.8px;
  letter-spacing: 0;
}

.brand-logo--momo {
  border-radius: 14px;
  background: linear-gradient(135deg, #d61b7a 0%, #ff4ea3 100%);
  color: #ffffff;
  font-size: 16px;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
}

.brand-logo--momo .brand-logo__main {
  font-size: 17px;
  font-weight: 900;
}

.payment-content h3 {
  margin: 0 0 2px;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
  line-height: 1.35;
}

.payment-content p {
  margin: 0;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  line-height: 1.45;
}
</style>
