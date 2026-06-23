<script setup>
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue'
import ProductCard from '@/components/product/ProductCard.vue'

const props = defineProps({
  products: {
    type: Array,
    required: true,
  },
  fallbackImages: {
    type: Array,
    default: () => [],
  },
})

const featuredScrollRef = ref(null)
const autoScrollTimer = ref(null)
const autoScrollPaused = ref(false)

const getShowcaseImage = (productImage, index = 0) => {
  const resolvedImage = String(productImage ?? '').trim()

  if (resolvedImage) {
    return resolvedImage
  }

  return props.fallbackImages[index % props.fallbackImages.length] || '/images/default-product.png'
}

const featuredProductsLoop = computed(() => {
  return props.products.length > 1 ? [...props.products, ...props.products] : props.products
})

const scrollFeaturedProducts = (direction) => {
  const element = featuredScrollRef.value

  if (!element) {
    return
  }

  const cardWidth = element.querySelector('.product-card-shell')?.offsetWidth || 260
  element.scrollBy({
    left: direction * (cardWidth + 16) * 2,
    behavior: 'smooth',
  })
}

const syncFeaturedLoop = () => {
  const element = featuredScrollRef.value

  if (!element) {
    return
  }

  const halfWidth = element.scrollWidth / 2

  if (halfWidth > 0 && element.scrollLeft >= halfWidth) {
    element.scrollLeft -= halfWidth
  }
}

const stopAutoScroll = () => {
  if (autoScrollTimer.value) {
    window.clearInterval(autoScrollTimer.value)
    autoScrollTimer.value = null
  }
}

const startAutoScroll = () => {
  stopAutoScroll()

  autoScrollTimer.value = window.setInterval(() => {
    if (autoScrollPaused.value || props.products.length <= 1) {
      return
    }

    const element = featuredScrollRef.value

    if (!element) {
      return
    }

    const cardWidth = element.querySelector('.product-card-shell')?.offsetWidth || 260
    const nextScrollLeft = element.scrollLeft + cardWidth + 16
    const halfWidth = element.scrollWidth / 2

    if (halfWidth > 0 && nextScrollLeft >= halfWidth) {
      element.scrollTo({
        left: 0,
        behavior: 'auto',
      })
      return
    }

    element.scrollBy({
      left: cardWidth + 16,
      behavior: 'smooth',
    })
  }, 2600)
}

watch(
    () => props.products.length,
    () => {
      startAutoScroll()
    },
)

onMounted(startAutoScroll)

onBeforeUnmount(stopAutoScroll)
</script>

<template>
  <section class="container-fluid px-4 featured-section">
    <div class="section-heading">
      <h2>Sản phẩm nổi bật</h2>

      <RouterLink :to="{ name: 'products.index', query: { featured: '1', featuredScope: 'all' } }">
        Xem tất cả
        <i class="bi bi-chevron-right"></i>
      </RouterLink>
    </div>

    <div class="product-slider-wrap">
      <button class="product-nav product-nav-left" type="button" @click="scrollFeaturedProducts(-1)">
        <i class="bi bi-chevron-left"></i>
      </button>

      <div
          ref="featuredScrollRef"
          class="product-grid featured-product-track"
          @mouseenter="autoScrollPaused = true"
          @mouseleave="autoScrollPaused = false"
          @scroll="syncFeaturedLoop"
      >
        <div v-for="(product, index) in featuredProductsLoop" :key="`${product.id}-${index}`"
             class="product-card-shell">
          <ProductCard
              :image="getShowcaseImage(product.image, index)"
              :name="product.name"
              :colors="product.colors"
              :price="product.price"
              :old-price="product.oldPrice || ''"
              :to="product.to"
              :product-id="product.productId"
              :variant-id="product.variantId"
              :cart-quantity="1"
              :stock-quantity="product.stockQuantity"
          />
        </div>
      </div>

      <button class="product-nav product-nav-right" type="button" @click="scrollFeaturedProducts(1)">
        <i class="bi bi-chevron-right"></i>
      </button>
    </div>
  </section>
</template>

<style scoped>
.featured-section {
  margin-top: 22px;
}

.section-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 14px;
}

.section-heading h2 {
  margin: 0;
  font-size: 30px;
  line-height: 1.25;
  font-weight: 800;
  color: var(--text-color);
}

.section-heading a {
  color: var(--primary-color);
  font-weight: 700;
  font-size: 16px;
  white-space: nowrap;
  text-decoration: none;
}

.section-heading a:hover {
  color: var(--primary-hover);
}

.product-slider-wrap {
  position: relative;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
}

.featured-product-track {
  display: flex;
  overflow-x: auto;
  scroll-behavior: smooth;
  scroll-snap-type: x mandatory;
  scrollbar-width: none;
  padding: 2px 2px 8px;
}

.featured-product-track::-webkit-scrollbar {
  display: none;
}

.product-card-shell {
  flex: 0 0 calc((100% - 64px) / 5);
  min-width: 220px;
  scroll-snap-align: start;
}

.product-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 4;
  border: none;
  background: var(--card-bg);
  color: var(--text-color);
  width: 48px;
  height: 48px;
  border-radius: 50%;
  box-shadow: 0 6px 20px rgba(15, 23, 42, 0.1);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.product-nav:hover {
  color: var(--primary-color);
}

.product-nav-left {
  left: -24px;
}

.product-nav-right {
  right: -24px;
}

@media (max-width: 1200px) {
  .product-grid {
    grid-template-columns: repeat(3, 1fr);
  }

  .product-card-shell {
    flex-basis: calc((100% - 32px) / 3);
  }
}

@media (max-width: 768px) {
  .section-heading h2 {
    font-size: 24px;
  }

  .product-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .product-nav {
    width: 42px;
    height: 42px;
  }
}

@media (max-width: 480px) {
  .product-grid {
    grid-template-columns: 1fr;
  }
}
</style>
