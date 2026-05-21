<script setup>
import { reactive } from 'vue'
import ProductSpecificationForm from '@/components/product/ProductSpecificationForm.vue'

const form = reactive({
  name: '',
  description: '',
})

function submitForm() {
  console.log('specification form', form)
}
</script>

<template>
  <section>
    <div class="admin-page-header">
      <h1>Quản lý thông số kỹ thuật</h1>
    </div>

    <div class="admin-card">
      <ProductSpecificationForm :form="form" @submit="submitForm" />
    </div>
  </section>
</template>
