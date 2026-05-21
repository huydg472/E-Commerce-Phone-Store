<script setup>
import { formatCurrency } from '@/utils/formatCurrency'

defineProps({
  total: {
    type: Number,
    default: 0,
  },
})
</script>

<template>
  <aside class="cart-summary">
    <h3>Tổng giỏ hàng</h3>
    <p>{{ formatCurrency(total) }}</p>
    <router-link to="/checkout">Thanh toán</router-link>
  </aside>
</template>
