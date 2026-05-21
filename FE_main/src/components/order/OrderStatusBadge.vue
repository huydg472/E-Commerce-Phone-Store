<script setup>
defineProps({
  status: {
    type: String,
    default: 'pending',
  },
})
</script>

<template>
  <span class="badge">
    {{ status }}
  </span>
</template>
