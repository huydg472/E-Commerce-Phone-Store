<script setup>
defineProps({
  page: {
    type: Number,
    default: 1,
  },
  totalPage: {
    type: Number,
    default: 1,
  },
})

defineEmits(['change'])
</script>

<template>
  <div class="pagination">
    <button :disabled="page <= 1" @click="$emit('change', page - 1)">Trước</button>
    <span>Trang {{ page }} / {{ totalPage }}</span>
    <button :disabled="page >= totalPage" @click="$emit('change', page + 1)">Sau</button>
  </div>
</template>

<style scoped>
.pagination {
  display: flex;
  gap: 10px;
  align-items: center;
}
</style>
