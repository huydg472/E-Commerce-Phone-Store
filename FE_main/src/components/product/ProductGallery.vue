<script setup>
import { APP_CONFIG } from '@/constants/appConfig'

defineProps({
  images: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="product-gallery">
    <img
      v-if="!images.length"
      :src="APP_CONFIG.defaultProductImage"
      alt="Default product"
    >

    <img
      v-for="image in images"
      :key="image.id || image.url"
      :src="image.url"
      :alt="image.name || 'Product image'"
    >
  </div>
</template>

<style scoped>
.product-gallery {
  display: grid;
  gap: 12px;
}
</style>
