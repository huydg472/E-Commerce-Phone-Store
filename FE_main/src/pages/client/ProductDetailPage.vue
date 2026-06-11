<script setup>
import ProductGallery from '@/components/product/ProductGallery.vue'
import ProductVariantSelector from '@/components/product/ProductVariantSelector.vue'
import ProductSpecificationBox from '@/components/product/ProductSpecificationBox.vue'
import ProductCard from '@/components/product/ProductCard.vue'

import {computed, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/authStore'
import {useCartStore} from '@/stores/cartStore'
import {useProductStore} from '@/stores/productStore'
import {buildColorOptions, slugifyText, toNumberPrice} from '@/utils/productCardHelpers'
import {formatCurrency} from '@/utils/formatCurrency'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()
const authStore = useAuthStore()
const cartStore = useCartStore()

const currentProduct = ref(null)
const loading = ref(false)
const notFound = ref(false)
const selectedStorage = ref('')
const selectedColor = ref('')
const activeBottomTab = ref('description')

const productSlug = computed(() => String(route.params.slug ?? ''))
const routeVariantId = computed(() => String(route.query.variant_id ?? ''))
const routeRom = computed(() => String(route.query.rom ?? ''))

const safeText = (value) => String(value ?? '').trim()

const getVariants = (product) => {
  const variants =
      product?.productVariants ??
      product?.product_variants ??
      product?.variants ??
      []

  return Array.isArray(variants) ? variants : []
}

const getVariantRom = (variant) => {
  return safeText(
      variant?.rom ??
      variant?.ROM ??
      variant?.storage ??
      variant?.storage_size ??
      variant?.capacity ??
      ''
  )
}

const getVariantColorName = (variant) => {
  if (typeof variant?.color === 'object' && variant?.color !== null) {
    return safeText(
        variant.color.name ??
        variant.color.color_name ??
        variant.color.title ??
        ''
    )
  }

  return safeText(
      variant?.color ??
      variant?.color_name ??
      variant?.colorName ??
      variant?.name_color ??
      ''
  )
}

const getVariantDisplayName = (variant) => {
  const parts = [getVariantRom(variant), getVariantColorName(variant)].filter(Boolean)
  return parts.length ? parts.join(' - ') : safeText(variant?.sku) || 'Phiên bản mặc định'
}

const getMainImage = (product) => {
  return getProductImages(product)[0] || '/images/default-product.png'
}

const normalizeKey = (value) => safeText(value).replace(/\s+/g, ' ').toLowerCase()

const getVariantImages = (variant) => {
  const images =
      variant?.productVariantImages ??
      variant?.product_variant_images ??
      variant?.images ??
      []

  return Array.isArray(images) ? images : []
}

const getImageUrl = (image) => {
  if (typeof image === 'string') {
    return image
  }

  return (
      image?.image_url ??
      image?.imageUrl ??
      image?.url ??
      image?.path ??
      image?.image ??
      image?.image_path ??
      ''
  )
}

const getProductImages = (product) => {
  const imageCandidates = [
    product?.thumbnail_url,
    product?.thumbnailUrl,
    product?.image,
    product?.image_url,
    product?.imageUrl,
  ]

  const variantImages = getVariants(product).flatMap((variant) => {
    return getVariantImages(variant).map(getImageUrl)
  })

  return [...imageCandidates, ...variantImages]
      .map((image) => safeText(image))
      .filter(Boolean)
}

const getAvailableVariants = (product) => {
  return getVariants(product).filter((variant) => isVariantInStock(variant))
}

const getVariantAvailableQuantity = (variant) => {
  return Number(
      variant?.available_quantity ??
      variant?.availableQuantity ??
      variant?.quantity ??
      0
  )
}

const getMatchingVariants = (product, storage = '', color = '') => {
  const storageKey = normalizeKey(storage)
  const colorKey = normalizeKey(color)

  return getAvailableVariants(product).filter((variant) => {
    const variantStorage = normalizeKey(getVariantRom(variant))
    const variantColor = normalizeKey(getVariantColorName(variant))

    const storageMatches = !storageKey || storageKey === variantStorage
    const colorMatches = !colorKey || colorKey === variantColor

    return storageMatches && colorMatches
  })
}

const buildStorageOptions = (product) => {
  const variants = getVariants(product)
  const storageValues = [...new Set(variants.map(getVariantRom).filter(Boolean))]
  const selectedColorKey = normalizeKey(selectedColor.value)

  return storageValues.map((storage) => {
    const matchingVariants = getMatchingVariants(product, storage, selectedColor.value)

    const available = selectedColorKey ? matchingVariants.length > 0 : getMatchingVariants(product, storage).length > 0

    return {
      value: storage,
      available,
      disabled: !available,
      quantity: matchingVariants.reduce((sum, variant) => sum + getVariantAvailableQuantity(variant), 0),
    }
  })
}

const buildColorOptionsByAvailability = (product) => {
  const variants = getVariants(product)
  const colorOptions = buildColorOptions(variants)
  const selectedStorageKey = normalizeKey(selectedStorage.value)

  return colorOptions.map((color) => {
    const matchingVariants = getMatchingVariants(product, selectedStorage.value, color.name)

    const available = selectedStorageKey ? matchingVariants.length > 0 : getMatchingVariants(product, '', color.name).length > 0

    return {
      ...color,
      available,
      disabled: !available,
      quantity: matchingVariants.reduce((sum, variant) => sum + getVariantAvailableQuantity(variant), 0),
    }
  })
}

const currentSelectedVariant = computed(() => {
  return getSelectedVariant(currentProduct.value)
})

const isVariantInStock = (variant) => {
  if (!variant) {
    return false
  }

  const status = String(variant?.status ?? '').toLowerCase()
  const quantity = Number(
      variant?.available_quantity ??
      variant?.availableQuantity ??
      variant?.quantity ??
      0
  )
  return status !== 'inactive' && status !== 'out_of_stock' && quantity > 0
}

const currentVariantInStock = computed(() => {
  return isVariantInStock(currentSelectedVariant.value)
})

const currentVariantAvailableQuantity = computed(() => {
  return getVariantAvailableQuantity(currentSelectedVariant.value)
})

const currentVariantCartQuantity = computed(() => {
  const variantId = String(currentSelectedVariant.value?.id ?? '')
  if (!variantId) {
    return 0
  }

  const items = Array.isArray(cartStore.items) ? cartStore.items : []
  return items.reduce((sum, item) => {
    const itemVariantId = String(item?.product_variant_id ?? item?.productVariant?.id ?? '')
    return itemVariantId === variantId ? sum + Number(item?.quantity ?? 0) : sum
  }, 0)
})

const currentVariantRemainingCartQuantity = computed(() => {
  return Math.max(currentVariantAvailableQuantity.value - currentVariantCartQuantity.value, 0)
})

const handleAddToCart = async ({productVariantId, quantity}) => {
  if (!productVariantId || !currentVariantInStock.value) {
    return
  }

  const nextQuantity = Math.min(
      Math.max(Number(quantity ?? 1) || 1, 1),
      currentVariantRemainingCartQuantity.value
  )

  if (nextQuantity < 1) {
    return
  }

  if (!authStore.isLoggedIn) {
    await router.push('/auth/login')
    return
  }

  try {
    await cartStore.create({
      product_variant_id: productVariantId,
      quantity: nextQuantity,
      unit_price: currentPrice.value,
    })
  } catch (error) {
    if (error.response?.status === 401) {
      await router.push('/auth/login')
      return
    }

    console.error('Không thể thêm vào giỏ hàng:', error)
  }
}

const handleBuyNow = async ({productVariantId, quantity}) => {
  if (!productVariantId || !currentVariantInStock.value) {
    return
  }

  const nextQuantity = Math.min(
      Math.max(Number(quantity ?? 1) || 1, 1),
      currentVariantAvailableQuantity.value
  )

  if (nextQuantity < 1) {
    return
  }

  if (!authStore.isLoggedIn) {
    await router.push('/auth/login')
    return
  }

  const variant = currentSelectedVariant.value

  await router.push({
    name: 'checkout',
    query: {
      direct_buy: '1',
      product_variant_id: String(productVariantId),
      quantity: String(nextQuantity),
      product_name: currentName.value,
      variant_name: getVariantDisplayName(variant),
      unit_price: String(currentPrice.value),
      image: getMainImage(currentProduct.value) || '',
      sku: variant?.sku ?? '',
    },
  })
}

const getSelectedVariant = (product) => {
  const variants = getVariants(product)

  if (!variants.length) {
    return null
  }

  const variantIdKey = routeVariantId.value
  const romKey = normalizeKey(routeRom.value)
  const storageKey = normalizeKey(selectedStorage.value)
  const colorKey = normalizeKey(selectedColor.value)

  const exactMatch = variants.find((variant) => {
    const variantStorage = normalizeKey(getVariantRom(variant))
    const variantColor = normalizeKey(getVariantColorName(variant))

    const storageMatches = !storageKey || storageKey === variantStorage
    const colorMatches = !colorKey || colorKey === variantColor

    return storageMatches && colorMatches
  })

  if (exactMatch) {
    return exactMatch
  }

  if (storageKey || colorKey) {
    return null
  }

  if (variantIdKey) {
    const byVariantId = variants.find((variant) => String(variant?.id ?? '') === variantIdKey)
    if (byVariantId) {
      return byVariantId
    }
  }

  if (romKey) {
    const byRom = variants.find((variant) => normalizeKey(getVariantRom(variant)) === romKey)
    if (byRom) {
      return byRom
    }
  }

  const storageMatch = variants.find((variant) => {
    return !storageKey || storageKey === normalizeKey(getVariantRom(variant))
  })

  return storageMatch ?? variants[0] ?? null
}

const productStorageOptions = computed(() => buildStorageOptions(currentProduct.value))
const productColorOptions = computed(() => buildColorOptionsByAvailability(currentProduct.value))

watch(
    currentProduct,
    (product) => {
      if (!product) {
        return
      }

      const routeVariant =
          product
              ? getVariants(product).find((variant) => String(variant?.id ?? '') === routeVariantId.value)
              : null
      const routeRomVariant =
          product
              ? getVariants(product).find((variant) => normalizeKey(getVariantRom(variant)) === normalizeKey(routeRom.value))
              : null
      const fallbackVariant = routeVariant ?? routeRomVariant ?? getAvailableVariants(product)[0] ?? getVariants(product)[0] ?? null
      if (!fallbackVariant) {
        return
      }

      const nextStorage = getVariantRom(fallbackVariant)
      const nextColor = getVariantColorName(fallbackVariant)

      if (!selectedStorage.value && nextStorage) {
        selectedStorage.value = nextStorage
      }

      if (!selectedColor.value && nextColor) {
        selectedColor.value = nextColor
      }
    },
    {immediate: true, deep: true}
)

const getFirstValue = (...values) => {
  return values.find((value) => safeText(value)) ?? ''
}

const getProductSpecifications = (product, selectedVariant = null) => {
  const primaryVariant = selectedVariant ?? getVariants(product)[0] ?? null

  const specs = [
    {
      label: 'Thương hiệu',
      value: getFirstValue(product?.brand?.name, product?.brand_name, product?.brandName),
    },
    {
      label: 'Danh mục',
      value: getFirstValue(product?.category?.name, product?.category_name, product?.categoryName),
    },
    {
      label: 'Dung lượng',
      value: getFirstValue(getVariantRom(primaryVariant), product?.storage, product?.capacity),
    },
    {
      label: 'Màu sắc',
      value: getFirstValue(
          primaryVariant?.color?.name,
          primaryVariant?.color_name,
          primaryVariant?.colorName,
          product?.color_name,
          product?.color
      ),
    },
    {
      label: 'RAM',
      value: getFirstValue(primaryVariant?.ram, primaryVariant?.memory, product?.ram),
    },
    {
      label: 'Chip',
      value: getFirstValue(product?.chip, product?.processor, product?.soc),
    },
    {
      label: 'Màn hình',
      value: getFirstValue(product?.screen, product?.display, product?.display_size),
    },
    {
      label: 'Camera',
      value: getFirstValue(product?.camera, product?.camera_spec, product?.rear_camera),
    },
    {
      label: 'Pin',
      value: getFirstValue(product?.battery, product?.battery_capacity),
    },
  ]

  return specs.filter((spec) => safeText(spec.value))
}

const bottomSpecifications = computed(() => {
  return getProductSpecifications(currentProduct.value, currentSelectedVariant.value)
})

const hasDescription = computed(() => {
  return Boolean(currentDescription.value && currentDescription.value !== 'Đang cập nhật mô tả sản phẩm.')
})

const getDisplayPrice = (product) => {
  const variants = getVariants(product)
  const firstVariant = variants[0] ?? null
  const selectedVariant = getSelectedVariant(product)
  const price =
      selectedVariant?.sale_price ??
      selectedVariant?.salePrice ??
      selectedVariant?.price ??
      selectedVariant?.display_price ??
      selectedVariant?.displayPrice ??
      product?.sale_price ??
      product?.salePrice ??
      product?.price ??
      product?.display_price ??
      product?.displayPrice ??
      firstVariant?.sale_price ??
      firstVariant?.salePrice ??
      firstVariant?.price ??
      firstVariant?.display_price ??
      firstVariant?.displayPrice

  return toNumberPrice(price)
}

const getDisplayOldPrice = (product) => {
  const variants = getVariants(product)
  const firstVariant = variants[0] ?? null
  const selectedVariant = getSelectedVariant(product)
  const price =
      selectedVariant?.old_price ??
      selectedVariant?.oldPrice ??
      selectedVariant?.compare_price ??
      selectedVariant?.comparePrice ??
      selectedVariant?.price ??
      product?.old_price ??
      product?.oldPrice ??
      product?.compare_price ??
      product?.comparePrice ??
      product?.price ??
      firstVariant?.old_price ??
      firstVariant?.oldPrice ??
      firstVariant?.compare_price ??
      firstVariant?.comparePrice ??
      firstVariant?.price

  return toNumberPrice(price)
}

const currentName = computed(() => safeText(currentProduct.value?.name) || 'Sản phẩm')
const currentBrandName = computed(() => {
  return getFirstValue(
      currentProduct.value?.brand?.name,
      currentProduct.value?.brand_name,
      currentProduct.value?.brandName
  ) || 'Sản phẩm'
})
const currentBrandSlug = computed(() => {
  return getFirstValue(
      currentProduct.value?.brand?.slug,
      currentProduct.value?.brand_slug,
      currentProduct.value?.brandSlug,
      slugifyText(currentBrandName.value)
  )
})
const brandFilterLink = computed(() => {
  const slug = currentBrandSlug.value
  return slug ? `/products?brand=${encodeURIComponent(slug)}` : '/products'
})

const currentPrice = computed(() => getDisplayPrice(currentProduct.value))
const currentOldPrice = computed(() => getDisplayOldPrice(currentProduct.value))
const hasOldPrice = computed(() => currentOldPrice.value > currentPrice.value)
const discountPercent = computed(() => {
  if (!hasOldPrice.value || !currentOldPrice.value) {
    return ''
  }

  const discount = Math.round(((currentOldPrice.value - currentPrice.value) / currentOldPrice.value) * 100)
  return discount > 0 ? `${discount}%` : ''
})

const currentDescription = computed(() => {
  return safeText(
      currentProduct.value?.description ??
      currentProduct.value?.short_description ??
      currentProduct.value?.shortDescription ??
      currentProduct.value?.content
  ) || 'Đang cập nhật mô tả sản phẩm.'
})

const loadProduct = async () => {
  const slug = productSlug.value

  if (!slug) {
    currentProduct.value = null
    notFound.value = true
    return
  }

  loading.value = true
  notFound.value = false

  try {
    try {
      await productStore.fetchBySlug(slug)
    } catch (error) {
      if (!/^\d+$/.test(slug)) {
        throw error
      }

      await productStore.fetchById(slug)
    }

    currentProduct.value = productStore.item ?? null

    if (!currentProduct.value) {
      notFound.value = true
      return
    }

    if (String(currentProduct.value?.status ?? '').toLowerCase() !== 'active') {
      currentProduct.value = null
      notFound.value = true
      return
    }

    const variants = getVariants(currentProduct.value)
    const selectedVariant =
        variants.find((variant) => String(variant?.id ?? '') === routeVariantId.value && isVariantInStock(variant)) ??
        variants.find((variant) => normalizeKey(getVariantRom(variant)) === normalizeKey(routeRom.value) && isVariantInStock(variant)) ??
        variants.find((variant) => isVariantInStock(variant)) ??
        variants[0] ??
        null

    selectedStorage.value = getVariantRom(selectedVariant)
    selectedColor.value = getVariantColorName(selectedVariant)
    activeBottomTab.value = 'description'

    if (authStore.isLoggedIn) {
      await cartStore.fetchAll().catch(() => {})
    }

    console.log('Product detail:', currentProduct.value)
    console.log('Product ID:', currentProduct.value.id)
  } catch (error) {
    currentProduct.value = null
    notFound.value = true
    console.error('KhÃ´ng thá»ƒ táº£i chi tiáº¿t sáº£n pháº©m:', error)
  } finally {
    loading.value = false
  }
}

watch(productSlug, loadProduct, {immediate: true})

const relatedProducts = [
  {
    id: 1,
    slug: 'iphone-15-pro-256gb',
    name: 'iPhone 15 Pro 256GB',
    image: '/images/products/iphone-15-pro-max.png',
    price: '28.990.000đ',
  },
  {
    id: 2,
    slug: 'iphone-14-pro-max-256gb',
    name: 'iPhone 14 Pro Max 256GB',
    image: '/images/products/iphone-14.png',
    price: '37.490.000đ',
  },
  {
    id: 3,
    slug: 'samsung-galaxy-s24-ultra-256gb',
    name: 'Samsung Galaxy S24 Ultra 256GB',
    image: '/images/products/samsung-galaxy-s24-ultra.png',
    price: '25.990.000đ',
  },
  {
    id: 4,
    slug: 'oppo-find-x7-ultra-5g-256gb',
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
              :images="getProductImages(currentProduct)"
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

            <div class="stock-status" :class="{ 'is-out': !currentVariantInStock }">
              <i class="bi" :class="currentVariantInStock ? 'bi-check-lg' : 'bi-x-circle'"></i>
              {{ currentVariantInStock ? 'Còn hàng' : 'Hết hàng' }}
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
  color: #16a34a;
  display: inline-flex;
  align-items: center;
  gap: 7px;
  font-size: 14px;
  font-weight: 800;
  margin-bottom: 2px;
}

.stock-status.is-out {
  color: #ef4444;
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
