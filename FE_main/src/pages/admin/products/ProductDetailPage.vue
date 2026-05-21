<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from '@/stores/productStore'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()

onMounted(() => {
  productStore.fetchById(route.params.id)
})
</script>

<template>
  <section>
    <div class="admin-page-header">
      <h1>Chi tiết sản phẩm</h1>

      <button class="btn btn-primary" @click="router.push(`/admin/products/${route.params.id}/edit`)">
        Sửa
      </button>
    </div>

    <div v-if="productStore.item" class="admin-card">
      <p>ID: {{ productStore.item.id }}</p>
      <p>Tên: {{ productStore.item.name }}</p>
      <p>Thương hiệu: {{ productStore.item.brand?.name || productStore.item.brand_id }}</p>
      <p>Danh mục: {{ productStore.item.category?.name || productStore.item.category_id }}</p>
      <p>Trạng thái: {{ productStore.item.status }}</p>
      <p>Mô tả: {{ productStore.item.description }}</p>
    </div>
  </section>
</template>
