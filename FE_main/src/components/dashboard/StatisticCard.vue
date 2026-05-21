<script setup>
defineProps({
  title: {
    type: String,
    default: 'Thống kê',
  },
  value: {
    type: [String, Number],
    default: 0,
  },
})
</script>

<template>
  <div class="admin-card">
    <p>{{ title }}</p>
    <h2>{{ value }}</h2>
  </div>
</template>
