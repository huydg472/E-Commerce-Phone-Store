<script setup>
defineProps({
  modelValue: {
    type: String,
    default: 'cod',
  },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="payment-method">
    <label>
      <input
        type="radio"
        value="cod"
        :checked="modelValue === 'cod'"
        @change="$emit('update:modelValue', 'cod')"
      >
      Thanh toán khi nhận hàng
    </label>

    <label>
      <input
        type="radio"
        value="bank_transfer"
        :checked="modelValue === 'bank_transfer'"
        @change="$emit('update:modelValue', 'bank_transfer')"
      >
      Chuyển khoản
    </label>
  </div>
</template>
