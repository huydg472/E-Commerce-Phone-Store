import {
    buildProductLink,
    getBestVariantByRom,
    getProductBrandId,
    getProductBrandName,
    getProductBrandSlug,
    getProductCategoryId,
    getProductCategoryName,
    getProductCategorySlug,
    getProductFallbackOldPrice,
    getProductFallbackPrice,
    getProductVariants,
    getStorageWeight,
    getVariantAvailableQuantity,
    getVariantColorName,
    getVariantColorValue,
    getVariantImage,
    getVariantOldPrice,
    getVariantPrice,
    getVariantRom,
    getVisibleProductVariants,
    isActiveStatus,
    normalizeText,
} from './productCardUtils.js'

const getLowestStorageVariantByColor = (variants, colorName) => {
    const colorKey = normalizeText(colorName)
    const sameColorVariants = variants.filter((variant) => {
        return (
            isActiveStatus(variant?.status) &&
            getVariantAvailableQuantity(variant) > 0 &&
            normalizeText(getVariantColorName(variant)) === colorKey
        )
    })

    return [...sameColorVariants].sort((left, right) => {
        const storageDiff = getStorageWeight(getVariantRom(left)) - getStorageWeight(getVariantRom(right))

        if (storageDiff !== 0) {
            return storageDiff
        }

        return Number(left?.id ?? 0) - Number(right?.id ?? 0)
    })[0] ?? null
}

export const buildColorOptions = (variants, options = {}) => {
    const colorMap = new Map()
    const product = options.product ?? null
    const targetVariants = Array.isArray(options.targetVariants) ? options.targetVariants : variants

    variants.forEach((variant) => {
        const name = getVariantColorName(variant)

        if (!name) {
            return
        }

        const key = normalizeText(name)

        if (!colorMap.has(key)) {
            const targetVariant = getLowestStorageVariantByColor(targetVariants, name) ?? variant
            const targetRom = getVariantRom(targetVariant)

            colorMap.set(key, {
                name,
                value: getVariantColorValue(variant),
                variantId: targetVariant?.id ?? null,
                rom: targetRom,
                to: product ? buildProductLink(product, targetRom) : '',
            })
        }
    })

    return Array.from(colorMap.values())
}

const buildCardName = (product, rom) => {
    const productName = String(product?.name ?? '').trim()
    const romText = String(rom ?? '').trim()

    if (!romText) {
        return productName
    }

    if (normalizeText(productName).includes(normalizeText(romText))) {
        return productName
    }

    return `${productName} ${romText}`
}

const createRomProductCard = (product, rom, variants, placeholderImage = '', allVariants = variants) => {
    const bestVariant = getBestVariantByRom(variants)
    const price = getVariantPrice(bestVariant) || getProductFallbackPrice(product)
    const oldPrice = getVariantOldPrice(bestVariant) || getProductFallbackOldPrice(product)
    const stockQuantity = getVariantAvailableQuantity(bestVariant) || Number(product?.quantity ?? 0)
    const isFeatured = Boolean(bestVariant?.is_featured)

    return {
        id: `${product?.id}-${normalizeText(rom) || bestVariant?.id || 'default'}`,
        productId: product?.id,
        variantId: bestVariant?.id,
        product,
        variant: bestVariant,
        rom,
        colors: buildColorOptions(variants, {
            product,
            targetVariants: allVariants,
        }),
        brandId: getProductBrandId(product),
        brandSlug: getProductBrandSlug(product),
        brandName: getProductBrandName(product),
        categoryId: getProductCategoryId(product),
        categorySlug: getProductCategorySlug(product),
        categoryName: getProductCategoryName(product),
        name: buildCardName(product, rom),
        image:
            product?.thumbnail_url ||
            product?.thumbnailUrl ||
            product?.image ||
            getVariantImage(bestVariant) ||
            placeholderImage ||
            '',
        price,
        oldPrice: oldPrice > price ? oldPrice : null,
        isFeatured,
        stockQuantity,
        to: buildProductLink(product, rom),
    }
}

const groupProductByRom = (product, placeholderImage = '') => {
    const allVariants = getProductVariants(product)
    const variants = getVisibleProductVariants(product)

    if (!allVariants.length) {
        return [createRomProductCard(product, '', [], placeholderImage)]
    }

    if (!variants.length) {
        return []
    }

    const romGroups = new Map()

    variants.forEach((variant) => {
        const rom = getVariantRom(variant)
        const key = normalizeText(rom) || `variant-${variant?.id}`

        if (!romGroups.has(key)) {
            romGroups.set(key, {
                rom,
                variants: [],
            })
        }

        romGroups.get(key).variants.push(variant)
    })

    return Array.from(romGroups.values()).map((group) => {
        return createRomProductCard(product, group.rom, group.variants, placeholderImage, variants)
    })
}

export const buildProductCards = (products, placeholderImage = '') => {
    const list = Array.isArray(products) ? products : []
    return list
        .filter((product) => isActiveStatus(product?.status))
        .flatMap((product) => groupProductByRom(product, placeholderImage))
}
