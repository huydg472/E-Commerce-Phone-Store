<script setup>
defineProps({
  type: {
    type: String,
    default: 'button',
  },
  variant: {
    type: String,
    default: 'primary',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
})
</script>

<template>
  <button
    :type="type"
    class="base-button"
    :class="`base-button--${variant}`"
    :disabled="disabled"
  >
    <slot />
  </button>
</template>

<style scoped>
.base-button {
  padding: 9px 14px;
  border: 0;
  border-radius: 8px;
  font-weight: 600;
}

.base-button--primary {
  background: #2563eb;
  color: #fff;
}

.base-button--secondary {
  background: #e2e8f0;
  color: #0f172a;
}

.base-button--danger {
  background: #dc2626;
  color: #fff;
}
</style>
