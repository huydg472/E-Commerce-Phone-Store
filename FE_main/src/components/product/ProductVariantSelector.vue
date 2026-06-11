<script setup>
import {computed, ref, watch} from 'vue'

const props = defineProps({
  storages: {
    type: Array,
    default: () => [],
  },
  colors: {
    type: Array,
    default: () => [],
  },
  selectedStorage: {
    type: String,
    default: '',
  },
  selectedColor: {
    type: String,
    default: '',
  },
  isOutOfStock: {
    type: Boolean,
    default: false,
  },
  productVariantId: {
    type: [String, Number],
    default: null,
  },
  maxQuantity: {
    type: Number,
    default: 0,
  },
  maxCartQuantity: {
    type: Number,
    default: null,
  },
})

const emit = defineEmits([
  'update:selectedStorage',
  'update:selectedColor',
  'add-to-cart',
  'buy-now',
])

const fallbackStorages = ['256GB', '512GB', '1TB']

const fallbackColors = [
  {name: 'Titan Tự Nhiên', color: '#b9b3a9'},
  {name: 'Titan Xanh', color: '#0f1d2e'},
  {name: 'Titan Trắng', color: '#f8fafc'},
  {name: 'Titan Đen', color: '#24211f'},
]

const quantity = ref(1)
const buyNowQuantity = ref(1)
const buyNowVisible = ref(false)

const maxSelectableQuantity = computed(() => {
  const rawValue = Number(props.maxQuantity)
  return rawValue > 0 ? rawValue : Number.POSITIVE_INFINITY
})

const maxAddToCartQuantity = computed(() => {
  if (props.maxCartQuantity === null || props.maxCartQuantity === undefined) {
    return maxSelectableQuantity.value
  }

  const rawValue = Number(props.maxCartQuantity)
  return rawValue > 0 ? rawValue : 0
})

const clampQuantity = (value) => {
  const normalized = Math.max(Number(value) || 1, 1)

  if (!Number.isFinite(maxSelectableQuantity.value)) {
    return normalized
  }

  return Math.min(normalized, maxSelectableQuantity.value)
}

const clampCartQuantity = (value) => {
  const maxValue = maxAddToCartQuantity.value

  if (maxValue <= 0) {
    return 1
  }

  const normalized = Math.max(Number(value) || 1, 1)

  if (!Number.isFinite(maxValue)) {
    return normalized
  }

  return Math.min(normalized, maxValue)
}

const canAddToCart = computed(() => {
  return Boolean(props.productVariantId) && !props.isOutOfStock && maxAddToCartQuantity.value > 0
})

const normalizeOption = (item, fallbackValue = '') => {
  if (typeof item === 'string') {
    return {
      value: item,
      available: true,
      disabled: false,
      quantity: 0,
    }
  }

  const value = item?.value ?? item?.name ?? item?.label ?? fallbackValue
  const available = item?.available ?? !item?.disabled

  return {
    ...item,
    value,
    name: item?.name ?? value,
    color: item?.color ?? item?.value ?? '#e5e7eb',
    available,
    disabled: Boolean(item?.disabled) || !available,
    quantity: Number(item?.quantity ?? 0),
  }
}

const storageOptions = computed(() => {
  const list = Array.isArray(props.storages) ? props.storages.filter(Boolean) : []

  if (!list.length) {
    return fallbackStorages.map((storage) => normalizeOption(storage))
  }

  return list.map((item) => normalizeOption(item))
})

const colorOptions = computed(() => {
  const list = Array.isArray(props.colors) ? props.colors.filter(Boolean) : []

  if (!list.length) {
    return fallbackColors.map((color) => normalizeOption(color, color.name))
  }

  return list.map((item) => normalizeOption(item, item?.name ?? item?.value ?? ''))
})

const activeStorageValue = computed(() => {
  return (
      props.selectedStorage ||
      storageOptions.value.find((item) => !item.disabled)?.value ||
      storageOptions.value[0]?.value ||
      ''
  )
})

const activeColorValue = computed(() => {
  return (
      props.selectedColor ||
      colorOptions.value.find((item) => !item.disabled)?.name ||
      colorOptions.value[0]?.name ||
      ''
  )
})

const selectedStorageModel = computed({
  get: () => activeStorageValue.value,
  set: (value) => emit('update:selectedStorage', value),
})

const selectedColorModel = computed({
  get: () => activeColorValue.value,
  set: (value) => emit('update:selectedColor', value),
})

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const increaseQuantity = () => {
  quantity.value = clampCartQuantity(quantity.value + 1)
}

const decreaseBuyNowQuantity = () => {
  if (buyNowQuantity.value > 1) {
    buyNowQuantity.value--
  }
}

