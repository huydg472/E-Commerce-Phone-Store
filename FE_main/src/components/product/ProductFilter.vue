<script setup>
import {computed, onMounted, ref, watch} from 'vue'
import {storeToRefs} from 'pinia'
import {useBrandStore} from '@/stores/brandStore.js'

const props = defineProps({
  initialBrandSlug: {
    type: String,
    default: '',
  },
})

const emit = defineEmits([
  'update:selected-brands',
  'update:selected-price-range',
  'update:selected-storages',
])

const brandStore = useBrandStore()
const {items: brands, loading: brandsLoading} = storeToRefs(brandStore)

const activeBrandIds = ref([])
const selectedPriceRange = ref('')
const activeStorages = ref([])
const openSections = ref({
  brand: true,
  price: true,
  storage: true,
})
const brandPriority = ['Apple', 'Samsung', 'OPPO', 'Xiaomi', 'Vivo', 'Realme']

onMounted(() => {
  brandStore.fetchAll({status: 'active'})
})

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

const priceRanges = [
  {label: 'Dưới 5 triệu', value: 'under-5'},
  {label: '5 - 10 triệu', value: '5-10'},
  {label: '10 - 20 triệu', value: '10-20'},
  {label: 'Trên 20 triệu', value: 'over-20'},
]

const storages = ['64GB', '128GB', '256GB', '512GB', '1TB']

const toggleBrand = (brandId) => {
  const id = String(brandId)
  activeBrandIds.value = activeBrandIds.value.includes(id)
      ? activeBrandIds.value.filter((currentId) => currentId !== id)
      : [...activeBrandIds.value, id]

  emit('update:selected-brands', [...activeBrandIds.value])
}

const selectPriceRange = (value) => {
  const nextValue = selectedPriceRange.value === value ? '' : value
  selectedPriceRange.value = nextValue
  emit('update:selected-price-range', nextValue)
}

const toggleStorage = (storage) => {
  activeStorages.value = activeStorages.value.includes(storage)
      ? activeStorages.value.filter((current) => current !== storage)
      : [...activeStorages.value, storage]

  emit('update:selected-storages', [...activeStorages.value])
}

const clearFilters = () => {
  activeBrandIds.value = []
  selectedPriceRange.value = ''
  activeStorages.value = []
  emit('update:selected-brands', [])
  emit('update:selected-price-range', '')
  emit('update:selected-storages', [])
}

const toggleSection = (section) => {
  openSections.value = {
    ...openSections.value,
    [section]: !openSections.value[section],
  }
}

const normalizeBrandKey = (value) => {
  return String(value ?? '')
      .trim()
      .toLowerCase()
      .replace(/\s+/g, '-')
}

watch(
    [brandList, () => props.initialBrandSlug],
    ([nextBrands, nextSlug]) => {
      const slug = normalizeBrandKey(nextSlug)

      if (!slug) {
        return
      }

      const matchedBrand = (nextBrands || []).find((brand) => {
        return normalizeBrandKey(brand?.slug ?? brand?.name) === slug
      })

      if (!matchedBrand) {
        return
      }

      const brandId = String(matchedBrand.id)

      if (!activeBrandIds.value.includes(brandId) || activeBrandIds.value.length !== 1) {
        activeBrandIds.value = [brandId]
        emit('update:selected-brands', [brandId])
      }
    },
    {immediate: true, deep: true}
)
</script>

