<script setup>
defineProps({
  variants: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: [String, Number],
    default: '',
  },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="variant-selector">
    <button
      v-for="variant in variants"
      :key="variant.id"
      type="button"
      :class="{ active: modelValue === variant.id }"
      @click="$emit('update:modelValue', variant.id)"
    >
      {{ variant.color }} - {{ variant.ram }} - {{ variant.storage }}
    </button>
  </div>
</template>
