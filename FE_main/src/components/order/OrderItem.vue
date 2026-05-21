<script setup>
import { formatCurrency } from '@/utils/formatCurrency'

defineProps({
  item: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <div class="order-item">
    {{ item.product_name || item.product?.name }} -
    {{ item.quantity }} x {{ formatCurrency(item.price || 0) }}
  </div>
</template>
