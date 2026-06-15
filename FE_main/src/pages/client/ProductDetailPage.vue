<script setup>
import ProductGallery from '@/components/product/ProductGallery.vue'
import ProductVariantSelector from '@/components/product/ProductVariantSelector.vue'
import ProductSpecificationBox from '@/components/product/ProductSpecificationBox.vue'
import ProductCard from '@/components/product/ProductCard.vue'
import {useProductDetailPage} from '@/composables/useProductDetailPage'

const {
  currentProduct,
  loading,
  notFound,
  selectedStorage,
  selectedColor,
  activeBottomTab,
  favoriteLoading,
  getDetailImages,
  currentName,
  currentProductFavorite,
  currentPrice,
  currentOldPrice,
  hasOldPrice,
  discountPercent,
  currentVariantInStock,
  currentProductVariantId,
  handleFavoriteToggle,
  productStorageOptions,
  productColorOptions,
  currentSelectedVariant,
  currentVariantAvailableQuantity,
  currentVariantRemainingCartQuantity,
  handleAddToCart,
  handleBuyNow,
  getProductSpecifications,
  hasDescription,
  currentDescription,
  bottomSpecifications,
  brandFilterLink,
  currentBrandName,
  relatedProducts,
  formatCurrency,
} = useProductDetailPage()
</script>

<template>
  <section class="product-detail-page">
    <div class="container">
      <div class="breadcrumb-area">
        <RouterLink to="/">Trang chủ</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <RouterLink :to="brandFilterLink">{{ currentBrandName }}</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <span>{{ currentName }}</span>
      </div>

      <div v-if="loading" class="detail-state">
        Đang tải sản phẩm...
      </div>

      <div v-else-if="notFound" class="detail-state">
        Không tìm thấy sản phẩm phù hợp.
      </div>

      <template v-else>
        <div class="product-detail-layout">
          <ProductGallery
              :images="getDetailImages(currentProduct)"
              :title="currentName"
          />

          <div class="product-info">
            <h1>{{ currentName }}</h1>

            <div class="rating-row">
              <div class="stars">
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-fill"></i>
                <i class="bi bi-star-half"></i>
              </div>

              <span>({{ currentProduct?.rating_count ?? currentProduct?.review_count ?? 0 }} đánh giá)</span>
              <span class="line"></span>
              <button type="button">Hỏi đáp</button>
            </div>

            <div class="price-row">
              <span class="sale-price">{{ formatCurrency(currentPrice) }}</span>
              <del v-if="hasOldPrice">{{ formatCurrency(currentOldPrice) }}</del>
              <strong v-if="discountPercent">-{{ discountPercent }}</strong>
            </div>

            <div class="status-actions">
              <div class="stock-status" :class="{ 'is-out': !currentVariantInStock }">
                <i class="bi" :class="currentVariantInStock ? 'bi-check-lg' : 'bi-x-circle'"></i>
                {{ currentVariantInStock ? 'Còn hàng' : 'Hết hàng' }}
              </div>

              <button
                  type="button"
                  class="favorite-detail-btn"
                  :class="{ active: currentProductFavorite }"
                  :disabled="favoriteLoading || !currentProductVariantId"
              @click="handleFavoriteToggle"
              >
                <i :class="currentProductFavorite ? 'bi bi-heart-fill' : 'bi bi-heart'"></i>
                {{ currentProductFavorite ? 'Đã yêu thích' : 'Yêu thích' }}
              </button>
            </div>

            <ProductVariantSelector
                :storages="productStorageOptions"
                :colors="productColorOptions"
                :is-out-of-stock="!currentVariantInStock"
                :product-variant-id="currentSelectedVariant?.id"
                :max-quantity="currentVariantAvailableQuantity"
                :max-cart-quantity="currentVariantRemainingCartQuantity"
                v-model:selectedStorage="selectedStorage"
                v-model:selectedColor="selectedColor"
                @add-to-cart="handleAddToCart"
                @buy-now="handleBuyNow"
            />
          </div>

          <ProductSpecificationBox
              :specifications="getProductSpecifications(currentProduct)"
          />
        </div>

        <div class="bottom-layout">
          <div class="description-card">
            <ul class="nav nav-tabs detail-tabs">
              <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ active: activeBottomTab === 'description' }"
                    type="button"
                    @click="activeBottomTab = 'description'"
                >
                  Mô tả sản phẩm
                </button>
              </li>

              <li class="nav-item">
                <button
                    class="nav-link"
                    :class="{ active: activeBottomTab === 'specs' }"
                    type="button"
                    @click="activeBottomTab = 'specs'"
                >
                  Thông số kỹ thuật
                </button>
              </li>
            </ul>

            <div v-if="activeBottomTab === 'description'" class="description-content">
              <p v-if="hasDescription">{{ currentDescription }}</p>
              <p v-else>Đang cập nhật mô tả sản phẩm.</p>
            </div>

            <div v-else class="specs-content">
              <div
                  v-for="spec in bottomSpecifications"
                  :key="spec.label"
                  class="spec-row"
              >
                <span class="spec-label">{{ spec.label }}</span>
                <span class="spec-value">{{ spec.value }}</span>
              </div>
            </div>
          </div>

        <div class="related-section">
          <div class="related-header">
              <h2>Sản phẩm liên quan</h2>

              <button type="button">
                <i class="bi bi-chevron-right"></i>
              </button>
            </div>

            <div class="related-grid">
              <ProductCard
                  v-for="product in relatedProducts"
                  :key="product.id"
                  :name="product.name"
                  :image="product.image"
                  :price="product.price"
                  :to="`/products/${product.slug || product.id}`"
              />
            </div>
          </div>
        </div>
      </template>
    </div>
  </section>
