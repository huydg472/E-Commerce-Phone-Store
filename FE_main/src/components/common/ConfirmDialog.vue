<script setup>
defineProps({
  message: {
    type: String,
    default: 'Bạn có chắc chắn không?',
  },
})

defineEmits(['confirm', 'cancel'])
</script>

<template>
  <div class="confirm-dialog">
    <p>{{ message }}</p>

    <div class="actions">
      <button type="button" @click="$emit('cancel')">Huỷ</button>
      <button type="button" @click="$emit('confirm')">Đồng ý</button>
    </div>
  </div>
</template>
