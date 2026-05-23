<script setup>
import {ref} from 'vue'

const images = [
  '/images/products/iphone-15-pro-max.png',
  '/images/products/iphone-15-pro-max-2.png',
  '/images/products/iphone-15-pro-max-3.png',
  '/images/products/iphone-15-pro-max-4.png',
]

const activeImage = ref(images[0])

const fallbackImage = 'https://placehold.co/520x420/f1f5f9/2563eb?text=Zin+Mobile'

const handleImageError = (event) => {
  event.target.src = fallbackImage
}
</script>

<template>
  <div class="product-gallery">
    <div class="main-image">
      <img
          :src="activeImage"
          alt="iPhone 15 Pro Max"
          @error="handleImageError"
      />
    </div>

    <div class="thumb-wrap">
      <button class="thumb-control" type="button">
        <i class="bi bi-chevron-left"></i>
      </button>

      <button
          v-for="image in images"
          :key="image"
          type="button"
          class="thumb-item"
          :class="{ active: activeImage === image }"
          @click="activeImage = image"
      >
        <img :src="image" alt="Ảnh sản phẩm" @error="handleImageError"/>
      </button>

      <button class="thumb-control" type="button">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
.product-gallery {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #ffffff;
  padding: 24px 22px 18px;
}

.main-image {
  height: 390px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.main-image img {
  max-width: 100%;
  max-height: 370px;
  object-fit: contain;
}

.thumb-wrap {
  margin-top: 18px;
  display: grid;
  grid-template-columns: 38px repeat(4, 1fr) 38px;
  gap: 14px;
  align-items: center;
}

.thumb-control {
  height: 42px;
  border: none;
  background: transparent;
  color: #1f2937;
  font-size: 20px;
}

.thumb-item {
  height: 58px;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #ffffff;
  display: flex;
  align-items: center;
  justify-content: center;
}

.thumb-item.active {
  border-color: #0d6efd;
}

.thumb-item img {
  max-width: 100%;
  max-height: 50px;
  object-fit: contain;
}

@media (max-width: 768px) {
  .main-image {
    height: 300px;
  }

  .main-image img {
    max-height: 280px;
  }

  .thumb-wrap {
    grid-template-columns: repeat(4, 1fr);
  }

  .thumb-control {
    display: none;
  }
}
</style>