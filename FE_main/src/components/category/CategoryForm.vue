<script setup>
defineProps({
  form: {
    type: Object,
    required: true,
  },
  buttonText: {
    type: String,
    default: 'Lưu danh mục',
  },
})

defineEmits(['submit'])
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="form-group">
      <label>Tên danh mục</label>
      <input v-model="form.name" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>Mô tả</label>
      <textarea v-model="form.description" class="form-control" />
    </div>

    <div class="form-group">
      <label>Trạng thái</label>
      <select v-model="form.status" class="form-control">
        <option value="active">Hoạt động</option>
        <option value="inactive">Ẩn</option>
      </select>
    </div>

    <button class="btn btn-primary" type="submit">{{ buttonText }}</button>
  </form>
</template>
