<script setup>
defineProps({
  filters: {
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
})

defineEmits(['change'])
</script>

<template>
  <div class="product-filter">
    <input
      v-model="filters.keyword"
      class="form-control"
      placeholder="Tìm sản phẩm..."
      @input="$emit('change')"
    >

    <select v-model="filters.brand_id" class="form-control" @change="$emit('change')">
      <option value="">Tất cả thương hiệu</option>
      <option v-for="brand in brands" :key="brand.id" :value="brand.id">
        {{ brand.name }}
      </option>
    </select>

    <select v-model="filters.category_id" class="form-control" @change="$emit('change')">
      <option value="">Tất cả danh mục</option>
      <option v-for="category in categories" :key="category.id" :value="category.id">
        {{ category.name }}
      </option>
    </select>
  </div>
</template>

<style scoped>
.product-filter {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 12px;
}
</style>
