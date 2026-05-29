<script setup>
import {computed, ref, watch} from 'vue'

const props = defineProps({
  images: {
    type: Array,
    default: () => [],
  },
  title: {
    type: String,
    default: 'Sản phẩm',
  },
})

const fallbackImage = 'https://placehold.co/520x420/f1f5f9/2563eb?text=Zin+Mobile'

const normalizedImages = computed(() => {
  const list = Array.isArray(props.images) ? props.images : []
  const unique = [...new Set(list.filter(Boolean))]
  return unique.length ? unique : [fallbackImage]
})

const activeImage = ref(normalizedImages.value[0])

watch(
    normalizedImages,
    (nextImages) => {
      activeImage.value = nextImages[0] || fallbackImage
    },
    {immediate: true}
)

const handleImageError = (event) => {
  event.target.src = fallbackImage
}
</script>

<template>
  <div class="product-gallery">
    <div class="main-image">
      <img
          :src="activeImage"
          :alt="title"
          @error="handleImageError"
      />
    </div>

    <div class="thumb-wrap">
      <button class="thumb-control" type="button">
        <i class="bi bi-chevron-left"></i>
      </button>

      <button
          v-for="image in normalizedImages"
          :key="image"
          type="button"
          class="thumb-item"
          :class="{ active: activeImage === image }"
          @click="activeImage = image"
      >
        <img :src="image" :alt="title" @error="handleImageError"/>
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
  padding: 18px 16px 14px;
}

.main-image {
  height: 330px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.main-image img {
  max-width: 100%;
  max-height: 310px;
  object-fit: contain;
}

.thumb-wrap {
  margin-top: 14px;
  display: grid;
  grid-template-columns: 30px repeat(4, 1fr) 30px;
  gap: 10px;
  align-items: center;
}

.thumb-control {
  height: 36px;
  border: none;
  background: transparent;
  color: #1f2937;
  font-size: 18px;
}

.thumb-item {
  height: 50px;
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
  max-height: 42px;
  object-fit: contain;
}

@media (max-width: 768px) {
  .main-image {
    height: 280px;
  }

  .main-image img {
    max-height: 250px;
  }

  .thumb-wrap {
    grid-template-columns: repeat(4, 1fr);
  }

  .thumb-control {
    display: none;
  }
}
</style>
