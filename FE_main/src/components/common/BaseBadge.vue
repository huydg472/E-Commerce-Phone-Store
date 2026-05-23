<script setup>
defineProps({
  variant: {
    type: String,
    default: 'success',
  },
})
</script>

<template>
  <span class="badge" :class="`badge--${variant}`">
    <slot/>
  </span>
</template>

<style scoped>
.badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.badge--success {
  background: #dcfce7;
  color: #166534;
}

.badge--warning {
  background: #fef3c7;
  color: #92400e;
}

.badge--danger {
  background: #fee2e2;
  color: #991b1b;
}
</style>
