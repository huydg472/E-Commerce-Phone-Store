<script setup>
import {computed} from 'vue'
import {useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/authStore'
import {useCartStore} from '@/stores/cartStore'
import {formatCurrency} from '@/utils/formatCurrency'
import {toNumberPrice} from '@/utils/productCardHelpers'

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
  productId: {
    type: [String, Number],
    default: null,
  },
  variantId: {
    type: [String, Number],
    default: null,
  },
  cartQuantity: {
    type: Number,
    default: 1,
  },
  stockQuantity: {
    type: Number,
    default: 0,
  },
})

const fallbackImage = 'https://placehold.co/300x220/f1f5f9/2563eb?text=Zin+Mobile'
const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()

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

const canQuickAdd = computed(() => {
  return Boolean(props.variantId) && Number(props.stockQuantity ?? 0) > 0
})

const handleImageError = (event) => {
  if (event?.target) {
    event.target.src = fallbackImage
  }
}

const handleQuickAdd = () => {
  if (!canQuickAdd.value) {
    return
  }

  if (!authStore.isLoggedIn) {
    void router.push('/auth/login')
    return
  }

  void cartStore.create({
    product_variant_id: props.variantId,
    quantity: props.cartQuantity,
    unit_price: toNumberPrice(props.price),
  }).catch((error) => {
    console.error('Không thể thêm vào giỏ hàng:', error)
  })
}
</script>

<template>
  <div class="product-card">
    <div class="product-image-wrap">
      <button class="card-action-btn wishlist-btn" type="button" aria-label="Yêu thích">
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

      <button
          type="button"
          class="card-action-btn quick-cart-btn"
          :disabled="!canQuickAdd"
          aria-label="Thêm vào giỏ hàng"
          @click="handleQuickAdd"
      >
        <i class="bi bi-cart3"></i>
      </button>
    </div>

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

.product-image-wrap {
  position: relative;
}

.card-action-btn {
  position: absolute;
  top: 6px;
  width: 34px;
  height: 34px;
  border: 1px solid #e5e7eb;
  border-radius: 50%;
  background: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-4px) scale(0.94);
  transition: opacity 0.26s cubic-bezier(0.22, 1, 0.36, 1),
  visibility 0.26s cubic-bezier(0.22, 1, 0.36, 1),
  transform 0.26s cubic-bezier(0.22, 1, 0.36, 1),
  border-color 0.26s cubic-bezier(0.22, 1, 0.36, 1),
  background-color 0.26s cubic-bezier(0.22, 1, 0.36, 1),
  color 0.26s cubic-bezier(0.22, 1, 0.36, 1),
  box-shadow 0.26s cubic-bezier(0.22, 1, 0.36, 1);
  pointer-events: none;
  z-index: 3;
}

.product-card:hover .card-action-btn,
.product-card:focus-within .card-action-btn {
  opacity: 1;
  visibility: visible;
  transform: translateY(0) scale(1);
  pointer-events: auto;
}

.wishlist-btn {
  right: 8px;
  color: #64748b;
}

.wishlist-btn:hover {
  color: #ef4444;
  border-color: #ef4444;
}

.quick-cart-btn {
  left: 8px;
  color: #0d6efd;
  box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08);
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

.quick-cart-btn:hover {
  border-color: #0d6efd;
  background: #f8fbff;
  transform: translateY(-1px);
}

.quick-cart-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
  transform: none;
  pointer-events: none;
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
