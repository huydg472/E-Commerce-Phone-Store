const colorNameMap = {
    'black titanium': '#24211f',
    'white titanium': '#f8fafc',
    'natural titanium': '#b9b3a9',
    'blue titanium': '#0f1d2e',
    'space black': '#111827',
    'phantom black': '#111827',
    'asteroid black': '#171717',
    'crystal black': '#111827',
    'starlight black': '#1f2937',
    'pearl white': '#f8fafc',
    cream: '#f5f0df',
    lavender: '#c4b5fd',
    'light violet': '#d8b4fe',
    'star grey': '#6b7280',
    'star gray': '#6b7280',
    'astro silver': '#d1d5db',
    'fluid silver': '#cbd5e1',
    'stellar silver': '#d1d5db',
    'matte brown': '#8b5e3c',
    'sunset pink': '#f472b6',
    'sunset orange': '#fb923c',
    'startrail blue': '#2563eb',
    'nebula purple': '#8b5cf6',
    'starlight purple': '#8b5cf6',
    'razor green': '#16a34a',
    đen: '#111827',
    black: '#111827',
    trắng: '#f8fafc',
    white: '#f8fafc',
    xanh: '#2563eb',
    blue: '#2563eb',
    'xanh dương': '#2563eb',
    'xanh lá': '#16a34a',
    green: '#16a34a',
    đỏ: '#dc2626',
    red: '#dc2626',
    hồng: '#f472b6',
    pink: '#f472b6',
    vàng: '#facc15',
    yellow: '#facc15',
    tím: '#8b5cf6',
    purple: '#8b5cf6',
    bạc: '#cbd5e1',
    silver: '#cbd5e1',
    xám: '#6b7280',
    gray: '#6b7280',
    grey: '#6b7280',
    titan: '#9ca3af',
    titanium: '#9ca3af',
    gold: '#d4af37',
}

export const normalizeText = (value) => {
    return String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, '')
        .toLowerCase()
}

const isActiveStatus = (value) => {
    return String(value ?? '').trim().toLowerCase() === 'active'
}

export const slugifyText = (value) => {
    return String(value ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

const colorNameEntries = Object.entries(colorNameMap)
    .map(([name, value]) => ({
        name,
        normalizedName: normalizeText(name),
        value,
    }))
    .sort((a, b) => b.normalizedName.length - a.normalizedName.length)

export const toNumberPrice = (value) => {
    if (value === null || value === undefined || value === '') {
        return 0
    }

    if (typeof value === 'number') {
        return Math.round(value)
    }

    const raw = String(value).trim().replace(/[^\d.,-]/g, '')

    if (!raw) {
        return 0
    }

    const hasDot = raw.includes('.')
    const hasComma = raw.includes(',')

    if (hasDot && hasComma) {
        const lastDot = raw.lastIndexOf('.')
        const lastComma = raw.lastIndexOf(',')
        const decimalSeparator = lastDot > lastComma ? '.' : ','
        const thousandSeparator = decimalSeparator === '.' ? ',' : '.'
        const normalized = raw
            .replace(new RegExp(`\\${thousandSeparator}`, 'g'), '')
            .replace(decimalSeparator, '.')

        return Math.round(Number.parseFloat(normalized) || 0)
    }

    if (hasDot) {
        const parts = raw.split('.')

        if (parts.length === 2 && parts[1].length <= 2) {
            return Math.round(Number.parseFloat(raw) || 0)
        }

        return Number(raw.replace(/\./g, '') || 0)
    }

    if (hasComma) {
        const parts = raw.split(',')

        if (parts.length === 2 && parts[1].length <= 2) {
            return Math.round(Number.parseFloat(raw.replace(',', '.')) || 0)
        }

        return Number(raw.replace(/,/g, '') || 0)
    }

    return Number(raw || 0)
}

const getProductVariants = (product) => {
    const variants =
        product?.productVariants ??
        product?.product_variants ??
        product?.variants ??
        []

    return Array.isArray(variants) ? variants : []
}

const getVisibleProductVariants = (product) => {
    return getProductVariants(product).filter((variant) => isActiveStatus(variant?.status))
}

const getVariantRom = (variant) => {
    return String(
        variant?.rom ??
        variant?.ROM ??
        variant?.storage ??
        variant?.storage_size ??
        variant?.capacity ??
        ''
    ).trim()
}

const getStorageWeight = (value) => {
    const text = String(value ?? '').trim().toLowerCase()
    const number = Number.parseFloat(text.replace(',', '.')) || 0

    if (text.includes('tb')) {
        return number * 1024
    }

    return number
}

const getVariantColorName = (variant) => {
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

const getVariantColorValue = (variant) => {
    const colorObject = typeof variant?.color === 'object' ? variant.color : null

    const colorCode =
        colorObject?.code ??
        colorObject?.hex ??
        colorObject?.hex_code ??
        colorObject?.color_code ??
        variant?.color_code ??
        variant?.colorCode ??
        variant?.hex_code ??
        variant?.hexCode ??
        variant?.color_hex ??
        variant?.colorHex

    if (colorCode) {
        return String(colorCode).trim()
    }

    const colorName = getVariantColorName(variant)
    const normalizedColor = normalizeText(colorName)
    const matchedColor = colorNameEntries.find((color) => {
        return normalizedColor.includes(color.normalizedName)
    })

    return matchedColor?.value ?? '#e5e7eb'
}

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
                to: product ? buildProductLink(product, targetRom, targetVariant) : '',
            })
        }
    })

    return Array.from(colorMap.values())
}

