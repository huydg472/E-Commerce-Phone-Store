import {computed, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/authStore'
import {useCartStore} from '@/stores/cartStore'
import {useFavoriteStore} from '@/stores/favoriteStore'
import {useProductStore} from '@/stores/productStore'
import {buildColorOptions, slugifyText, toNumberPrice} from '@/utils/productCardHelpers'
import {formatCurrency} from '@/utils/formatCurrency'

export function useProductDetailPage() {
    const route = useRoute()
    const router = useRouter()
    const productStore = useProductStore()
    const authStore = useAuthStore()
    const cartStore = useCartStore()
    const favoriteStore = useFavoriteStore()

    const currentProduct = ref(null)
    const loading = ref(false)
    const notFound = ref(false)
    const selectedStorage = ref('')
    const selectedColor = ref('')
    const activeBottomTab = ref('description')
    const favoriteLoading = ref(false)

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

    const getVariantRom = (variant) => safeText(variant?.rom ?? variant?.ROM ?? variant?.storage ?? variant?.storage_size ?? variant?.capacity ?? '')

    const getVariantColorName = (variant) => {
        if (typeof variant?.color === 'object' && variant?.color !== null) {
            return safeText(variant.color.name ?? variant.color.color_name ?? variant.color.title ?? '')
        }

        return safeText(variant?.color ?? variant?.color_name ?? variant?.colorName ?? variant?.name_color ?? '')
    }

    const getVariantDisplayName = (variant) => {
        const parts = [getVariantRom(variant), getVariantColorName(variant)].filter(Boolean)
        return parts.length ? parts.join(' - ') : safeText(variant?.sku) || 'Phiên bản mặc định'
    }

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

    const sortVariantImages = (images) => {
        return [...images].sort((left, right) => {
            const leftOrder = Number(left?.sort_order ?? left?.sortOrder ?? 0)
            const rightOrder = Number(right?.sort_order ?? right?.sortOrder ?? 0)

            if (leftOrder !== rightOrder) {
                return leftOrder - rightOrder
            }

            return Number(left?.id ?? 0) - Number(right?.id ?? 0)
        })
    }

    const uniqueImages = (images) => {
        return [...new Set(images.map((image) => safeText(image)).filter(Boolean))]
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

        return [...imageCandidates, ...variantImages].map((image) => safeText(image)).filter(Boolean)
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

    const getDetailImages = (product) => {
        const selectedVariant = getSelectedVariant(product)
        const variants = getVariants(product)
        const selectedVariantId = String(selectedVariant?.id ?? '')

        const selectedImages = selectedVariant
            ? sortVariantImages(getVariantImages(selectedVariant)).map(getImageUrl)
            : []

        const otherImages = variants.flatMap((variant) => {
            if (selectedVariantId && String(variant?.id ?? '') === selectedVariantId) {
                return []
            }

            return sortVariantImages(getVariantImages(variant)).map(getImageUrl)
        })

        const uniqueVariantImages = uniqueImages([...selectedImages, ...otherImages])

        if (uniqueVariantImages.length) {
            return uniqueVariantImages
        }

        return getProductImages(product)
    }

    const getAvailableVariants = (product) => {
        return getVariants(product).filter((variant) => isVariantInStock(variant))
    }

    const getVariantAvailableQuantity = (variant) => {
        return Number(variant?.available_quantity ?? variant?.availableQuantity ?? variant?.quantity ?? 0)
    }

    const normalizeKey = (value) => safeText(value).replace(/\s+/g, ' ').toLowerCase()

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

    const currentSelectedVariant = computed(() => getSelectedVariant(currentProduct.value))

    const isVariantInStock = (variant) => {
        if (!variant) {
            return false
        }

        const status = String(variant?.status ?? '').toLowerCase()
        const quantity = Number(variant?.available_quantity ?? variant?.availableQuantity ?? variant?.quantity ?? 0)
        return status !== 'inactive' && status !== 'out_of_stock' && quantity > 0
    }

    const currentVariantInStock = computed(() => isVariantInStock(currentSelectedVariant.value))
    const currentVariantAvailableQuantity = computed(() => getVariantAvailableQuantity(currentSelectedVariant.value))
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

    const currentVariantRemainingCartQuantity = computed(() => Math.max(currentVariantAvailableQuantity.value - currentVariantCartQuantity.value, 0))

    const handleAddToCart = async ({productVariantId, quantity}) => {
        if (!productVariantId || !currentVariantInStock.value) {
            return
        }

        const nextQuantity = Math.min(Math.max(Number(quantity ?? 1) || 1, 1), currentVariantRemainingCartQuantity.value)

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

        const nextQuantity = Math.min(Math.max(Number(quantity ?? 1) || 1, 1), currentVariantAvailableQuantity.value)

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

    const applyRouteVariantSelection = () => {
        const product = currentProduct.value

        if (!product) {
            return
        }

        const variants = getVariants(product)
        const routeVariant =
            variants.find((variant) => String(variant?.id ?? '') === routeVariantId.value && isVariantInStock(variant)) ?? null
        const routeRomVariant =
            variants.find((variant) => normalizeKey(getVariantRom(variant)) === normalizeKey(routeRom.value) && isVariantInStock(variant)) ?? null
        const selectedVariant = routeVariant ?? routeRomVariant

        if (!selectedVariant) {
            return
        }

        selectedStorage.value = getVariantRom(selectedVariant)
        selectedColor.value = getVariantColorName(selectedVariant)
    }

    watch(
        currentProduct,
        (product) => {
            if (!product) {
                return
            }

            const routeVariant = product ? getVariants(product).find((variant) => String(variant?.id ?? '') === routeVariantId.value) : null
            const routeRomVariant = product ? getVariants(product).find((variant) => normalizeKey(getVariantRom(variant)) === normalizeKey(routeRom.value)) : null
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

    watch([routeVariantId, routeRom], applyRouteVariantSelection)

    const getFirstValue = (...values) => values.find((value) => safeText(value)) ?? ''

    const getProductSpecifications = (product, selectedVariant = null) => {
        const primaryVariant = selectedVariant ?? getVariants(product)[0] ?? null
        const defaultSpecs = [
            {label: 'Thương hiệu', value: getFirstValue(product?.brand?.name, product?.brand_name, product?.brandName)},
            {label: 'Danh mục', value: getFirstValue(product?.category?.name, product?.category_name, product?.categoryName)},
            {label: 'Dung lượng', value: getFirstValue(getVariantRom(primaryVariant), product?.storage, product?.capacity)},
            {
                label: 'Màu sắc',
                value: getFirstValue(primaryVariant?.color?.name, primaryVariant?.color_name, primaryVariant?.colorName, product?.color_name, product?.color),
            },
            {label: 'RAM', value: getFirstValue(primaryVariant?.ram, primaryVariant?.memory, product?.ram)},
            {label: 'Chip', value: getFirstValue(product?.chip, product?.processor, product?.soc)},
            {label: 'Màn hình', value: getFirstValue(product?.screen, product?.display, product?.display_size)},
            {label: 'Camera', value: getFirstValue(product?.camera, product?.camera_spec, product?.rear_camera)},
            {label: 'Pin', value: getFirstValue(product?.battery, product?.battery_capacity)},
        ]

        const specifications = [
            ...(Array.isArray(product?.productSpecifications) ? product.productSpecifications : []),
            ...(Array.isArray(product?.product_specifications) ? product.product_specifications : []),
        ]

        if (specifications.length) {
            const extraSpecs = specifications
                .map((spec) => ({
                    label: safeText(spec?.spec_name ?? spec?.name ?? ''),
                    value: safeText(spec?.spec_value ?? spec?.value ?? ''),
                    sortOrder: Number(spec?.sort_order ?? spec?.sortOrder ?? 0),
                }))
                .filter((spec) => Boolean(spec.label) && Boolean(spec.value))
                .sort((left, right) => left.sortOrder - right.sortOrder)
                .map(({label, value}) => ({label, value}))

            const mergedSpecs = [...defaultSpecs]

            extraSpecs.forEach((spec) => {
                const normalizedLabel = normalizeKey(spec.label)
                const existingIndex = mergedSpecs.findIndex((item) => normalizeKey(item.label) === normalizedLabel)

                if (existingIndex >= 0) {
                    mergedSpecs[existingIndex] = spec
                    return
                }

                mergedSpecs.push(spec)
            })

            return mergedSpecs.filter((spec) => safeText(spec.value))
        }

        return defaultSpecs.filter((spec) => safeText(spec.value))
    }

    const bottomSpecifications = computed(() => getProductSpecifications(currentProduct.value, currentSelectedVariant.value))

    const hasDescription = computed(() => Boolean(currentDescription.value && currentDescription.value !== 'Đang cập nhật mô tả sản phẩm.'))

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
    const currentBrandName = computed(() => getFirstValue(currentProduct.value?.brand?.name, currentProduct.value?.brand_name, currentProduct.value?.brandName) || 'Sản phẩm')
    const currentBrandSlug = computed(() => getFirstValue(currentProduct.value?.brand?.slug, currentProduct.value?.brand_slug, currentProduct.value?.brandSlug, slugifyText(currentBrandName.value)))
    const brandFilterLink = computed(() => {
        const slug = currentBrandSlug.value
        return slug ? `/products?brand=${encodeURIComponent(slug)}` : '/products'
    })

    const currentPrice = computed(() => getDisplayPrice(currentProduct.value))
    const currentOldPrice = computed(() => getDisplayOldPrice(currentProduct.value))
    const currentProductId = computed(() => Number(currentProduct.value?.id ?? 0))
    const currentProductVariantId = computed(() => Number(currentSelectedVariant.value?.id ?? 0))
    const currentProductFavorite = computed(() => authStore.isLoggedIn && favoriteStore.isFavorite(currentProductVariantId.value))
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
                await favoriteStore.ensureLoaded().catch(() => {})
            }
        } catch (error) {
            currentProduct.value = null
            notFound.value = true
            console.error('Không thể tải chi tiết sản phẩm:', error)
        } finally {
            loading.value = false
        }
    }

    watch(productSlug, loadProduct, {immediate: true})

    const handleFavoriteToggle = async () => {
        if (!currentProductVariantId.value || favoriteLoading.value) {
            return
        }

        if (!authStore.isLoggedIn) {
            await router.push('/auth/login')
            return
        }

        favoriteLoading.value = true

        try {
            await favoriteStore.ensureLoaded()
            await favoriteStore.toggle(currentProductVariantId.value)
        } catch (error) {
            console.error('Không thể cập nhật yêu thích:', error)
        } finally {
            favoriteLoading.value = false
        }
    }

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
            slug: 'iphone-13-pro-max-128gb',
            name: 'iPhone 13 Pro Max 128GB',
            image: '/images/products/iphone-13-pro-max.png',
            price: '23.990.000đ',
        },
    ]

    const getMainImage = (product) => getDetailImages(product)[0] || '/images/default-product.png'

    return {
        route,
        router,
        currentProduct,
        loading,
        notFound,
        selectedStorage,
        selectedColor,
        activeBottomTab,
        favoriteLoading,
        productSlug,
        routeVariantId,
        routeRom,
        safeText,
        getVariants,
        getVariantRom,
        getVariantColorName,
        getVariantDisplayName,
        getMainImage,
        normalizeKey,
        getVariantImages,
        getImageUrl,
        sortVariantImages,
        uniqueImages,
        getProductImages,
        getDetailImages,
        getAvailableVariants,
        getVariantAvailableQuantity,
        getMatchingVariants,
        buildStorageOptions,
        buildColorOptionsByAvailability,
        currentSelectedVariant,
        isVariantInStock,
        currentVariantInStock,
        currentVariantAvailableQuantity,
        currentVariantCartQuantity,
        currentVariantRemainingCartQuantity,
        handleAddToCart,
        handleBuyNow,
        getSelectedVariant,
        productStorageOptions: computed(() => buildStorageOptions(currentProduct.value)),
        productColorOptions: computed(() => buildColorOptionsByAvailability(currentProduct.value)),
        applyRouteVariantSelection,
        getFirstValue,
        getProductSpecifications,
        bottomSpecifications,
        hasDescription,
        getDisplayPrice,
        getDisplayOldPrice,
        currentName,
        currentBrandName,
        currentBrandSlug,
        brandFilterLink,
        currentPrice,
        currentOldPrice,
        currentProductId,
        currentProductVariantId,
        currentProductFavorite,
        hasOldPrice,
        discountPercent,
        currentDescription,
        loadProduct,
        handleFavoriteToggle,
        relatedProducts,
        formatCurrency,
    }
}
