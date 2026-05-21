<script setup>
defineProps({
  form: {
    type: Object,
    required: true,
  },
  buttonText: {
    type: String,
    default: 'Lưu biến thể',
  },
})

defineEmits(['submit'])
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="form-group">
      <label>Màu sắc</label>
      <input v-model="form.color" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>RAM</label>
      <input v-model="form.ram" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>Dung lượng</label>
      <input v-model="form.storage" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>Giá</label>
      <input v-model="form.price" class="form-control" type="number">
    </div>

    <div class="form-group">
      <label>SKU</label>
      <input v-model="form.sku" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>Số lượng</label>
      <input v-model="form.quantity" class="form-control" type="number">
    </div>

    <button class="btn btn-primary" type="submit">{{ buttonText }}</button>
  </form>
</template>
