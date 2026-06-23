<script setup>
import ProductCard from '@/components/product/ProductCard.vue'
import ProductFilter from '@/components/product/ProductFilter.vue'
import BasePagination from '@/components/common/BasePagination.vue'
import {computed, onMounted, ref, watch} from 'vue'
import {storeToRefs} from 'pinia'
import {useRoute} from 'vue-router'
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

const accessorySignals = [
  'phu kien',
  'phu-kien',
  'accessory',
  'op lung',
  'bao da',
  'kinh cuong luc',
  'tai nghe',
  'sac nhanh',
  'pin du phong',
  'cap sac',
]

const accessoryCategoryRules = [
  {
    label: 'Sạc',
    keywords: ['charger', 'gan', 'sạc'],
  },
  {
    label: 'Cáp sạc',
    keywords: ['cable', 'usb-c cable', 'cap sac', 'usb'],
  },
  {
    label: 'Pin dự phòng',
    keywords: ['power bank', 'powerbank', 'pin du phong'],
  },
  {
    label: 'Sạc không dây',
    keywords: ['wireless charger', 'magsafe charger', 'magsafe', 'wireless'],
  },
  {
    label: 'Ốp lưng',
    keywords: ['case', 'op lung', 'bao da', 'armor', 'hybrid', 'liquid air'],
  },
  {
    label: 'Kính cường lực',
    keywords: ['screen protector', 'privacy', 'anti-glare', 'air guard', 'camera lens', 'kinh cuong luc'],
  },
  {
    label: 'Hub / Dock',
    keywords: ['hub', 'dock', '4-in-1', '5-in-1', '7-in-1', '9-in-1', '11-in-1'],
  },
  {
    label: 'Tai nghe',
    keywords: ['tai nghe', 'earbud', 'headphone', 'earphone'],
  },
  {
    label: 'Giá đỡ / Mount',
    keywords: ['kickstand', 'car mount', 'mount', 'stand', 'holder', 'giá đỡ'],
  },
  {
    label: 'Sạc ô tô',
    keywords: ['car charger', 'oto', 'ô tô'],
  },
]

const productList = computed(() => Array.isArray(products.value) ? products.value : [])
const productCards = computed(() => buildProductCards(productList.value))

const handleSelectedBrands = (brandIds) => {
  selectedBrands.value = brandIds
}

const handleSelectedPriceRange = (priceRange) => {
  selectedPriceRange.value = priceRange
}

const handleSelectedStorages = (storages) => {
  selectedStorages.value = storages
}

const isAccessoryCard = (productCard) => {
  const values = [
    productCard.name,
    productCard.categoryName,
    productCard.categorySlug,
    productCard.brandName,
  ]

  return accessorySignals.some((keyword) => {
    const normalizedKeyword = normalizeText(keyword)
    return values.some((value) => normalizeText(value).includes(normalizedKeyword))
  })
}

const getAccessoryCategory = (productCard) => {
  const values = [
    productCard.name,
    productCard.categoryName,
    productCard.categorySlug,
    productCard.brandName,
  ]
  const normalizedValues = values.map((value) => normalizeText(value))

  for (const rule of accessoryCategoryRules) {
    if (rule.keywords.some((keyword) => normalizedValues.some((value) => value.includes(normalizeText(keyword))))) {
      return rule.label
    }
  }

  return 'Khác'
}

const accessoryCards = computed(() => productCards.value.filter(isAccessoryCard))

const sortOptions = [
  {label: 'Sắp xếp: Mới nhất', value: 'newest'},
  {label: 'Nổi bật', value: 'featured'},
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
    case 'under-200k':
      return price < 200000
    case '200k-500k':
      return price >= 200000 && price < 500000
    case '500k-1m':
      return price >= 500000 && price < 1000000
    case 'over-1m':
      return price >= 1000000
    default:
      return true
  }
}

const filteredAccessories = computed(() => {
  return accessoryCards.value.filter((productCard) => {
    const featuredOk =
        selectedSort.value !== 'featured' ||
        Boolean(productCard.isFeatured)

    const brandOk =
        !selectedBrands.value.length ||
        selectedBrands.value.includes(productCard.brandId)

    const categoryOk =
        !selectedStorages.value.length ||
        selectedStorages.value.some((storage) => {
          return normalizeText(storage) === normalizeText(getAccessoryCategory(productCard))
        })

    return featuredOk && brandOk && categoryOk && matchesPriceRange(productCard.price)
  })
})

const sortedAccessories = computed(() => {
  const sortedList = [...filteredAccessories.value]

  switch (selectedSort.value) {
    case 'featured':
      return sortedList.sort((a, b) => {
        if (a.isFeatured !== b.isFeatured) {
          return a.isFeatured ? -1 : 1
        }

        return b.id - a.id
      })
    case 'price-asc':
      return sortedList.sort((a, b) => a.price - b.price)
    case 'price-desc':
      return sortedList.sort((a, b) => b.price - a.price)
    case 'name-asc':
      return sortedList.sort((a, b) => a.name.localeCompare(b.name, 'vi', {sensitivity: 'base'}))
    default:
      return sortedList
  }
})

const totalPages = computed(() => {
  return Math.max(1, Math.ceil(sortedAccessories.value.length / selectedPageSize.value))
})

const visibleAccessories = computed(() => {
  const startIndex = (currentPage.value - 1) * selectedPageSize.value
  return sortedAccessories.value.slice(startIndex, startIndex + selectedPageSize.value)
})

const resetPage = () => {
  currentPage.value = 1
}

watch(
    [selectedBrands, selectedPriceRange, selectedStorages, selectedSort, selectedPageSize],
    resetPage,
)

watch(totalPages, (nextTotalPages) => {
  if (currentPage.value > nextTotalPages) {
    currentPage.value = nextTotalPages
  }
})

onMounted(() => {
  if (!productList.value.length) {
    productStore.fetchAll({status: 'active', per_page: 500})
  }
})
</script>

<template>
  <section class="product-list-page accessories-list-page">
    <div class="container">
      <div class="breadcrumb-area">
        <RouterLink to="/">Trang chủ</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <span>Phụ kiện</span>
      </div>

      <div class="page-heading">
        <div>
          <h1>Phụ kiện điện thoại</h1>
          <p>Hiển thị {{ visibleAccessories.length }} / {{ filteredAccessories.length }} sản phẩm</p>
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
            :initial-brand-slug="String(route.query.brand ?? '')"
            brand-type="accessory"
            @update:selected-brands="handleSelectedBrands"
            @update:selected-price-range="handleSelectedPriceRange"
            @update:selected-storages="handleSelectedStorages"
        />

        <div class="product-main">
          <div v-if="!visibleAccessories.length && !productList.length && productStore.loading" class="empty-state">
            Đang tải phụ kiện...
          </div>

          <div v-else-if="!visibleAccessories.length" class="empty-state">
            Không tìm thấy phụ kiện phù hợp.
          </div>

          <div v-else class="product-grid">
            <ProductCard
                v-for="product in visibleAccessories"
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

          <div class="product-pagination">
            <BasePagination
                v-model:currentPage="currentPage"
                :total-pages="totalPages"
            />
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.accessories-list-page {
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

.product-pagination {
  margin-top: 28px;
  padding-top: 10px;
  border-top: 1px solid #eef2f7;
  display: flex;
  justify-content: center;
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

  .product-pagination {
    margin-top: 22px;
  }
}
</style>
