<script setup>
defineProps({
  form: {
    type: Object,
    required: true,
  },
})

defineEmits(['submit'])
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="form-group">
      <label>Tên thông số</label>
      <input v-model="form.name" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>Mô tả/Giá trị</label>
      <textarea v-model="form.description" class="form-control" />
    </div>

    <button class="btn btn-primary" type="submit">Lưu thông số</button>
  </form>
</template>