const increaseBuyNowQuantity = () => {
  buyNowQuantity.value = clampQuantity(buyNowQuantity.value + 1)
}

const openBuyNowModal = () => {
  if (!props.productVariantId || props.isOutOfStock) {
    return
  }

  buyNowQuantity.value = clampQuantity(quantity.value)
  buyNowVisible.value = true
}

const closeBuyNowModal = () => {
  buyNowVisible.value = false
}

const confirmBuyNow = () => {
  if (!props.productVariantId || props.isOutOfStock) {
    return
  }

  emit('buy-now', {
    productVariantId: props.productVariantId,
    quantity: clampQuantity(buyNowQuantity.value),
  })

  closeBuyNowModal()
}

const handleAddToCart = () => {
  if (!canAddToCart.value) {
    return
  }

  emit('add-to-cart', {
    productVariantId: props.productVariantId,
    quantity: clampCartQuantity(quantity.value),
  })
}

watch(
    () => [props.productVariantId, props.maxQuantity, props.maxCartQuantity],
    () => {
      quantity.value = clampCartQuantity(quantity.value)
      buyNowQuantity.value = clampQuantity(buyNowQuantity.value)
    }
)
</script>

<template>
  <div class="variant-box">
    <div class="option-group">
      <h4>Dung lượng</h4>

      <div class="option-list">
        <button
            v-for="storage in storageOptions"
            :key="storage.value"
            type="button"
            class="option-btn"
            :class="{ active: activeStorageValue === storage.value, disabled: storage.disabled }"
            :aria-disabled="storage.disabled"
            @click="selectedStorageModel = storage.value"
        >
          {{ storage.value }}
        </button>
      </div>
    </div>

    <div class="option-group">
      <h4>Màu sắc: {{ selectedColor }}</h4>

      <div class="color-list">
        <button
            v-for="color in colorOptions"
            :key="color.name"
            type="button"
            class="color-btn"
            :class="{ active: activeColorValue === color.name, disabled: color.disabled }"
            :aria-disabled="color.disabled"
            @click="selectedColorModel = color.name"
        >
          <span
              class="color-dot"
              :style="{ backgroundColor: color.color || color.value || '#e5e7eb' }"
          ></span>

          {{ color.name }}
        </button>
      </div>
    </div>

    <div class="option-group">
      <h4>Số lượng</h4>
      <p v-if="Number.isFinite(maxSelectableQuantity)" class="quantity-note">
        Còn {{ maxSelectableQuantity }} sản phẩm trong kho.
      </p>

      <div class="buy-row">
        <div class="quantity-box" :class="{ disabled: isOutOfStock }">
          <button type="button" :disabled="isOutOfStock" @click="decreaseQuantity">-</button>
          <span>{{ quantity }}</span>
          <button
              type="button"
              :disabled="isOutOfStock || quantity >= maxAddToCartQuantity"
              @click="increaseQuantity"
          >
            +
          </button>
        </div>

        <button type="button" class="buy-now-btn" :disabled="isOutOfStock" @click="openBuyNowModal">
          <strong>Mua ngay</strong>
          <span>Giao hàng tận nơi hoặc nhận tại cửa hàng</span>
        </button>
      </div>
    </div>

    <div class="action-row">
      <button type="button" class="cart-btn" :disabled="!canAddToCart" @click="handleAddToCart">
        <i class="bi bi-cart3"></i>
        {{ canAddToCart ? 'Thêm vào giỏ hàng' : 'Đã đạt tối đa trong giỏ' }}
      </button>
    </div>
  </div>
  <teleport to="body">
    <div v-if="buyNowVisible" class="buy-now-overlay" @click.self="closeBuyNowModal">
      <div class="buy-now-modal" role="dialog" aria-modal="true" aria-label="Chọn số lượng mua ngay">
        <div class="buy-now-head">
          <div>
            <p>Chọn số lượng</p>
            <h3>Mua ngay</h3>
          </div>

          <button type="button" class="buy-now-close" aria-label="Đóng" @click="closeBuyNowModal">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <p class="buy-now-note">
          Chọn số lượng sản phẩm trước khi chuyển sang trang thanh toán.
        </p>

        <div class="buy-now-quantity">
          <button type="button" :disabled="buyNowQuantity <= 1" @click="decreaseBuyNowQuantity">-</button>
          <span>{{ buyNowQuantity }}</span>
          <button
              type="button"
              :disabled="Number.isFinite(maxSelectableQuantity) && buyNowQuantity >= maxSelectableQuantity"
              @click="increaseBuyNowQuantity"
          >
            +
          </button>
        </div>

        <div class="buy-now-actions">
          <button type="button" class="buy-now-secondary" @click="closeBuyNowModal">
            Hủy
          </button>

          <button type="button" class="buy-now-primary" :disabled="isOutOfStock" @click="confirmBuyNow">
            Đi đến thanh toán
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

