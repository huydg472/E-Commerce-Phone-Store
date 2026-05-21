<script setup>
defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  permissions: {
    type: Array,
    default: () => [],
  },
})

defineEmits(['update:modelValue'])
</script>

<template>
  <div>
    <label v-for="permission in permissions" :key="permission.id">
      <input
        type="checkbox"
        :value="permission.id"
        :checked="modelValue.includes(permission.id)"
        @change="$emit('update:modelValue', modelValue.includes(permission.id)
          ? modelValue.filter((id) => id !== permission.id)
          : [...modelValue, permission.id])"
      >
      {{ permission.name }}
    </label>
  </div>
</template>
