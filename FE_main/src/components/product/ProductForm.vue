<script setup>
defineProps({
  form: {
    type: Object,
    required: true,
  },
  brands: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
  buttonText: {
    type: String,
    default: 'Lưu',
  },
})

defineEmits(['submit'])
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="form-group">
      <label>Tên sản phẩm</label>
      <input v-model="form.name" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>Thương hiệu</label>
      <select v-model="form.brand_id" class="form-control">
        <option value="">-- Chọn thương hiệu --</option>
        <option v-for="brand in brands" :key="brand.id" :value="brand.id">
          {{ brand.name }}
        </option>
      </select>
    </div>

    <div class="form-group">
      <label>Danh mục</label>
      <select v-model="form.category_id" class="form-control">
        <option value="">-- Chọn danh mục --</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">
          {{ category.name }}
        </option>
      </select>
    </div>

    <div class="form-group">
      <label>Ảnh đại diện</label>
      <input v-model="form.thumbnail" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>Mô tả</label>
      <textarea v-model="form.description" class="form-control" rows="4" />
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
