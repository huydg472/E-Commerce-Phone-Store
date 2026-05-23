<script setup>
import { ref } from 'vue'

const selectedStorage = ref('256GB')
const selectedColor = ref('Titan Tự Nhiên')
const quantity = ref(1)

const storages = ['256GB', '512GB', '1TB']

const colors = [
  {
    name: 'Titan Tự Nhiên',
    color: '#b9b3a9',
  },
  {
    name: 'Titan Xanh',
    color: '#0f1d2e',
  },
  {
    name: 'Titan Trắng',
    color: '#f8fafc',
  },
  {
    name: 'Titan Đen',
    color: '#24211f',
  },
]

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const increaseQuantity = () => {
  quantity.value++
}
</script>

<template>
  <div class="variant-box">
    <div class="option-group">
      <h4>Dung lượng</h4>

      <div class="option-list">
        <button
            v-for="storage in storages"
            :key="storage"
            type="button"
            class="option-btn"
            :class="{ active: selectedStorage === storage }"
            @click="selectedStorage = storage"
        >
          {{ storage }}
        </button>
      </div>
    </div>

    <div class="option-group">
      <h4>Màu sắc: {{ selectedColor }}</h4>

      <div class="color-list">
        <button
            v-for="color in colors"
            :key="color.name"
            type="button"
            class="color-btn"
            :class="{ active: selectedColor === color.name }"
            @click="selectedColor = color.name"
        >
          <span
              class="color-dot"
              :style="{ backgroundColor: color.color }"
          ></span>

          {{ color.name }}
        </button>
      </div>
    </div>

    <div class="option-group">
      <h4>Số lượng</h4>

      <div class="buy-row">
        <div class="quantity-box">
          <button type="button" @click="decreaseQuantity">−</button>
          <span>{{ quantity }}</span>
          <button type="button" @click="increaseQuantity">+</button>
        </div>

        <button type="button" class="buy-now-btn">
          <strong>Mua ngay</strong>
          <span>Giao hàng tận nơi hoặc nhận tại cửa hàng</span>
        </button>
      </div>
    </div>

    <div class="action-row">
      <button type="button" class="cart-btn">
        <i class="bi bi-cart3"></i>
        Thêm vào giỏ hàng
      </button>

      <button type="button" class="favorite-btn">
        <i class="bi bi-heart"></i>
        Yêu thích
      </button>
    </div>
  </div>
</template>

<style scoped>
.variant-box {
  margin-top: 18px;
}

.option-group + .option-group {
  margin-top: 18px;
}

.option-group h4 {
  margin-bottom: 10px;
  color: #111827;
  font-size: 15px;
  font-weight: 800;
}

.option-list,
.color-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.option-btn {
  min-width: 96px;
  height: 42px;
  padding: 0 18px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  background: #ffffff;
  color: #334155;
  font-size: 14px;
  font-weight: 700;
}

.option-btn.active,
.color-btn.active {
  border-color: #0d6efd;
  color: #0d6efd;
  background: #f8fbff;
}

.color-btn {
  height: 42px;
  padding: 0 16px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  background: #ffffff;
  color: #334155;
  display: inline-flex;
  align-items: center;
  gap: 9px;
  font-size: 14px;
  font-weight: 700;
}

.color-dot {
  width: 18px;
  height: 18px;
  border: 1px solid #dbe3ef;
  border-radius: 50%;
  display: inline-block;
}

.buy-row {
  display: grid;
  grid-template-columns: 150px minmax(0, 1fr);
  gap: 16px;
}

.quantity-box {
  height: 48px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  display: grid;
  grid-template-columns: 42px 1fr 42px;
  overflow: hidden;
}

.quantity-box button {
  border: none;
  background: #ffffff;
  color: #111827;
  font-size: 18px;
  font-weight: 800;
}

.quantity-box span {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #111827;
  font-size: 16px;
  font-weight: 800;
}

.buy-now-btn {
  height: 48px;
  border: none;
  border-radius: 8px;
  background: #0057ff;
  color: #ffffff;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.buy-now-btn strong {
  font-size: 15px;
  line-height: 1.2;
}

.buy-now-btn span {
  font-size: 12px;
  line-height: 1.2;
  margin-top: 3px;
}

.action-row {
  margin-top: 16px;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.cart-btn,
.favorite-btn {
  height: 46px;
  border-radius: 8px;
  background: #ffffff;
  color: #0d6efd;
  border: 1px solid #0d6efd;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  font-size: 15px;
  font-weight: 800;
}

.favorite-btn {
  color: #111827;
  border-color: #dbe3ef;
}

.favorite-btn:hover {
  color: #ef4444;
  border-color: #ef4444;
}

@media (max-width: 576px) {
  .buy-row,
  .action-row {
    grid-template-columns: 1fr;
  }

  .quantity-box {
    max-width: 170px;
  }
}
</style>