<script setup>
import ProductCard from '@/components/product/ProductCard.vue'

const demoProducts = [
  { id: 1, name: 'iPhone 15 Pro Max', price: 29990000 },
  { id: 2, name: 'Samsung Galaxy S24 Ultra', price: 25990000 },
  { id: 3, name: 'Xiaomi 14', price: 18990000 },
  { id: 4, name: 'OPPO Reno', price: 9990000 },
]
</script>

<template>
  <section class="page-section">
    <div class="container">
      <h1 class="page-title">Website bán điện thoại</h1>
      <p>Trang chủ client.</p>

      <div class="grid grid-4">
        <ProductCard
          v-for="product in demoProducts"
          :key="product.id"
          :product="product"
        />
      </div>
    </div>
  </section>
</template>
