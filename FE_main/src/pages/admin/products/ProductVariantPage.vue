<script setup>
import { reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import ProductVariantForm from '@/components/product/ProductVariantForm.vue'
import { productVariantService } from '@/services/productVariantService'

const route = useRoute()

const form = reactive({
  product_id: route.params.id,
  color: '',
  ram: '',
  storage: '',
  price: '',
  sku: '',
  quantity: 0,
  status: 'active',
})

async function createVariant() {
  await productVariantService.create(form)
  alert('Đã lưu biến thể')
}

onMounted(() => {
  form.product_id = route.params.id
})
</script>

<template>
  <section>
    <div class="admin-page-header">
      <h1>Quản lý biến thể sản phẩm</h1>
    </div>

    <div class="admin-card">
      <ProductVariantForm :form="form" @submit="createVariant" />
    </div>
  </section>
</template>
