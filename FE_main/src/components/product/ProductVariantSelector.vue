<script setup>
import {computed, ref} from 'vue'

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
})

const emit = defineEmits([
  'update:selectedStorage',
  'update:selectedColor',
  'add-to-cart',
])

const fallbackStorages = ['256GB', '512GB', '1TB']

const fallbackColors = [
  {name: 'Titan Tự Nhiên', color: '#b9b3a9'},
  {name: 'Titan Xanh', color: '#0f1d2e'},
  {name: 'Titan Trắng', color: '#f8fafc'},
  {name: 'Titan Đen', color: '#24211f'},
]

const quantity = ref(1)

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
  quantity.value++
}

const handleAddToCart = () => {
  if (!props.productVariantId || props.isOutOfStock) {
    return
  }

  emit('add-to-cart', {
    productVariantId: props.productVariantId,
    quantity: quantity.value,
  })
}
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

      <div class="buy-row">
        <div class="quantity-box" :class="{ disabled: isOutOfStock }">
          <button type="button" :disabled="isOutOfStock" @click="decreaseQuantity">-</button>
          <span>{{ quantity }}</span>
          <button type="button" :disabled="isOutOfStock" @click="increaseQuantity">+</button>
        </div>

        <button type="button" class="buy-now-btn" :disabled="isOutOfStock" @click="handleAddToCart">
          <strong>Mua ngay</strong>
          <span>Giao hàng tận nơi hoặc nhận tại cửa hàng</span>
        </button>
      </div>
    </div>

    <div class="action-row">
      <button type="button" class="cart-btn" :disabled="isOutOfStock" @click="handleAddToCart">
        <i class="bi bi-cart3"></i>
        Thêm vào giỏ hàng
      </button>
    </div>
  </div>
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
