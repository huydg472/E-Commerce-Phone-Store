<script setup>
import { APP_CONFIG } from '@/constants/appConfig'
import { formatCurrency } from '@/utils/formatCurrency'

defineProps({
  product: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <article class="product-card">
    <img :src="product.thumbnail || APP_CONFIG.defaultProductImage" :alt="product.name">

    <h3>{{ product.name }}</h3>

    <p>{{ formatCurrency(product.price || product.min_price || 0) }}</p>

    <router-link :to="`/products/${product.id}`">
      Xem chi tiết
    </router-link>
  </article>
</template>

<style scoped>
.product-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 14px;
}

.product-card img {
  width: 100%;
  aspect-ratio: 1;
  object-fit: contain;
}
</style>
