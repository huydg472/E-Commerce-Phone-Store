<script setup>
defineProps({
  products: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="admin-card">
    <h3>Sản phẩm bán chạy</h3>
    <p v-if="!products.length">Chưa có dữ liệu.</p>
  </div>
</template>
