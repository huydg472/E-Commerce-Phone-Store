const colorNameMap = {
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
  return String(value ?? '').replace(/\s+/g, '').toLowerCase()
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

export const buildColorOptions = (variants) => {
  const colorMap = new Map()

  variants.forEach((variant) => {
    const name = getVariantColorName(variant)

    if (!name) {
      return
    }

    const key = normalizeText(name)

    if (!colorMap.has(key)) {
      colorMap.set(key, {
        name,
        value: getVariantColorValue(variant),
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
  return [...variants].sort((a, b) => {
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

const createRomProductCard = (product, rom, variants, placeholderImage = '') => {
  const bestVariant = getBestVariantByRom(variants)
  const price = getVariantPrice(bestVariant) || getProductFallbackPrice(product)
  const oldPrice = getVariantOldPrice(bestVariant) || getProductFallbackOldPrice(product)

  return {
    id: `${product?.id}-${normalizeText(rom) || bestVariant?.id || 'default'}`,
    productId: product?.id,
    variantId: bestVariant?.id,
    product,
    variant: bestVariant,
    rom,
    colors: buildColorOptions(variants),
    brandId: getProductBrandId(product),
    brandSlug: getProductBrandSlug(product),
    brandName: getProductBrandName(product),
    name: buildCardName(product, rom),
    image:
        getVariantImage(bestVariant) ||
        product?.thumbnail_url ||
        product?.thumbnailUrl ||
        product?.image ||
        placeholderImage ||
        '',
    price,
    oldPrice: oldPrice > price ? oldPrice : null,
    isFeatured: Boolean(
        product?.is_featured ||
        product?.isFeatured ||
        bestVariant?.is_featured ||
        bestVariant?.isFeatured
    ),
    to: buildProductLink(product, rom, bestVariant),
  }
}

const groupProductByRom = (product, placeholderImage = '') => {
  const variants = getProductVariants(product)

  if (!variants.length) {
    return [createRomProductCard(product, '', [], placeholderImage)]
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
    return createRomProductCard(product, group.rom, group.variants, placeholderImage)
  })
}

export const buildProductCards = (products, placeholderImage = '') => {
  const list = Array.isArray(products) ? products : []
  return list.flatMap((product) => groupProductByRom(product, placeholderImage))
}
