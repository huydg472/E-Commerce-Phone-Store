<script setup>
defineProps({
  status: {
    type: String,
    default: 'unpaid',
  },
})
</script>

<template>
  <span>{{ status }}</span>
</template>
