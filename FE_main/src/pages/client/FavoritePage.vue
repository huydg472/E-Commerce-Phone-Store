<script setup>
import BasePagination from '@/components/common/BasePagination.vue'
import ProductCard from '@/components/product/ProductCard.vue'
import {computed, onMounted, ref, watch} from 'vue'
import {storeToRefs} from 'pinia'
import {useFavoriteStore} from '@/stores/favoriteStore'
import {buildProductCards} from '@/utils/productCardHelpers'

const favoriteStore = useFavoriteStore()
const {items, loading} = storeToRefs(favoriteStore)

const currentPage = ref(1)
const pageSize = 9

const favoriteProducts = computed(() => {
  return (Array.isArray(items.value) ? items.value : [])
      .map((favorite) => {
        const product = favorite?.product ?? favorite?.productVariant?.product ?? favorite?.product_variant?.product ?? null
        const productVariant = favorite?.productVariant ?? favorite?.product_variant ?? null

        if (!product || !productVariant) {
          return null
        }

        return {
          ...product,
          productVariants: [productVariant],
          product_variants: [productVariant],
        }
      })
      .filter(Boolean)
})

const productCards = computed(() => buildProductCards(favoriteProducts.value))

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(productCards.value.length / pageSize))
})

const visibleProducts = computed(() => {
  const startIndex = (currentPage.value - 1) * pageSize
  return productCards.value.slice(startIndex, startIndex + pageSize)
})

watch(productCards, () => {
  currentPage.value = 1
})

watch(totalPages, (nextTotalPages) => {
  if (currentPage.value > nextTotalPages) {
    currentPage.value = nextTotalPages
  }
})

onMounted(() => {
  void favoriteStore.fetchAll()
})
</script>

<template>
  <section class="favorite-page">
    <div class="favorite-header">
      <div>
        <span class="section-eyebrow">Tài khoản của tôi</span>
        <h1>Sản phẩm yêu thích</h1>
      </div>

      <span class="favorite-count">{{ productCards.length }} sản phẩm</span>
    </div>

    <div v-if="loading" class="favorite-state">
      Đang tải danh sách yêu thích...
    </div>

    <div v-else-if="!productCards.length" class="favorite-empty">
      <div class="empty-icon">
        <i class="bi bi-heart"></i>
      </div>

      <h2>Chưa có sản phẩm yêu thích</h2>
      <p>Lưu những sản phẩm bạn quan tâm để quay lại xem và mua nhanh hơn.</p>

      <RouterLink to="/products" class="browse-link">
        Xem sản phẩm
      </RouterLink>
    </div>

    <template v-else>
      <div class="favorite-grid">
        <ProductCard
            v-for="product in visibleProducts"
            :key="`${product.productId}-${product.variantId || product.rom || product.name}`"
            :name="product.name"
            :image="product.image"
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

      <div v-if="totalPages > 1" class="favorite-pagination">
        <BasePagination
            v-model:currentPage="currentPage"
            :total-pages="totalPages"
        />
      </div>
    </template>
  </section>
</template>

<style scoped>
.favorite-page {
  padding: 22px;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.04);
}

.favorite-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.section-eyebrow {
  display: block;
  margin-bottom: 4px;
  color: #64748b;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.04em;
  text-transform: uppercase;
}

.favorite-header h1 {
  margin: 0;
  color: #0f172a;
  font-size: 24px;
  font-weight: 900;
}

.favorite-count {
  min-height: 32px;
  padding: 0 12px;
  border-radius: 999px;
  background: #fff1f2;
  color: #e11d48;
  display: inline-flex;
  align-items: center;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}

.favorite-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(240px, 1fr));
  gap: 22px;
  align-items: stretch;
}

.favorite-grid :deep(.product-card) {
  min-height: 310px;
  padding: 18px;
}

.favorite-grid :deep(.product-image) {
  height: 170px;
}

.favorite-grid :deep(.product-image img) {
  height: 150px;
  max-width: 210px;
}

.favorite-pagination {
  display: flex;
  justify-content: center;
  margin-top: 24px;
  padding-top: 4px;
}

.favorite-state,
.favorite-empty {
  min-height: 260px;
  border: 1px dashed #cbd5e1;
  border-radius: 14px;
  background: #f8fafc;
  color: #475569;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.favorite-state {
  font-size: 15px;
  font-weight: 700;
}

.favorite-empty {
  padding: 30px;
  flex-direction: column;
}

.empty-icon {
  width: 58px;
  height: 58px;
  border-radius: 50%;
  background: #fff1f2;
  color: #e11d48;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 25px;
  margin-bottom: 14px;
}

.favorite-empty h2 {
  margin: 0 0 8px;
  color: #0f172a;
  font-size: 20px;
  font-weight: 900;
}

.favorite-empty p {
  max-width: 420px;
  margin: 0 0 18px;
  color: #64748b;
  font-size: 14px;
  line-height: 1.55;
}

.browse-link {
  min-height: 40px;
  padding: 0 16px;
  border-radius: 999px;
  background: #0d6efd;
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
}

@media (max-width: 1200px) {
  .favorite-grid {
    grid-template-columns: repeat(2, minmax(240px, 1fr));
  }
}

@media (max-width: 640px) {
  .favorite-page {
    padding: 16px;
  }

  .favorite-header {
    flex-direction: column;
  }

  .favorite-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }
}
</style>
