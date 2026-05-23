<script setup>
defineProps({
  image: {
    type: String,
    default: '',
  },
  name: {
    type: String,
    default: 'Tên sản phẩm',
  },
  price: {
    type: String,
    default: '',
  },
  oldPrice: {
    type: String,
    default: '',
  },
  storage: {
    type: String,
    default: '',
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

const handleImageError = (event) => {
  event.target.src = fallbackImage
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
          @error="handleImageError"
      />
    </RouterLink>

    <div class="product-info">
      <RouterLink :to="to" class="product-name">
        {{ name }}
      </RouterLink>

      <span v-if="storage" class="product-storage">
        {{ storage }}
      </span>

      <div class="price-row">
        <span class="sale-price">{{ price }}</span>

        <del v-if="oldPrice" class="old-price">
          {{ oldPrice }}
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

.product-storage {
  display: inline-flex;
  align-items: center;
  height: 22px;
  padding: 0 8px;
  margin-bottom: 10px;
  border-radius: 6px;
  background: #f1f5f9;
  color: #475569;
  font-size: 12px;
  font-weight: 700;
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