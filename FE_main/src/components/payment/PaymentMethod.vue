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
    icon: 'bi-wallet2',
  },
  {
    id: 'bank_transfer',
    title: 'Chuyển khoản ngân hàng',
    description: 'Chuyển khoản qua tài khoản ngân hàng',
    icon: 'bi-bank',
  },
  {
    id: 'vnpay',
    title: 'Thẻ ATM / Visa / MasterCard',
    description: 'Thanh toán qua thẻ ATM, Visa, MasterCard',
    icon: 'bi-credit-card',
  },
  {
    id: 'momo',
    title: 'Ví điện tử',
    description: 'Thanh toán qua ví điện tử',
    icon: 'bi-wallet',
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

        <div class="payment-icon">
          <i :class="`bi ${method.icon}`"></i>
        </div>

        <div class="payment-content">
          <h3>{{ method.title }}</h3>
          <p>{{ method.description }}</p>
        </div>
      </label>
    </div>

    <label class="invoice-check">
      <input class="form-check-input" type="checkbox" />
      <span>
        <strong>Xuất hóa đơn công ty</strong>
        <small>Cung cấp thông tin để xuất hóa đơn VAT</small>
      </span>
    </label>
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
  grid-template-columns: 20px 34px 1fr;
  gap: 14px;
  align-items: flex-start;
  cursor: pointer;
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
  width: 34px;
  height: 34px;
  color: #0f172a;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
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

.invoice-check {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid #e5e7eb;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  cursor: pointer;
}

.invoice-check .form-check-input {
  margin-top: 3px;
  box-shadow: none;
}

.invoice-check span {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.invoice-check strong {
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
}

.invoice-check small {
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
}
</style>
