<script setup>
import { formatCurrency } from '@/utils/formatCurrency'

defineProps({
  order: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <div class="order-summary">
    <p>Mã đơn: #{{ order.id }}</p>
    <p>Tổng tiền: {{ formatCurrency(order.total_amount || 0) }}</p>
    <p>Trạng thái: {{ order.status }}</p>
  </div>
</template>
