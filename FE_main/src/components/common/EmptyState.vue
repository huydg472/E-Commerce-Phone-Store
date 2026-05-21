<script setup>
defineProps({
  message: {
    type: String,
    default: 'Không có dữ liệu',
  },
})
</script>

<template>
  <div class="empty-state">
    {{ message }}
  </div>
</template>