const getVariantPrice = (variant) => {
    return toNumberPrice(
        variant?.sale_price ??
        variant?.salePrice ??
        variant?.price ??
        variant?.display_price ??
        variant?.displayPrice
    )
}

const getVariantAvailableQuantity = (variant) => {
    return Number(
        variant?.available_quantity ??
        variant?.availableQuantity ??
        variant?.quantity ??
        0
    )
}

const getVariantOldPrice = (variant) => {
    return toNumberPrice(
        variant?.old_price ??
        variant?.oldPrice ??
        variant?.compare_price ??
        variant?.comparePrice ??
        variant?.price
    )
}

const getProductFallbackPrice = (product) => {
    return toNumberPrice(
        product?.display_price ??
        product?.sale_price ??
        product?.price
    )
}

const getProductFallbackOldPrice = (product) => {
    return toNumberPrice(
        product?.display_old_price ??
        product?.old_price ??
        product?.oldPrice
    )
}

const getVariantImages = (variant) => {
    const images =
        variant?.productVariantImages ??
        variant?.product_variant_images ??
        variant?.images ??
        []

    return Array.isArray(images) ? images : []
}

const getVariantImage = (variant) => {
    const firstImage = getVariantImages(variant)[0]

    return (
        firstImage?.image_url ??
        firstImage?.imageUrl ??
        firstImage?.url ??
        firstImage?.path ??
        firstImage?.image ??
        firstImage?.image_path ??
        variant?.thumbnail_url ??
        variant?.thumbnailUrl ??
        variant?.image ??
        ''
    )
}

const getBestVariantByRom = (variants) => {
    const availableVariants = variants.filter((variant) => {
        return isActiveStatus(variant?.status) && getVariantAvailableQuantity(variant) > 0
    })
    const source = availableVariants.length ? availableVariants : variants
    const featuredSource = source.filter((variant) => Boolean(variant?.is_featured))
    const rankedSource = featuredSource.length ? featuredSource : source

    return [...rankedSource].sort((a, b) => {
        return getVariantPrice(a) - getVariantPrice(b)
    })[0] ?? null
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

const buildProductLink = (product, rom, variant) => {
    const productSlug =
        product?.slug ||
        product?.product_slug ||
        slugifyText(product?.name) ||
        product?.code ||
        product?.id
    const variantId = variant?.id
    const romQuery = rom ? `rom=${encodeURIComponent(rom)}` : ''
    const variantQuery = variantId ? `variant_id=${variantId}` : ''
    const query = [romQuery, variantQuery].filter(Boolean).join('&')

    return query ? `/products/${productSlug}?${query}` : `/products/${productSlug}`
}

const getProductBrandId = (product) => {
    return String(product?.brand?.id ?? product?.brand_id ?? '')
}

const getProductBrandSlug = (product) => {
    return String(product?.brand?.slug ?? '')
}

const getProductBrandName = (product) => {
    return String(product?.brand?.name ?? '')
}

const getProductCategoryId = (product) => {
    return String(product?.category?.id ?? product?.category_id ?? '')
}

const getProductCategorySlug = (product) => {
    return String(product?.category?.slug ?? product?.category_slug ?? '')
}

const getProductCategoryName = (product) => {
    return String(product?.category?.name ?? product?.category_name ?? '')
}

const createRomProductCard = (product, rom, variants, placeholderImage = '', allVariants = variants) => {
    const bestVariant = getBestVariantByRom(variants)
    const price = getVariantPrice(bestVariant) || getProductFallbackPrice(product)
    const oldPrice = getVariantOldPrice(bestVariant) || getProductFallbackOldPrice(product)
    const stockQuantity = getVariantAvailableQuantity(bestVariant) || Number(product?.quantity ?? 0)
    const isFeatured = variants.some((variant) => Boolean(variant?.is_featured))

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
        to: buildProductLink(product, rom, bestVariant),
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
