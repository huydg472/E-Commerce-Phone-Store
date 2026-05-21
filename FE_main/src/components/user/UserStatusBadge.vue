<script setup>
defineProps({
  status: {
    type: String,
    default: 'active',
  },
})
</script>

<template>
  <span>{{ status }}</span>
</template>
