<script setup>
import CartItem from '@/components/cart/CartItem.vue'
import CartSummary from '@/components/cart/CartSummary.vue'
import ProductCard from '@/components/product/ProductCard.vue'

const cartItems = [
  {
    id: 1,
    name: 'iPhone 15 Pro Max 256GB',
    image: '/images/products/iphone-15-pro-max.png',
    version: '256GB',
    color: 'Titan Tự nhiên',
    colorCode: '#6d6258',
    price: '34.990.000đ',
    total: '34.990.000đ',
    quantity: 1,
  },
  {
    id: 2,
    name: 'Samsung Galaxy S24 Ultra 5G 256GB',
    image: '/images/products/samsung-galaxy-s24-ultra.png',
    version: '256GB',
    color: 'Titanium Gray',
    colorCode: '#4b4946',
    price: '28.990.000đ',
    total: '28.990.000đ',
    quantity: 1,
  },
]

const suggestedProducts = [
  {
    id: 1,
    name: 'iPhone 15 128GB',
    image: '/images/products/iphone-15.png',
    price: '22.990.000đ',
    rating: '(128)',
  },
  {
    id: 2,
    name: 'Samsung Galaxy S24 5G 256GB',
    image: '/images/products/samsung-galaxy-s24.png',
    price: '21.490.000đ',
    rating: '(96)',
  },
  {
    id: 3,
    name: 'OPPO Reno11 F 5G 256GB',
    image: '/images/products/oppo-reno11-f.png',
    price: '8.990.000đ',
    rating: '(64)',
  },
]
</script>

<template>
  <section class="cart-page">
    <div class="cart-container">
      <div class="breadcrumb-area">
        <RouterLink to="/">Trang chủ</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <span>Giỏ hàng</span>
      </div>

      <h1 class="page-title">Giỏ hàng</h1>

      <div class="cart-layout">
        <div class="cart-left">
          <div class="cart-table">
            <div class="cart-select-row">
              <label>
                <input type="checkbox" class="form-check-input" checked />
                Chọn tất cả (2 sản phẩm)
              </label>
            </div>

            <div class="cart-table-head">
              <span></span>
              <span class="product-head">Sản phẩm</span>
              <span>Đơn giá</span>
              <span>Số lượng</span>
              <span>Thành tiền</span>
              <span>Thao tác</span>
            </div>

            <CartItem
                v-for="item in cartItems"
                :key="item.id"
                :name="item.name"
                :image="item.image"
                :version="item.version"
                :color="item.color"
                :color-code="item.colorCode"
                :price="item.price"
                :total="item.total"
                :quantity="item.quantity"
            />

            <div class="continue-shopping">
              <RouterLink to="/products">
                <i class="bi bi-arrow-left"></i>
                Tiếp tục mua sắm
              </RouterLink>
            </div>
          </div>

          <div class="suggest-section">
            <div class="suggest-header">
              <h2>Có thể bạn sẽ thích</h2>

              <RouterLink to="/products">
                Xem tất cả
                <i class="bi bi-chevron-right"></i>
              </RouterLink>
            </div>

            <div class="suggest-grid">
              <div
                  v-for="product in suggestedProducts"
                  :key="product.id"
                  class="suggest-card"
              >
                <button class="suggest-heart" type="button">
                  <i class="bi bi-heart"></i>
                </button>

                <ProductCard
                    :name="product.name"
                    :image="product.image"
                    :price="product.price"
                    :to="`/products/${product.id}`"
                />

                <div class="suggest-rating">
                  <span>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                    <i class="bi bi-star-fill"></i>
                  </span>

                  <em>{{ product.rating }}</em>
                </div>
              </div>
            </div>
          </div>
        </div>

        <CartSummary />
      </div>
    </div>
  </section>
</template>

<style scoped>
.cart-page {
  padding: 24px 0 52px;
  background: #ffffff;
}

.cart-container {
  width: min(100% - 36px, 1500px);
  margin: 0 auto;
}

.breadcrumb-area {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 14px;
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

.page-title {
  margin: 0 0 18px;
  color: #111827;
  font-size: 36px;
  font-weight: 900;
}

.cart-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 360px;
  gap: 22px;
  align-items: flex-start;
}

.cart-left {
  min-width: 0;
}

.cart-table {
  width: 100%;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #ffffff;
  overflow: hidden;
}

.cart-select-row {
  min-height: 56px;
  padding: 0 18px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
}

.cart-select-row label {
  color: #111827;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 15px;
  font-weight: 800;
}

.cart-select-row .form-check-input {
  width: 18px;
  height: 18px;
  box-shadow: none;
}

.cart-select-row .form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.cart-table-head {
  min-height: 48px;
  padding: 0 18px;
  display: grid;
  grid-template-columns: 32px minmax(330px, 1fr) 105px 105px 118px 54px;
  align-items: center;
  gap: 10px;
  color: #475569;
  font-size: 13px;
  font-weight: 800;
  border-bottom: 1px solid #eef2f7;
}
.cart-table-head .product-head {
  justify-content: center;
}

.cart-table-head span {
  white-space: nowrap;
  display: flex;
  align-items: center;
  justify-content: center;
}

.continue-shopping {
  padding: 18px 26px;
  border-top: 1px solid #e5e7eb;
}

.continue-shopping a {
  color: #0d6efd;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  font-size: 15px;
  font-weight: 800;
}

.suggest-section {
  margin-top: 24px;
}

.suggest-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 14px;
}

.suggest-header h2 {
  margin: 0;
  color: #111827;
  font-size: 22px;
  font-weight: 900;
}

.suggest-header a {
  color: #0d6efd;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
}

.suggest-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}

.suggest-card {
  position: relative;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #ffffff;
  overflow: hidden;
  padding-bottom: 14px;
}

.suggest-heart {
  position: absolute;
  top: 14px;
  right: 16px;
  border: none;
  background: transparent;
  color: #64748b;
  font-size: 21px;
  z-index: 5;
}

.suggest-card :deep(.product-card) {
  border: none;
  box-shadow: none;
  min-height: 196px;
  padding: 12px 14px 4px;
}

.suggest-card :deep(.product-card:hover) {
  transform: none;
  box-shadow: none;
}

.suggest-card :deep(.wishlist-btn) {
  display: none;
}

.suggest-card :deep(.product-image) {
  height: 108px;
}

.suggest-card :deep(.product-image img) {
  height: 104px;
}

.suggest-card :deep(.product-name) {
  min-height: 38px;
  font-size: 14px;
}

.suggest-card :deep(.sale-price) {
  font-size: 17px;
}

.suggest-rating {
  padding: 0 16px;
  display: flex;
  align-items: center;
  gap: 10px;
}

.suggest-rating span {
  color: #f59e0b;
  display: inline-flex;
  gap: 3px;
  font-size: 13px;
}

.suggest-rating em {
  color: #64748b;
  font-size: 13px;
  font-style: normal;
  font-weight: 600;
}

@media (max-width: 1200px) {
  .cart-layout {
    grid-template-columns: 1fr;
  }

  .cart-table-head {
    display: none;
  }

  .suggest-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .cart-container {
    width: min(100% - 24px, 1500px);
  }

  .page-title {
    font-size: 30px;
  }

  .suggest-grid {
    grid-template-columns: 1fr;
  }

  .cart-select-row {
    padding: 0 16px;
  }
}
</style>