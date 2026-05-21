<script setup>
defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  rows: {
    type: Number,
    default: 4,
  },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div class="form-group">
    <label v-if="label">{{ label }}</label>

    <textarea
      class="form-control"
      :rows="rows"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
    />
  </div>
</template>