<template>
  <aside class="product-filter">
    <div class="filter-header">
      <h2>Bộ lọc</h2>
      <button type="button" @click="clearFilters">
        Xóa lọc
      </button>
    </div>

    <div class="filter-group">
      <button
          type="button"
          class="filter-title"
          @click="toggleSection('brand')"
      >
        <span>Thương hiệu</span>
        <i class="bi bi-chevron-down" :class="{ rotated: openSections.brand }"></i>
      </button>

      <Transition name="collapse">
        <div v-show="openSections.brand" class="filter-body">
          <label
              v-for="brand in brandList"
              :key="brand.id || brand.slug || brand.name"
              class="filter-check"
          >
            <input
                :checked="activeBrandIds.includes(String(brand.id))"
                @change="toggleBrand(brand.id)"
                type="checkbox"
                class="form-check-input"
            />
            <span>{{ brand.name }}</span>
          </label>

          <div v-if="brandsLoading" class="filter-loading">
            Đang tải thương hiệu...
          </div>
        </div>
      </Transition>
    </div>

    <div class="filter-group">
      <button
          type="button"
          class="filter-title"
          @click="toggleSection('price')"
      >
        <span>Mức giá</span>
        <i class="bi bi-chevron-down" :class="{ rotated: openSections.price }"></i>
      </button>

      <Transition name="collapse">
        <div v-show="openSections.price" class="filter-body">
          <label
              v-for="price in priceRanges"
              :key="price.value"
              class="filter-check"
          >
            <input
                :checked="selectedPriceRange === price.value"
                @click="selectPriceRange(price.value)"
                type="radio"
                class="form-check-input"
                name="price-filter"
            />
            <span>{{ price.label }}</span>
          </label>
        </div>
      </Transition>
    </div>

    <div class="filter-group">
      <button
          type="button"
          class="filter-title"
          @click="toggleSection('storage')"
      >
        <span>Dung lượng</span>
        <i class="bi bi-chevron-down" :class="{ rotated: openSections.storage }"></i>
      </button>

      <Transition name="collapse">
        <div v-show="openSections.storage" class="filter-body">
          <div class="storage-list">
            <button
                v-for="storage in storages"
                :key="storage"
                type="button"
                class="storage-btn"
                :class="{ active: activeStorages.includes(storage) }"
                @click="toggleStorage(storage)"
            >
              {{ storage }}
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </aside>
</template>

<style scoped>
.product-filter {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
}

.filter-header {
  min-height: 56px;
  padding: 14px 16px;
  border-bottom: 1px solid #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.filter-header h2 {
  margin: 0;
  color: #111827;
  font-size: 18px;
  font-weight: 800;
}

.filter-header button {
  border: none;
  background: transparent;
  color: #0d6efd;
  font-size: 13px;
  font-weight: 700;
}

.filter-group + .filter-group {
  border-top: 1px solid #e5e7eb;
}

.filter-title {
  min-height: 52px;
  padding: 0 16px;
  width: 100%;
  border: none;
  background: transparent;
  color: #111827;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 15px;
  font-weight: 800;
  text-align: left;
}

.filter-title i {
  font-size: 13px;
  display: inline-block;
  transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.filter-title i.rotated {
  transform: rotate(180deg);
}

.filter-body {
  padding: 0 16px 16px;
  overflow: hidden;
}

.collapse-enter-active,
.collapse-leave-active {
  overflow: hidden;
  transition: max-height 0.38s cubic-bezier(0.4, 0, 0.2, 1),
  opacity 0.28s ease,
  transform 0.28s ease,
  padding 0.38s cubic-bezier(0.4, 0, 0.2, 1);
}

.collapse-enter-from,
.collapse-leave-to {
  max-height: 0;
  opacity: 0;
  transform: translateY(-6px);
  padding-top: 0;
  padding-bottom: 0;
}

.collapse-enter-to,
.collapse-leave-from {
  max-height: 500px;
  opacity: 1;
  transform: translateY(0);
}

.filter-check {
  min-height: 32px;
  display: flex;
  align-items: center;
  gap: 9px;
  color: #334155;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.filter-check input {
  box-shadow: none;
}

.filter-check input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.filter-loading {
  margin-top: 8px;
  color: #64748b;
  font-size: 13px;
}

.storage-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.storage-btn {
  height: 34px;
  min-width: 68px;
  padding: 0 12px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  background: #ffffff;
  color: #334155;
  font-size: 13px;
  font-weight: 700;
}

.storage-btn:hover,
.storage-btn.active {
  border-color: #0d6efd;
  color: #0d6efd;
}
</style>