</template>

<style scoped>
.product-detail-page {
  padding: 24px 0 48px;
  background: #ffffff;
}

.breadcrumb-area {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 14px;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
}

.breadcrumb-area a {
  color: #64748b;
  text-decoration: none;
}

.breadcrumb-area a:hover,
.breadcrumb-area span {
  color: #0d6efd;
}

.breadcrumb-area i {
  font-size: 11px;
}

.detail-state {
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #f8fafc;
  color: #334155;
  font-size: 15px;
  font-weight: 600;
}

.product-detail-layout {
  display: grid;
  grid-template-columns: 1.18fr 1fr 0.82fr;
  gap: 18px;
  align-items: start;
}

.product-info h1 {
  margin: 0 0 10px;
  color: #111827;
  font-size: 26px;
  line-height: 1.15;
  font-weight: 800;
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
  margin-bottom: 12px;
}

.stars {
  color: #f59e0b;
  display: inline-flex;
  gap: 2px;
}

.rating-row .line {
  width: 1px;
  height: 14px;
  background: #cbd5e1;
}

.rating-row button {
  border: none;
  background: transparent;
  color: #334155;
  font-weight: 700;
}

.price-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.sale-price {
  color: #0057ff;
  font-size: 28px;
  font-weight: 900;
  line-height: 1;
}

.price-row del {
  color: #64748b;
  font-size: 15px;
  font-weight: 600;
}

.price-row strong {
  min-width: 48px;
  height: 26px;
  border: 1px solid #ef4444;
  border-radius: 5px;
  color: #ef4444;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 800;
}

.stock-status {
  min-height: 38px;
  padding: 0 14px;
  border: 1px solid #bbf7d0;
  border-radius: 999px;
  background: #f0fdf4;
  color: #16a34a;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 14px;
  font-weight: 800;
}

.stock-status.is-out {
  border-color: #fecdd3;
  background: #fff1f2;
  color: #ef4444;
}

.favorite-detail-btn {
  min-height: 38px;
  padding: 0 14px;
  border: 1px solid #fecdd3;
  border-radius: 999px;
  background: #ffffff;
  color: #e11d48;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
}

.status-actions {
  margin: 10px 0 14px;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.favorite-detail-btn:hover,
.favorite-detail-btn.active {
  border-color: #fb7185;
  background: #fff1f2;
  color: #be123c;
  transform: translateY(-1px);
}

.favorite-detail-btn:disabled {
  opacity: 0.65;
  cursor: not-allowed;
  transform: none;
}

.bottom-layout {
  margin-top: 16px;
  display: grid;
  grid-template-columns: 1fr 0.95fr;
  gap: 18px;
}

.description-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #ffffff;
  overflow: hidden;
}

.detail-tabs {
  border-bottom: 1px solid #e5e7eb;
  padding: 0 12px;
}

.detail-tabs .nav-link {
  min-height: 44px;
  border: none;
  color: #334155;
  font-size: 14px;
  font-weight: 800;
}

.detail-tabs .nav-link.active {
  color: #0d6efd;
  border-bottom: 3px solid #0d6efd;
  background: transparent;
}

.description-content {
  padding: 16px 20px;
}

.specs-content {
  padding: 16px 20px 20px;
}

.description-content p {
  margin-bottom: 12px;
  color: #1f2937;
  font-size: 14px;
  line-height: 1.7;
  font-weight: 500;
}

.spec-row {
  display: grid;
  grid-template-columns: minmax(0, 220px) 1fr;
  gap: 16px;
  align-items: center;
  padding: 10px 0;
  border-bottom: 1px solid #eef2f7;
}

.spec-row:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.spec-label {
  color: #475569;
  font-size: 14px;
  font-weight: 700;
}

.spec-value {
  color: #111827;
  font-size: 14px;
  font-weight: 600;
  text-align: right;
}

.related-section {
  min-width: 0;
}

.related-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 12px;
}

.related-header h2 {
  margin: 0;
  color: #111827;
  font-size: 18px;
  font-weight: 800;
}

.related-header button {
  width: 42px;
  height: 42px;
  border: 1px solid #e5e7eb;
  border-radius: 50%;
  background: #ffffff;
  color: #111827;
}

.related-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 12px;
}

.related-grid :deep(.product-card) {
  min-height: 230px;
  padding: 12px;
}

.related-grid :deep(.product-image) {
  height: 105px;
}

.related-grid :deep(.product-image img) {
  height: 100px;
}

.related-grid :deep(.product-name) {
  font-size: 13px;
}

.related-grid :deep(.sale-price) {
  font-size: 14px;
}

.related-grid :deep(.product-storage),
.related-grid :deep(.discount-badge),
.related-grid :deep(.old-price) {
  display: none;
}

@media (max-width: 1200px) {
  .product-detail-layout {
    grid-template-columns: 1.15fr 1fr;
  }

  .product-detail-layout > :last-child {
    grid-column: 1 / -1;
  }

  .bottom-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 992px) {
  .product-detail-layout {
    grid-template-columns: 1fr;
  }

  .related-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 576px) {
  .product-info h1 {
    font-size: 22px;
  }

  .sale-price {
    font-size: 24px;
  }

  .rating-row,
  .price-row {
    flex-wrap: wrap;
  }

  .related-grid {
    grid-template-columns: 1fr;
  }
}
</style>

