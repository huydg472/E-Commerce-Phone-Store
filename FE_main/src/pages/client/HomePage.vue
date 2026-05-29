<script setup>
import ServicePolicyBar from '@/components/common/ServicePolicyBar.vue'
import ProductCard from '@/components/product/ProductCard.vue'
import {computed, onMounted, ref, watch} from 'vue'
import {storeToRefs} from 'pinia'
import {useProductStore} from '@/stores/productStore.js'
import {useBrandStore} from '@/stores/brandStore.js'
import {buildProductCards, normalizeText} from '@/utils/productCardHelpers.js'

const productStore = useProductStore()
const brandStore = useBrandStore()

const {items: products, loading: productLoading} = storeToRefs(productStore)
const {items: brands} = storeToRefs(brandStore)

const placeholder = {
  hero: 'https://placehold.co/1800x420/e8f0fb/2563eb?text=Hero+Banner',
  product: 'https://placehold.co/300x180/f1f5f9/2563eb?text=Phone',
  accessories: 'https://placehold.co/900x180/e8f0fb/2563eb?text=Accessories',
  tradeIn: 'https://placehold.co/900x180/f1ecfb/2563eb?text=Trade+In',
}

const selectedBrand = ref('')
const brandPriority = ['Apple', 'Samsung', 'OPPO', 'Xiaomi', 'Vivo', 'Realme']

const productList = computed(() => Array.isArray(products.value) ? products.value : [])
const brandList = computed(() => {
  const list = Array.isArray(brands.value) ? brands.value : []

  return [...list].sort((left, right) => {
    const leftIndex = brandPriority.indexOf(String(left?.name ?? '').trim())
    const rightIndex = brandPriority.indexOf(String(right?.name ?? '').trim())

    const safeLeftIndex = leftIndex === -1 ? Number.POSITIVE_INFINITY : leftIndex
    const safeRightIndex = rightIndex === -1 ? Number.POSITIVE_INFINITY : rightIndex

    if (safeLeftIndex !== safeRightIndex) {
      return safeLeftIndex - safeRightIndex
    }

    return String(left?.name ?? '').localeCompare(String(right?.name ?? ''), 'vi', {sensitivity: 'base'})
  })
})

const brandTabs = computed(() => brandList.value.slice(0, 6))
const productCards = computed(() => buildProductCards(productList.value, placeholder.product))

const getBrandTabValue = (brand) => {
  return String(brand?.slug ?? brand?.name ?? brand?.id ?? '')
}

const matchesBrand = (productCard) => {
  if (!selectedBrand.value) {
    return true
  }

  const brandValue = normalizeText(selectedBrand.value)
  return [
    productCard.brandSlug,
    productCard.brandName,
    productCard.brandId,
  ].some((value) => normalizeText(value) === brandValue)
}

const featuredProducts = computed(() => {
  const featured = productCards.value.filter((product) => product.isFeatured)
  return (featured.length ? featured : productCards.value).slice(0, 5)
})

const brandProducts = computed(() => {
  return productCards.value
      .filter(matchesBrand)
      .slice(0, 8)
})

watch(
    brandList,
    (list) => {
      if (!selectedBrand.value && list.length) {
        selectedBrand.value = getBrandTabValue(list[0])
      }
    },
    {immediate: true}
)

onMounted(() => {
  productStore.fetchAll()
  brandStore.fetchAll()
})
</script>

<template>
  <div class="home-page">
    <section class="container-fluid px-4">
      <div class="hero-banner">
        <img :src="placeholder.hero" alt="Hero banner"/>
      </div>
    </section>

    <ServicePolicyBar/>

    <section class="container-fluid px-4 featured-section">
      <div class="section-heading">
        <h2>Sản phẩm nổi bật</h2>

        <RouterLink to="/san-pham">
          Xem tất cả
          <i class="bi bi-chevron-right"></i>
        </RouterLink>
      </div>

      <div class="product-slider-wrap">
        <button class="product-nav product-nav-left" type="button">
          <i class="bi bi-chevron-left"></i>
        </button>

        <div class="product-grid">
          <div v-if="productLoading" class="loading-state">
            Đang tải sản phẩm...
          </div>

          <ProductCard
              v-for="product in featuredProducts"
              :key="product.id"
              :image="product.image"
              :name="product.name"
              :colors="product.colors"
              :price="product.price"
              :old-price="product.oldPrice || ''"
              :to="product.to"
              :product-id="product.productId"
              :variant-id="product.variantId"
              :cart-quantity="1"
              :stock-quantity="product.stockQuantity"
          />
        </div>

        <button class="product-nav product-nav-right" type="button">
          <i class="bi bi-chevron-right"></i>
        </button>
      </div>
    </section>

    <section class="container-fluid px-4 promo-section">
      <div class="promo-grid">
        <RouterLink to="/phu-kien" class="promo-placeholder-card">
          <img :src="placeholder.accessories" alt="Accessories placeholder"/>
        </RouterLink>

        <RouterLink to="/thu-cu-doi-moi" class="promo-placeholder-card">
          <img :src="placeholder.tradeIn" alt="Trade in placeholder"/>
        </RouterLink>
      </div>
    </section>

    <section class="container-fluid px-4 brand-products-section">
      <div class="section-heading brand-heading">
        <div>
          <h2>Sản phẩm theo hãng</h2>
          <p>Khám phá các mẫu điện thoại nổi bật theo từng thương hiệu</p>
        </div>

        <RouterLink to="/san-pham">
          Xem tất cả
          <i class="bi bi-chevron-right"></i>
        </RouterLink>
      </div>

      <div class="brand-tabs">
        <button
            v-for="(brand, index) in brandTabs"
            :key="brand.id || brand.slug || brand.name"
            type="button"
            class="brand-tab"
            :class="{ active: selectedBrand ? selectedBrand === getBrandTabValue(brand) : index === 0 }"
            @click="selectedBrand = getBrandTabValue(brand)"
        >
          {{ brand.name }}
        </button>
      </div>

      <div class="brand-product-grid">
        <ProductCard
            v-for="product in brandProducts"
            :key="product.id"
            :image="product.image"
            :name="product.name"
            :colors="product.colors"
            :price="product.price"
            :old-price="product.oldPrice || ''"
            :to="product.to"
            :product-id="product.productId"
            :variant-id="product.variantId"
            :cart-quantity="1"
            :stock-quantity="product.stockQuantity"
        />
      </div>
    </section>
  </div>
