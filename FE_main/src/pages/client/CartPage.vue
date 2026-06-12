<script setup>
import {computed, onMounted, ref, watch} from 'vue'
import CartEmpty from '@/components/cart/CartEmpty.vue'
import CartItem from '@/components/cart/CartItem.vue'
import CartSummary from '@/components/cart/CartSummary.vue'
import ProductCard from '@/components/product/ProductCard.vue'
import {useCouponStore} from '@/stores/couponStore'
import {useCartStore} from '@/stores/cartStore'
import {useProductStore} from '@/stores/productStore'
import {formatCurrency} from '@/utils/formatCurrency'
import {buildProductCards, toNumberPrice} from '@/utils/productCardHelpers'

const cartStore = useCartStore()
const couponStore = useCouponStore()
const productStore = useProductStore()
const isInitialLoading = ref(true)
const selectedItemIds = ref([])
const cartHydrated = ref(false)

const fallbackSuggestImage = '/images/products/iphone-15.png'

const resolveVariant = (item) => {
  return (
      item?.productVariant ??
      item?.product_variant ??
      item?.variant ??
      item?.product_variants?.[0] ??
      null
  )
}

const resolveProduct = (item, variant) => {
  return (
      variant?.product ??
      item?.product ??
      item?.products ??
      null
  )
}

const resolveColorName = (variant) => {
  if (typeof variant?.color === 'object' && variant?.color !== null) {
    return String(
        variant.color.name ??
        variant.color.color_name ??
        variant.color.title ??
        ''
    ).trim()
  }

  return String(
      variant?.color ??
      variant?.color_name ??
      variant?.colorName ??
      variant?.name_color ??
      ''
  ).trim()
}

const resolveColorCode = (variant) => {
  const colorObject = typeof variant?.color === 'object' ? variant.color : null

  return String(
      colorObject?.code ??
      colorObject?.hex ??
      colorObject?.hex_code ??
      colorObject?.color_code ??
      variant?.color_code ??
      variant?.colorCode ??
      variant?.hex_code ??
      variant?.hexCode ??
      variant?.color_hex ??
      variant?.colorHex ??
      '#d1d5db'
  ).trim()
}

const resolveVersion = (variant) => {
  return String(
      variant?.rom ??
      variant?.ROM ??
      variant?.storage ??
      variant?.storage_size ??
      variant?.capacity ??
      ''
  ).trim()
}

const resolveAvailableQuantity = (variant) => {
  return Number(
      variant?.available_quantity ??
      variant?.availableQuantity ??
      variant?.quantity ??
      0
  )
}

const resolveImage = (product, variant) => {
  const firstVariantImage =
      variant?.productVariantImages?.[0] ??
      variant?.product_variant_images?.[0] ??
      variant?.images?.[0] ??
      null

  return (
      firstVariantImage?.image_url ??
      firstVariantImage?.imageUrl ??
      firstVariantImage?.url ??
      firstVariantImage?.path ??
      firstVariantImage?.image ??
      firstVariantImage?.image_path ??
      variant?.thumbnail_url ??
      variant?.thumbnailUrl ??
      variant?.image ??
      product?.thumbnail_url ??
      product?.thumbnailUrl ??
      product?.image ??
      product?.image_url ??
      product?.imageUrl ??
      ''
  )
}

const cartItems = computed(() => {
  const source = cartStore.item?.items
  return Array.isArray(source) ? source : (Array.isArray(cartStore.items) ? cartStore.items : [])
})

const mappedCartItems = computed(() => {
  return cartItems.value.map((item) => {
    const variant = resolveVariant(item)
    const product = resolveProduct(item, variant)
    const quantity = Number(item?.quantity ?? 1)
    const priceValue = toNumberPrice(
        item?.price ??
        variant?.sale_price ??
        variant?.salePrice ??
        variant?.price ??
        variant?.display_price ??
        variant?.displayPrice ??
        product?.display_price ??
        product?.sale_price ??
        product?.price
    )
    const totalValue = toNumberPrice(
        item?.subtotal ??
        item?.total ??
        priceValue * quantity
    )

    return {
      id: item?.id,
      name: product?.name ?? 'Sản phẩm',
      image: resolveImage(product, variant),
      version: resolveVersion(variant),
      color: resolveColorName(variant),
      colorCode: resolveColorCode(variant),
      price: formatCurrency(priceValue),
      total: formatCurrency(totalValue),
      priceValue,
      totalValue,
      quantity,
      maxQuantity: resolveAvailableQuantity(variant),
    }
  })
})

const selectedItems = computed(() => {
  return mappedCartItems.value.filter((item) => selectedItemIds.value.includes(item.id))
})

const summaryItemCount = computed(() => mappedCartItems.value.length)
const selectedItemCount = computed(() => selectedItems.value.length)
const subtotalValue = computed(() => {
  return selectedItems.value.reduce((sum, item) => sum + Number(item.totalValue ?? 0), 0)
})
const discountValue = computed(() => couponStore.discountAmount(subtotalValue.value))
const shippingValue = computed(() => 0)
const totalValue = computed(() => subtotalValue.value)

const isAllSelected = computed(() => {
  return mappedCartItems.value.length > 0 && selectedItemIds.value.length === mappedCartItems.value.length
})

