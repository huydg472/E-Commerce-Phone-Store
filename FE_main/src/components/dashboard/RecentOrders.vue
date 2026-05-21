<script setup>
defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="admin-card">
    <h3>Đơn hàng gần đây</h3>
    <p v-if="!orders.length">Chưa có dữ liệu.</p>
  </div>
</template>
