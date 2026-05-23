<script setup>
const products = [
  {
    id: 1,
    name: 'iPhone 15 Pro Max 256GB',
    image: '/images/products/iphone-15-pro-max.png',
    color: 'Titan Tự Nhiên',
    quantity: 1,
    price: '34.990.000đ',
  },
  {
    id: 2,
    name: 'Samsung Galaxy S24 Ultra 5G 256GB',
    image: '/images/products/samsung-galaxy-s24-ultra.png',
    color: 'Titanium Gray',
    quantity: 1,
    price: '28.990.000đ',
  },
]

const fallbackImage = 'https://placehold.co/120x120/f1f5f9/2563eb?text=Zin+Mobile'

const handleImageError = (event) => {
  event.target.src = fallbackImage
}
</script>

<template>
  <aside class="summary-card">
    <h2>Tóm tắt đơn hàng</h2>

    <div class="summary-products">
      <div
          v-for="product in products"
          :key="product.id"
          class="summary-product"
      >
        <div class="product-left">
          <div class="product-thumb">
            <img
                :src="product.image || fallbackImage"
                :alt="product.name"
                @error="handleImageError"
            />
          </div>

          <div class="product-info">
            <h3>{{ product.name }}</h3>
            <p>Màu: {{ product.color }}</p>
            <span>Số lượng: {{ product.quantity }}</span>
          </div>
        </div>

        <strong class="product-price">{{ product.price }}</strong>
      </div>
    </div>

    <div class="summary-pricing">
      <div class="price-row">
        <span>Tạm tính</span>
        <strong>63.980.000đ</strong>
      </div>

      <div class="price-row">
        <span>Giảm giá</span>
        <strong class="discount">-2.000.000đ</strong>
      </div>

      <div class="price-row">
        <span>
          Phí vận chuyển
          <i class="bi bi-info-circle"></i>
        </span>
        <strong class="free">Miễn phí</strong>
      </div>
    </div>

    <div class="summary-total">
      <div>
        <span>Tổng cộng</span>
      </div>

      <div class="total-right">
        <strong>61.980.000đ</strong>
        <p>(Đã bao gồm VAT)</p>
      </div>
    </div>

    <button type="button" class="order-btn">
      <i class="bi bi-lock"></i>
      Đặt hàng
    </button>

    <RouterLink to="/cart" class="back-cart-btn">
      <i class="bi bi-arrow-left"></i>
      <i class="bi bi-cart3"></i>
      Quay lại giỏ hàng
    </RouterLink>

    <div class="agree-note">
      <i class="bi bi-shield-check"></i>
      <span>
        Bằng việc đặt hàng, bạn đồng ý với
        <a href="javascript:void(0)">điều khoản mua hàng</a>
      </span>
    </div>
  </aside>
</template>

<style scoped>
.summary-card {
  padding: 18px 18px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #ffffff;
}

.summary-card h2 {
  margin: 0 0 16px;
  color: #0f172a;
  font-size: 20px;
  font-weight: 800;
}

.summary-products {
  border-bottom: 1px solid #e5e7eb;
}

.summary-product {
  padding: 12px 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}

.summary-product + .summary-product {
  border-top: 1px solid #eef2f7;
}

.product-left {
  display: flex;
  align-items: center;
  gap: 12px;
  min-width: 0;
}

.product-thumb {
  width: 76px;
  height: 76px;
  border-radius: 10px;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.product-thumb img {
  max-width: 68px;
  max-height: 68px;
  object-fit: contain;
}

.product-info {
  min-width: 0;
}

.product-info h3 {
  margin: 0 0 6px;
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
  line-height: 1.4;
}

.product-info p,
.product-info span {
  display: block;
  margin: 0;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  line-height: 1.5;
}

.product-price {
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
  white-space: nowrap;
}

.summary-pricing {
  padding: 16px 0;
  border-bottom: 1px solid #e5e7eb;
}

.price-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.price-row + .price-row {
  margin-top: 12px;
}

.price-row span {
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
}

.price-row strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
}

.price-row .discount {
  color: #ef4444;
}

.price-row .free {
  color: #16a34a;
}

.summary-total {
  padding: 16px 0 14px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.summary-total span {
  color: #0f172a;
  font-size: 16px;
  font-weight: 800;
}

.total-right {
  text-align: right;
}

.total-right strong {
  color: #0d6efd;
  font-size: 18px;
  font-weight: 900;
}

.total-right p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
}

.order-btn,
.back-cart-btn {
  width: 100%;
  height: 44px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 15px;
  font-weight: 800;
  text-decoration: none;
}

.order-btn {
  border: none;
  background: #0d6efd;
  color: #ffffff;
}

.back-cart-btn {
  margin-top: 12px;
  border: 1px solid #0d6efd;
  background: #ffffff;
  color: #0d6efd;
}

.agree-note {
  margin-top: 14px;
  padding-top: 14px;
  border-top: 1px solid #eef2f7;
  display: flex;
  align-items: flex-start;
  gap: 8px;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  line-height: 1.5;
}

.agree-note i {
  color: #64748b;
  font-size: 15px;
  margin-top: 1px;
}

.agree-note a {
  color: #0d6efd;
  text-decoration: none;
  font-weight: 700;
}
</style>