</template>

<style scoped>
.home-page {
  padding-top: 14px;
  background: var(--card-bg);
}

.hero-banner {
  width: 100%;
  height: 420px;
  border-radius: 10px;
  overflow: hidden;
  background: #e8f0fb;
}

.hero-banner img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.featured-section {
  margin-top: 22px;
}

.section-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 14px;
}

.section-heading h2 {
  margin: 0;
  font-size: 30px;
  line-height: 1.25;
  font-weight: 800;
  color: var(--text-color);
}

.section-heading p {
  margin: 5px 0 0;
  color: var(--muted-color);
  font-size: 15px;
  font-weight: 500;
}

.section-heading a {
  color: var(--primary-color);
  font-weight: 700;
  font-size: 16px;
  white-space: nowrap;
  text-decoration: none;
}

.section-heading a:hover {
  color: var(--primary-hover);
}

.product-slider-wrap {
  position: relative;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 16px;
}

.loading-state {
  grid-column: 1 / -1;
  padding: 16px 0;
  color: var(--muted-color);
  font-size: 14px;
  font-weight: 600;
}

.product-nav {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 4;
  border: none;
  background: var(--card-bg);
  color: var(--text-color);
  width: 48px;
  height: 48px;
  border-radius: 50%;
  box-shadow: 0 6px 20px rgba(15, 23, 42, 0.1);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.product-nav:hover {
  color: var(--primary-color);
}

.product-nav-left {
  left: -24px;
}

.product-nav-right {
  right: -24px;
}

.promo-section {
  margin-top: 20px;
}

.promo-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 16px;
}

.promo-placeholder-card {
  display: block;
  height: 180px;
  border-radius: 10px;
  overflow: hidden;
  background: #e8f0fb;
}

.promo-placeholder-card img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.brand-products-section {
  margin-top: 28px;
  padding-bottom: 46px;
}

.brand-heading {
  align-items: flex-end;
  margin-bottom: 16px;
}

.brand-tabs {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 18px;
  overflow-x: auto;
  padding-bottom: 4px;
}

.brand-tab {
  height: 38px;
  padding: 0 20px;
  border: 1px solid var(--border-color);
  border-radius: 999px;
  background: #ffffff;
  color: var(--text-color);
  font-size: 14px;
  font-weight: 700;
  white-space: nowrap;
}

.brand-tab.active,
.brand-tab:hover {
  border-color: var(--primary-color);
  background: var(--primary-color);
  color: #ffffff;
}

.brand-product-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 22px;
}

@media (max-width: 1200px) {
  .hero-banner {
    height: 340px;
  }

  .product-grid {
    grid-template-columns: repeat(3, 1fr);
  }

  .brand-product-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 900px) {
  .promo-grid {
    grid-template-columns: 1fr;
  }

  .hero-banner {
    height: 260px;
  }

  .product-grid,
  .brand-product-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
  }

  .promo-placeholder-card {
    height: 150px;
  }

  .product-nav-left {
    left: -10px;
  }

  .product-nav-right {
    right: -10px;
  }

  .section-heading {
    align-items: flex-start;
    flex-direction: column;
  }
}

@media (max-width: 576px) {
  .hero-banner {
    height: 210px;
  }

  .product-grid,
  .brand-product-grid {
    grid-template-columns: 1fr;
  }

  .section-heading h2 {
    font-size: 24px;
  }

  .brand-products-section {
    margin-top: 24px;
  }
}
</style>
