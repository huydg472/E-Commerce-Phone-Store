<script setup>
import ProductImageUpload from '@/components/product/ProductImageUpload.vue'

function handleChange(file) {
  console.log('selected image', file)
}
</script>

<template>
  <section>
    <div class="admin-page-header">
      <h1>Quản lý ảnh sản phẩm</h1>
    </div>

    <div class="admin-card">
      <ProductImageUpload @change="handleChange" />
    </div>
  </section>
</template>
