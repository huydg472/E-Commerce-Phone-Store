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


async function fetchProduct() {
  const response = await productStore.fetchById(route.params.id)
  const product = response.data.data || response.data

  Object.assign(form, {
    name: product.name || '',
    brand_id: product.brand_id || '',
    category_id: product.category_id || '',
    thumbnail: product.thumbnail || '',
    description: product.description || '',
    status: product.status || 'active',
  })
}

async function updateProduct() {
  await productStore.update(route.params.id, form)
  router.push('/admin/products')
}

onMounted(fetchProduct)
</script>

<template>
  <section>
    <div class="admin-page-header">
      <h1>Sửa sản phẩm</h1>
    </div>

    <div class="admin-card">
      <ProductForm
        :form="form"
        :brands="brandStore.items"
        :categories="categoryStore.items"
        button-text="Cập nhật sản phẩm"
        @submit="updateProduct"
      />
    </div>
  </section>
</template>
