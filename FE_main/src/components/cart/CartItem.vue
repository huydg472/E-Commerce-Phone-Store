<script setup>
import { formatCurrency } from '@/utils/formatCurrency'

defineProps({
  item: {
    type: Object,
    required: true,
  },
})

defineEmits(['update-quantity', 'remove'])
</script>

<template>
  <div class="cart-item">
    <div>
      <strong>{{ item.product?.name || item.name }}</strong>
      <p>{{ formatCurrency(item.price || 0) }}</p>
    </div>

    <input
      :value="item.quantity"
      type="number"
      min="1"
      @input="$emit('update-quantity', item, Number($event.target.value))"
    >

    <button @click="$emit('remove', item.id)">Xoá</button>
  </div>
</template>
