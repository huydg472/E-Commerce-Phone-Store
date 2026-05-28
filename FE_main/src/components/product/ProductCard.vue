<script setup>
import {computed} from 'vue'
import {formatCurrency} from '@/utils/formatCurrency'

const props = defineProps({
  image: {
    type: String,
    default: '',
  },
  name: {
    type: String,
    default: 'Tên sản phẩm',
  },
  price: {
    type: [String, Number],
    default: '',
  },
  oldPrice: {
    type: [String, Number],
    default: '',
  },
  colors: {
    type: Array,
    default: () => [],
  },
  discount: {
    type: String,
    default: '',
  },
  to: {
    type: String,
    default: '/products/1',
  },
})

const fallbackImage = 'https://placehold.co/300x220/f1f5f9/2563eb?text=Zin+Mobile'

const normalizeMoney = (value) => {
  if (value === null || value === undefined || value === '') {
    return ''
  }

  const formatted = formatCurrency(value)
  return formatted || String(value)
}

const salePrice = computed(() => normalizeMoney(props.price))
const previousPrice = computed(() => normalizeMoney(props.oldPrice))
const hasPreviousPrice = computed(() => {
  return Boolean(previousPrice.value) && previousPrice.value !== salePrice.value
})
const colorSwatches = computed(() => Array.isArray(props.colors) ? props.colors.slice(0, 5) : [])

const isLightColor = (value) => {
  const normalized = String(value || '').trim().toLowerCase()
  return ['#f8fafc', '#ffffff', '#fff', 'white'].includes(normalized)
}

const handleImageError = (event) => {
  if (event?.target) {
    event.target.src = fallbackImage
  }
}
</script>

<template>
  <div class="product-card">
    <button class="wishlist-btn" type="button">
      <i class="bi bi-heart"></i>
    </button>

    <RouterLink :to="to" class="product-image">
      <img
          :src="image || fallbackImage"
          :alt="name"
          loading="lazy"
          decoding="async"
          @error="handleImageError"
      />
    </RouterLink>

    <div class="product-info">
      <RouterLink :to="to" class="product-name">
        {{ name }}
      </RouterLink>

      <div v-if="colorSwatches.length" class="product-colors">
        <span
            v-for="color in colorSwatches"
            :key="`${color.name}-${color.value}`"
            class="color-swatch"
            :class="{ 'is-light': isLightColor(color.value) }"
            :title="color.name"
            :aria-label="color.name"
            :style="{ backgroundColor: color.value }"
        ></span>
      </div>

      <div class="price-row">
        <span class="sale-price">{{ salePrice }}</span>

        <del v-if="hasPreviousPrice" class="old-price">
          {{ previousPrice }}
        </del>

        <span v-if="discount" class="discount-badge">
          -{{ discount }}
        </span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.product-card {
  position: relative;
  min-height: 285px;
  padding: 14px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  transition: 0.2s ease;
}

.product-card:hover {
  border-color: #0d6efd;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
  transform: translateY(-2px);
}

.wishlist-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  width: 34px;
  height: 34px;
  border: 1px solid #e5e7eb;
  border-radius: 50%;
  background: #ffffff;
  color: #64748b;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}

.wishlist-btn:hover {
  color: #ef4444;
  border-color: #ef4444;
}

.product-image {
  height: 150px;
  padding: 8px 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
}

.product-image img {
  width: 100%;
  max-width: 190px;
  height: 135px;
  object-fit: contain;
}

.product-info {
  padding-top: 6px;
}

.product-name {
  min-height: 42px;
  display: block;
  margin-bottom: 8px;
  color: #111827;
  font-size: 15px;
  font-weight: 700;
  line-height: 1.4;
  text-decoration: none;
}

.product-name:hover {
  color: #0d6efd;
}

.product-colors {
  display: flex;
  align-items: center;
  gap: 6px;
  min-height: 18px;
  margin-bottom: 8px;
}

.color-swatch {
  width: 14px;
  height: 14px;
  border-radius: 50%;
  border: 1px solid rgba(148, 163, 184, 0.35);
  display: inline-block;
  flex: 0 0 auto;
}

.color-swatch.is-light {
  border-color: rgba(148, 163, 184, 0.7);
}

.price-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.sale-price {
  color: #0d6efd;
  font-size: 17px;
  font-weight: 800;
}

.old-price {
  color: #94a3b8;
  font-size: 13px;
  font-weight: 600;
}

.discount-badge {
  height: 22px;
  padding: 0 7px;
  border-radius: 5px;
  background: #ef4444;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 800;
}
</style>