const suggestedProducts = computed(() => {
  const cards = buildProductCards(productStore.items, fallbackSuggestImage)
  return cards.slice(0, 3)
})

watch(mappedCartItems, (items) => {
  if (!cartHydrated.value) {
    return
  }

  const nextIds = items.map((item) => item.id)
  selectedItemIds.value = selectedItemIds.value.filter((id) => nextIds.includes(id))
})

const syncInitialSelection = () => {
  selectedItemIds.value = mappedCartItems.value.map((item) => item.id)
  cartHydrated.value = true
}

const toggleAll = (checked) => {
  if (checked) {
    selectedItemIds.value = mappedCartItems.value.map((item) => item.id)
    return
  }

  selectedItemIds.value = []
}

const toggleItem = (itemId, checked) => {
  if (checked) {
    if (!selectedItemIds.value.includes(itemId)) {
      selectedItemIds.value = [...selectedItemIds.value, itemId]
    }
    return
  }

  selectedItemIds.value = selectedItemIds.value.filter((id) => id !== itemId)
}

const isSelected = (itemId) => {
  return selectedItemIds.value.includes(itemId)
}

const getLiveCartItem = (itemId) => {
  const source = cartStore.item?.items ?? cartStore.items ?? []
  return source.find((cartItem) => cartItem.id === itemId) ?? null
}

const handleIncrease = async (item) => {
  try {
    const liveItem = getLiveCartItem(item.id) ?? item
    const currentQuantity = Number(liveItem.quantity ?? 1)
    const maxQuantity = Number(item.maxQuantity ?? 0)

    if (maxQuantity > 0 && currentQuantity >= maxQuantity) {
      return
    }

    await cartStore.update(item.id, {
      quantity: maxQuantity > 0 ? Math.min(currentQuantity + 1, maxQuantity) : currentQuantity + 1,
    })
  } catch (error) {
    console.error('Không thể tăng số lượng giỏ hàng:', error)
  }
}

const handleDecrease = async (item) => {
  try {
    const liveItem = getLiveCartItem(item.id) ?? item
    const currentQuantity = Number(liveItem.quantity ?? 1)

    if (currentQuantity <= 1) {
      await handleRemove(item)
      return
    }

    await cartStore.update(item.id, {
      quantity: currentQuantity - 1,
    })
  } catch (error) {
    console.error('Không thể giảm số lượng giỏ hàng:', error)
  }
}

const handleRemove = async (item) => {
  try {
    await cartStore.remove(item.id)
    selectedItemIds.value = selectedItemIds.value.filter((id) => id !== item.id)
  } catch (error) {
    console.error('Không thể xóa sản phẩm khỏi giỏ hàng:', error)
  }
}

onMounted(async () => {
  couponStore.hydrate()
  isInitialLoading.value = true

  try {
    await Promise.allSettled([
      cartStore.fetchAll(),
      productStore.fetchAll({status: 'active'}),
    ])
    syncInitialSelection()
  } finally {
    isInitialLoading.value = false
  }
})
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

      <div v-if="isInitialLoading" class="cart-loading">
        <div class="cart-loading-card">
          <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
          <p>Đang tải dữ liệu giỏ hàng...</p>
        </div>
      </div>

      <div v-else class="cart-layout">
        <div class="cart-left">
          <div class="cart-table">
            <div class="cart-select-row">
              <label>
                <input
                    type="checkbox"
                    class="form-check-input"
                    :checked="isAllSelected"
                    @change="toggleAll($event.target.checked)"
                />
                Chọn tất cả ({{ summaryItemCount }} sản phẩm)
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

            <template v-if="mappedCartItems.length">
              <CartItem
                  v-for="item in mappedCartItems"
                  :key="item.id"
                  :name="item.name"
                  :image="item.image"
                  :version="item.version"
                  :color="item.color"
                  :color-code="item.colorCode"
                  :price="item.price"
                  :total="item.total"
                  :quantity="item.quantity"
                  :max-quantity="item.maxQuantity"
                  :selected="isSelected(item.id)"
                  @toggle="(checked) => toggleItem(item.id, checked)"
                  @increase="handleIncrease(item)"
                  @decrease="handleDecrease(item)"
                  @remove="handleRemove(item)"
              />
            </template>

            <CartEmpty v-else />

            <div v-if="mappedCartItems.length" class="continue-shopping">
              <RouterLink to="/products">
                <i class="bi bi-arrow-left"></i>
                Tiếp tục mua sắm
              </RouterLink>
            </div>
          </div>

          <div v-if="suggestedProducts.length" class="suggest-section">
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
                    :old-price="product.oldPrice"
                    :colors="product.colors"
                    :to="product.to"
                    :product-id="product.productId"
                    :variant-id="product.variantId"
                    :cart-quantity="1"
                    :stock-quantity="product.stockQuantity ?? 0"
                />
              </div>
            </div>
          </div>
        </div>

        <CartSummary
            :item-count="selectedItemCount"
            :subtotal="subtotalValue"
            :discount="discountValue"
            :shipping="shippingValue"
            :total="totalValue"
            :selected-item-ids="selectedItemIds"
        />
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

.cart-loading {
  min-height: 280px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cart-loading-card {
  min-width: 240px;
  padding: 28px 24px;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.cart-loading-card p {
  margin: 0;
  color: #475569;
  font-size: 14px;
  font-weight: 700;
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
  min-height: 285px;
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
