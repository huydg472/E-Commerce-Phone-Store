<script setup>
import ProductCard from '@/components/product/ProductCard.vue'
import ProductFilter from '@/components/product/ProductFilter.vue'
import BasePagination from '@/components/common/BasePagination.vue'
import {computed, onMounted, ref, watch} from 'vue'
import {useRoute} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useProductStore} from '@/stores/productStore.js'
import {buildProductCards, normalizeText} from '@/utils/productCardHelpers.js'

const productStore = useProductStore()
const route = useRoute()
const {items: products} = storeToRefs(productStore)

const selectedBrands = ref([])
const selectedPriceRange = ref('')
const selectedStorages = ref([])
const selectedSort = ref('newest')
const selectedPageSize = ref(12)
const currentPage = ref(1)
const initialBrandSlug = computed(() => String(route.query.brand ?? ''))
const initialCategorySlug = computed(() => String(route.query.category ?? ''))
const selectedCategorySlug = ref('')

const handleSelectedBrands = (brandIds) => {
  selectedBrands.value = brandIds
}

const handleSelectedPriceRange = (priceRange) => {
  selectedPriceRange.value = priceRange
}

const handleSelectedStorages = (storages) => {
  selectedStorages.value = storages
}

const productList = computed(() => Array.isArray(products.value) ? products.value : [])
const productCards = computed(() => buildProductCards(productList.value))

const sortOptions = [
  {label: 'Sắp xếp: Mới nhất', value: 'newest'},
  {label: 'Giá tăng dần', value: 'price-asc'},
  {label: 'Giá giảm dần', value: 'price-desc'},
  {label: 'Tên A-Z', value: 'name-asc'},
]

const pageSizeOptions = [12, 24, 36]

const matchesPriceRange = (price) => {
  if (!selectedPriceRange.value) {
    return true
  }

  switch (selectedPriceRange.value) {
    case 'under-5':
      return price < 5000000
    case '5-10':
      return price >= 5000000 && price < 10000000
    case '10-20':
      return price >= 10000000 && price < 20000000
    case 'over-20':
      return price >= 20000000
    default:
      return true
  }
}

const filteredProducts = computed(() => {
  return productCards.value.filter((productCard) => {
    const brandOk =
        !selectedBrands.value.length ||
        selectedBrands.value.includes(productCard.brandId)

    const categoryOk =
        !selectedCategorySlug.value ||
        normalizeText(productCard.categorySlug) === normalizeText(selectedCategorySlug.value) ||
        normalizeText(productCard.categoryName) === normalizeText(selectedCategorySlug.value)

    const romOk =
        !selectedStorages.value.length ||
        selectedStorages.value.some((storage) => {
          return normalizeText(storage) === normalizeText(productCard.rom)
        })

    return brandOk && categoryOk && romOk && matchesPriceRange(productCard.price)
  })
})

const sortedProducts = computed(() => {
  const products = [...filteredProducts.value]

  switch (selectedSort.value) {
    case 'price-asc':
      return products.sort((a, b) => a.price - b.price)
    case 'price-desc':
      return products.sort((a, b) => b.price - a.price)
    case 'name-asc':
      return products.sort((a, b) => {
        return a.name.localeCompare(b.name, 'vi', {sensitivity: 'base'})
      })
    default:
      return products
  }
})

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(sortedProducts.value.length / selectedPageSize.value))
})

const visibleProducts = computed(() => {
  const startIndex = (currentPage.value - 1) * selectedPageSize.value
  return sortedProducts.value.slice(startIndex, startIndex + selectedPageSize.value)
})

const resetPage = () => {
  currentPage.value = 1
}

watch(
    [selectedBrands, selectedPriceRange, selectedStorages, selectedSort, selectedPageSize],
    resetPage,
    {deep: true}
)

watch(
    initialCategorySlug,
    (nextCategorySlug) => {
      if (nextCategorySlug) {
        selectedCategorySlug.value = nextCategorySlug
        resetPage()
      }
    },
    {immediate: true}
)

watch(totalPages, (nextTotalPages) => {
  if (currentPage.value > nextTotalPages) {
    currentPage.value = nextTotalPages
  }
})

onMounted(() => {
  productStore.fetchAll({status: 'active', per_page: 500})
})
</script>

<template>
  <section class="product-list-page">
    <div class="container">
      <div class="breadcrumb-area">
        <RouterLink to="/">Trang chủ</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <span>Sản phẩm</span>
      </div>

      <div class="page-heading">
        <div>
          <h1>Tất cả sản phẩm</h1>
          <p>Hiển thị {{ visibleProducts.length }} / {{ filteredProducts.length }} sản phẩm</p>
        </div>

        <div class="heading-action">
          <select v-model="selectedSort" class="form-select">
            <option
                v-for="option in sortOptions"
                :key="option.value"
                :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>

          <select v-model.number="selectedPageSize" class="form-select show-select">
            <option
                v-for="pageSize in pageSizeOptions"
                :key="pageSize"
                :value="pageSize"
            >
              Hiển thị: {{ pageSize }}
            </option>
          </select>
        </div>
      </div>

      <div class="product-layout">
        <ProductFilter
            :initial-brand-slug="initialBrandSlug"
            @update:selected-brands="handleSelectedBrands"
            @update:selected-price-range="handleSelectedPriceRange"
            @update:selected-storages="handleSelectedStorages"
        />

        <div class="product-main">
          <div v-if="!visibleProducts.length" class="empty-state">
            Không tìm thấy sản phẩm phù hợp.
          </div>

          <div v-else class="product-grid">
            <ProductCard
                v-for="product in visibleProducts"
                :key="product.id"
                :name="product.name"
                :image="product.image"
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

          <BasePagination
              v-model:currentPage="currentPage"
              :total-pages="totalPages"
          />
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.product-list-page {
  padding: 26px 0 56px;
  background: #ffffff;
}

.breadcrumb-area {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 12px;
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

.page-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 22px;
}

.page-heading h1 {
  margin: 0 0 8px;
  color: #111827;
  font-size: 30px;
  font-weight: 800;
}

.page-heading p {
  margin: 0;
  color: #64748b;
  font-size: 15px;
  font-weight: 500;
}

.heading-action {
  display: flex;
  align-items: center;
  gap: 12px;
}

.heading-action .form-select {
  min-width: 180px;
  height: 40px;
  border-color: #dbe3ef;
  border-radius: 8px;
  color: #334155;
  font-size: 14px;
  font-weight: 600;
  box-shadow: none;
}

.heading-action .show-select {
  min-width: 130px;
}

.product-layout {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 24px;
  align-items: flex-start;
}

.product-main {
  min-width: 0;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}

.empty-state {
  padding: 32px 20px;
  border: 1px dashed #dbe3ef;
  border-radius: 12px;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
  text-align: center;
}

@media (max-width: 1200px) {
  .product-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 992px) {
  .page-heading {
    align-items: flex-start;
    flex-direction: column;
  }

  .heading-action {
    width: 100%;
  }

  .heading-action .form-select {
    width: 100%;
  }

  .product-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .page-heading h1 {
    font-size: 25px;
  }

  .heading-action {
    flex-direction: column;
  }

  .product-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }
}

@media (max-width: 480px) {
  .product-grid {
    grid-template-columns: 1fr;
  }
}
</style>
