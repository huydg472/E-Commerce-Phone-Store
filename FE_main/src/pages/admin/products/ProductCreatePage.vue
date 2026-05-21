<script setup>

import { reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import ProductForm from '@/components/product/ProductForm.vue'
import { useProductStore } from '@/stores/productStore'
import { useBrandStore } from '@/stores/brandStore'
import { useCategoryStore } from '@/stores/categoryStore'

const router = useRouter()
const route = useRoute()
const productStore = useProductStore()
const brandStore = useBrandStore()
const categoryStore = useCategoryStore()

const form = reactive({
  name: '',
  brand_id: '',
  category_id: '',
  thumbnail: '',
  description: '',
  status: 'active',
})

onMounted(async () => {
  await Promise.all([
    brandStore.fetchAll(),
    categoryStore.fetchAll(),
  ])
})


async function createProduct() {
  await productStore.create(form)
  router.push('/admin/products')
}
</script>

<template>
  <section>
    <div class="admin-page-header">
      <h1>Thêm sản phẩm</h1>
    </div>

    <div class="admin-card">
      <ProductForm
        :form="form"
        :brands="brandStore.items"
        :categories="categoryStore.items"
        button-text="Thêm sản phẩm"
        @submit="createProduct"
      />
    </div>
  </section>
</template>
