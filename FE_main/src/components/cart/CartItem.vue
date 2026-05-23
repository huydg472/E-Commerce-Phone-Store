<script setup>
defineProps({
  image: {
    type: String,
    default: '',
  },
  name: {
    type: String,
    default: '',
  },
  version: {
    type: String,
    default: '',
  },
  color: {
    type: String,
    default: '',
  },
  colorCode: {
    type: String,
    default: '#333333',
  },
  price: {
    type: String,
    default: '',
  },
  total: {
    type: String,
    default: '',
  },
  quantity: {
    type: Number,
    default: 1,
  },
})

const fallbackImage = 'https://placehold.co/150x150/f1f5f9/2563eb?text=Zin+Mobile'

const handleImageError = (event) => {
  event.target.src = fallbackImage
}
</script>

<template>
  <div class="cart-item">
    <div class="cart-check">
      <input type="checkbox" class="form-check-input" checked />
    </div>

    <div class="product-cell">
      <div class="product-image">
        <img
            :src="image || fallbackImage"
            :alt="name"
            @error="handleImageError"
        />
      </div>

      <div class="product-info">
        <h3>{{ name }}</h3>

        <p>Phiên bản: {{ version }}</p>

        <p class="color-line">
          <span
              class="color-dot"
              :style="{ backgroundColor: colorCode }"
          ></span>
          Màu: {{ color }}
        </p>

        <span class="stock-status">
          <i class="bi bi-check-circle"></i>
          Còn hàng
        </span>
      </div>
    </div>

    <div class="price-cell">
      {{ price }}
    </div>

    <div class="quantity-cell">
      <div class="quantity-box">
        <button type="button">−</button>
        <span>{{ quantity }}</span>
        <button type="button">+</button>
      </div>
    </div>

    <div class="total-cell">
      {{ total }}
    </div>

    <div class="action-cell">
      <button type="button" class="delete-btn">
        <i class="bi bi-trash"></i>
      </button>
    </div>
  </div>
</template>

<style scoped>
.cart-item {
  display: grid;
  grid-template-columns: 32px minmax(330px, 1fr) 105px 105px 118px 54px;
  align-items: center;
  gap: 10px;
  padding: 18px;
  border-top: 1px solid #e5e7eb;
}

.cart-check {
  display: flex;
  justify-content: center;
}

.cart-check .form-check-input {
  width: 18px;
  height: 18px;
  box-shadow: none;
}

.cart-check .form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.product-cell {
  display: grid;
  grid-template-columns: 96px minmax(0, 1fr);
  align-items: center;
  gap: 14px;
  min-width: 0;
}

.product-image {
  width: 96px;
  height: 96px;
  border-radius: 10px;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
}

.product-image img {
  max-width: 86px;
  max-height: 86px;
  object-fit: contain;
}

.product-info {
  min-width: 0;
}

.product-info h3 {
  margin: 0 0 8px;
  color: #111827;
  font-size: 15px;
  font-weight: 800;
  line-height: 1.35;
}

.product-info p {
  margin: 0 0 6px;
  color: #475569;
  font-size: 13px;
  font-weight: 600;
}

.color-line {
  display: flex;
  align-items: center;
  gap: 7px;
}

.color-dot {
  width: 15px;
  height: 15px;
  border: 1px solid #cbd5e1;
  border-radius: 50%;
  display: inline-block;
  flex-shrink: 0;
}

.stock-status {
  color: #16a34a;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 700;
}

.price-cell,
.quantity-cell,
.total-cell,
.action-cell {
  display: flex;
  align-items: center;
  justify-content: center;
}

.price-cell,
.total-cell {
  color: #111827;
  font-size: 14px;
  font-weight: 800;
  white-space: nowrap;
}

.quantity-box {
  width: 92px;
  height: 36px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  display: grid;
  grid-template-columns: 30px 1fr 30px;
  overflow: hidden;
}

.quantity-box button {
  border: none;
  background: #ffffff;
  color: #111827;
  font-size: 15px;
  font-weight: 800;
}

.quantity-box span {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #111827;
  font-size: 14px;
  font-weight: 800;
  border-left: 1px solid #edf2f7;
  border-right: 1px solid #edf2f7;
}

.delete-btn {
  border: none;
  background: transparent;
  color: #111827;
  font-size: 20px;
}

.delete-btn:hover {
  color: #ef4444;
}

@media (max-width: 1200px) {
  .cart-item {
    grid-template-columns: 34px 1fr;
    align-items: flex-start;
    gap: 12px;
    padding: 18px 16px;
  }

  .product-cell {
    grid-column: 2;
    grid-template-columns: 96px minmax(0, 1fr);
  }

  .price-cell,
  .quantity-cell,
  .total-cell,
  .action-cell {
    grid-column: 2;
  }

  .price-cell::before {
    content: 'Đơn giá: ';
    color: #64748b;
    font-weight: 700;
  }

  .total-cell::before {
    content: 'Thành tiền: ';
    color: #64748b;
    font-weight: 700;
  }

  .quantity-cell,
  .action-cell {
    justify-content: flex-start;
  }
}

@media (max-width: 576px) {
  .cart-item {
    padding: 16px 14px;
  }

  .product-cell {
    grid-template-columns: 1fr;
  }
}
</style>