<style scoped>
.variant-box {
  margin-top: 14px;
}

.option-group + .option-group {
  margin-top: 14px;
}

.option-group h4 {
  margin-bottom: 8px;
  color: #111827;
  font-size: 14px;
  font-weight: 800;
}

.quantity-note {
  margin: -2px 0 8px;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.option-list,
.color-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.option-btn {
  min-width: 88px;
  height: 38px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  background: #ffffff;
  color: #334155;
  font-size: 13px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.option-btn.active,
.color-btn.active {
  border-color: #0d6efd;
  color: #0d6efd;
  background: #f8fbff;
}

.option-btn.disabled,
.color-btn.disabled {
  opacity: 1;
  cursor: pointer;
}

.color-btn {
  height: 38px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  background: #ffffff;
  color: #334155;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 700;
}

.color-dot {
  width: 16px;
  height: 16px;
  border: 1px solid #dbe3ef;
  border-radius: 50%;
  display: inline-block;
}

.buy-row {
  display: grid;
  grid-template-columns: 140px minmax(0, 1fr);
  gap: 12px;
}

.quantity-box {
  height: 42px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  display: grid;
  grid-template-columns: 38px 1fr 38px;
  overflow: hidden;
}

.quantity-box.disabled {
  opacity: 0.65;
}

.quantity-box button {
  border: none;
  background: #ffffff;
  color: #111827;
  font-size: 16px;
  font-weight: 800;
}

.quantity-box button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.quantity-box span {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #111827;
  font-size: 15px;
  font-weight: 800;
}

.buy-now-btn {
  min-height: 42px;
  width: 100%;
  padding: 7px 12px;
  border: none;
  border-radius: 8px;
  background: #0057ff;
  color: #ffffff;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.buy-now-btn:disabled {
  background: #93c5fd;
  box-shadow: none;
  cursor: not-allowed;
}

.buy-now-btn strong {
  font-size: 14px;
  line-height: 1.15;
}

.buy-now-btn span {
  font-size: 11px;
  line-height: 1.3;
  margin-top: 3px;
}

.action-row {
  margin-top: 12px;
  display: grid;
  grid-template-columns: 1fr;
  gap: 12px;
}

.cart-btn {
  height: 42px;
  border-radius: 8px;
  background: #ffffff;
  color: #0d6efd;
  border: 1px solid #0d6efd;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
}

.cart-btn:disabled {
  opacity: 0.55;
  cursor: not-allowed;
}

.buy-now-overlay {
  position: fixed;
  inset: 0;
  z-index: 1050;
  background: rgba(15, 23, 42, 0.58);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 18px;
}

.buy-now-modal {
  width: min(100%, 420px);
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 24px 80px rgba(15, 23, 42, 0.22);
  padding: 20px;
}

.buy-now-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.buy-now-head p {
  margin: 0 0 4px;
  color: #0d6efd;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.buy-now-head h3 {
  margin: 0;
  color: #0f172a;
  font-size: 20px;
  font-weight: 900;
}

.buy-now-close {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 999px;
  background: #f1f5f9;
  color: #334155;
}

.buy-now-note {
  margin: 10px 0 18px;
  color: #475569;
  font-size: 14px;
  line-height: 1.5;
}

.buy-now-quantity {
  height: 48px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  display: grid;
  grid-template-columns: 48px 1fr 48px;
  overflow: hidden;
}

.buy-now-quantity button {
  border: none;
  background: #f8fafc;
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
}

.buy-now-quantity button:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.buy-now-quantity span {
  display: flex;
  align-items: center;
  justify-content: center;
  color: #0f172a;
  font-size: 16px;
  font-weight: 800;
}

.buy-now-actions {
  margin-top: 18px;
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.buy-now-secondary,
.buy-now-primary {
  height: 44px;
  border-radius: 10px;
  border: none;
  font-size: 14px;
  font-weight: 800;
}

.buy-now-secondary {
  background: #e2e8f0;
  color: #0f172a;
}

.buy-now-primary {
  background: #0057ff;
  color: #ffffff;
}

.buy-now-primary:disabled {
  background: #93c5fd;
  cursor: not-allowed;
}

@media (max-width: 576px) {
  .buy-now-modal {
    padding: 18px;
  }

  .buy-now-actions {
    grid-template-columns: 1fr;
  }
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
