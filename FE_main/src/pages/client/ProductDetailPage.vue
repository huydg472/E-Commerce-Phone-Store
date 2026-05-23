<script setup>
import ProductGallery from '@/components/product/ProductGallery.vue'
import ProductVariantSelector from '@/components/product/ProductVariantSelector.vue'
import ProductSpecificationBox from '@/components/product/ProductSpecificationBox.vue'
import ProductCard from '@/components/product/ProductCard.vue'

const relatedProducts = [
  {
    id: 1,
    name: 'iPhone 15 Pro 256GB',
    image: '/images/products/iphone-15-pro-max.png',
    price: '28.990.000đ',
  },
  {
    id: 2,
    name: 'iPhone 14 Pro Max 256GB',
    image: '/images/products/iphone-14.png',
    price: '37.490.000đ',
  },
  {
    id: 3,
    name: 'Samsung Galaxy S24 Ultra 256GB',
    image: '/images/products/samsung-galaxy-s24-ultra.png',
    price: '25.990.000đ',
  },
  {
    id: 4,
    name: 'OPPO Find X7 Ultra 5G 256GB',
    image: '/images/products/oppo-reno11-f.png',
    price: '23.990.000đ',
  },
]
</script>

<template>
  <section class="product-detail-page">
    <div class="container">
      <div class="breadcrumb-area">
        <RouterLink to="/">Trang chủ</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <RouterLink to="/products">Apple</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <span>iPhone 15 Pro Max 256GB</span>
      </div>

      <div class="product-detail-layout">
        <ProductGallery/>

        <div class="product-info">
          <h1>iPhone 15 Pro Max 256GB</h1>

          <div class="rating-row">
            <div class="stars">
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-half"></i>
            </div>

            <span>(126 đánh giá)</span>
            <span class="line"></span>
            <button type="button">Hỏi đáp</button>
          </div>

          <div class="price-row">
            <span class="sale-price">34.990.000đ</span>
            <del>37.990.000đ</del>
            <strong>-8%</strong>
          </div>

          <div class="stock-status">
            <i class="bi bi-check-lg"></i>
            Còn hàng
          </div>

          <ProductVariantSelector/>
        </div>

        <ProductSpecificationBox/>
      </div>

      <div class="bottom-layout">
        <div class="description-card">
          <ul class="nav nav-tabs detail-tabs">
            <li class="nav-item">
              <button class="nav-link active" type="button">
                Mô tả sản phẩm
              </button>
            </li>

            <li class="nav-item">
              <button class="nav-link" type="button">
                Thông số kỹ thuật
              </button>
            </li>

          </ul>

          <div class="description-content">
            <p>
              Được chế tác từ titan cấp 5, iPhone 15 Pro Max nhẹ hơn nhưng bền bỉ hơn bao giờ hết.
              Trang bị chip A17 Pro mạnh mẽ, camera 48MP đột phá và thời lượng pin ấn tượng.
            </p>

            <p>
              Màn hình Super Retina XDR 6,7 inch mang đến trải nghiệm hiển thị sắc nét, sống động.
              Với khả năng quay video ProRes và kết nối USB-C.
            </p>

            <p>
              iPhone 15 Pro Max là công cụ lý tưởng cho công việc và giải trí – mạnh mẽ,
              linh hoạt và xứng tầm đẳng cấp.
            </p>
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
                :to="`/products/${product.id}`"
            />
          </div>
        </div>
      </div>
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

.product-detail-layout {
  display: grid;
  grid-template-columns: 1.45fr 1.2fr 0.85fr;
  gap: 24px;
  align-items: start;
}

.product-info h1 {
  margin: 0 0 12px;
  color: #111827;
  font-size: 30px;
  font-weight: 800;
}

.rating-row {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 14px;
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
  gap: 16px;
  margin-bottom: 10px;
}

.sale-price {
  color: #0057ff;
  font-size: 32px;
  font-weight: 900;
}

.price-row del {
  color: #64748b;
  font-size: 17px;
  font-weight: 600;
}

.price-row strong {
  min-width: 54px;
  height: 28px;
  border: 1px solid #ef4444;
  border-radius: 5px;
  color: #ef4444;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  font-weight: 800;
}

.stock-status {
  color: #16a34a;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 800;
}

.bottom-layout {
  margin-top: 22px;
  display: grid;
  grid-template-columns: 1fr 0.95fr;
  gap: 24px;
}

.description-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #ffffff;
  overflow: hidden;
}

.detail-tabs {
  border-bottom: 1px solid #e5e7eb;
  padding: 0 16px;
}

.detail-tabs .nav-link {
  min-height: 50px;
  border: none;
  color: #334155;
  font-size: 15px;
  font-weight: 800;
}

.detail-tabs .nav-link.active {
  color: #0d6efd;
  border-bottom: 3px solid #0d6efd;
  background: transparent;
}

.description-content {
  padding: 20px 26px;
}

.description-content p {
  margin-bottom: 14px;
  color: #1f2937;
  font-size: 15px;
  line-height: 1.75;
  font-weight: 500;
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
  font-size: 22px;
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
  gap: 14px;
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
    grid-template-columns: 1fr 1fr;
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
    font-size: 24px;
  }

  .sale-price {
    font-size: 26px;
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