<script setup>
import {computed, onMounted, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useProductStore} from '@/stores/productStore'
import {buildProductCards, normalizeText} from '@/utils/productCardHelpers'
import {formatCurrency} from '@/utils/formatCurrency'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()
const searchKeyword = ref('')

const {items: productItems} = storeToRefs(productStore)

const productCards = computed(() => {
  const items = Array.isArray(productItems.value) ? productItems.value : []
  return buildProductCards(items)
})

const searchSuggestions = computed(() => {
  const keyword = normalizeText(searchKeyword.value)

  if (!keyword) {
    return []
  }

  return productCards.value
      .map((card) => {
        const name = normalizeText(card.name)
        const brandName = normalizeText(card.brandName)
        const categoryName = normalizeText(card.categoryName)
        const rom = normalizeText(card.rom)
        const sku = normalizeText(card.variant?.sku ?? '')

        let score = 0

        if (name === keyword) score += 100
        if (name.startsWith(keyword)) score += 60
        if (name.includes(keyword)) score += 30
        if (brandName.includes(keyword)) score += 15
        if (categoryName.includes(keyword)) score += 10
        if (rom.includes(keyword)) score += 10
        if (sku.includes(keyword)) score += 20

        return {
          ...card,
          score,
        }
      })
      .filter((card) => card.score > 0)
      .sort((left, right) => {
        if (right.score !== left.score) {
          return right.score - left.score
        }

        return Number(left.price ?? 0) - Number(right.price ?? 0)
      })
      .slice(0, 6)
})

const syncSearchKeyword = () => {
  searchKeyword.value = String(route.query.q ?? '')
}

const handleSearch = async () => {
  const keyword = searchKeyword.value.trim()
  const currentKeyword = String(route.query.q ?? '').trim()

  if (route.name === 'products.index' && keyword === currentKeyword) {
    return
  }

  await router.push({
    name: 'products.index',
    query: keyword ? {q: keyword} : {},
  })
}

watch(
    () => route.query.q,
    syncSearchKeyword,
    {immediate: true},
)

onMounted(() => {
  if (!productItems.value.length) {
    productStore.fetchAll({status: 'active', per_page: 500}).catch(() => {
    })
  }
})
</script>

<template>
  <form class="search-box" @submit.prevent="handleSearch">
    <input v-model.trim="searchKeyword" type="search" placeholder="Bạn cần tìm gì?"/>
    <button type="submit">Tìm kiếm</button>
    <div v-if="searchSuggestions.length" class="search-suggestions">
      <RouterLink
          v-for="item in searchSuggestions"
          :key="item.id"
          :to="item.to"
          class="search-suggestion"
      >
        <img :src="item.image || '/images/default-product.png'" :alt="item.name" class="suggestion-image"/>

        <div class="suggestion-info">
          <strong>{{ item.name }}</strong>
          <span v-if="item.brandName || item.rom">{{ [item.brandName, item.rom].filter(Boolean).join(' - ') }}</span>
          <em>{{ formatCurrency(item.price) }}</em>
        </div>
      </RouterLink>
    </div>
  </form>
</template>

<style scoped>
.search-box {
  position: relative;
  width: min(100%, 720px);
  height: 38px;
  display: grid;
  grid-template-columns: 1fr 128px;
  justify-self: center;
}

.search-box input {
  border: 1px solid var(--border-color);
  border-right: none;
  border-radius: 8px 0 0 8px;
  padding: 0 16px;
  outline: none;
  font-size: 13px;
  background: var(--card-bg);
  color: var(--text-color);
}

.search-box input:focus {
  border-color: var(--primary-color);
}

.search-box button {
  border: none;
  background: var(--primary-color);
  color: #ffffff;
  border-radius: 0 8px 8px 0;
  font-weight: 700;
  font-size: 13px;
}

.search-box button:hover {
  background: var(--primary-hover);
}

.search-suggestions {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 128px;
  max-height: 360px;
  overflow-y: auto;
  border: 1px solid var(--border-color);
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
  z-index: 20;
}

.search-suggestion {
  display: grid;
  grid-template-columns: 56px 1fr;
  gap: 12px;
  align-items: center;
  padding: 10px 12px;
  color: var(--text-color);
  text-decoration: none;
  border-bottom: 1px solid #eef2f7;
}

.search-suggestion:last-child {
  border-bottom: none;
}

.search-suggestion:hover {
  background: #f8fbff;
}

.suggestion-image {
  width: 56px;
  height: 56px;
  object-fit: cover;
  border-radius: 10px;
  background: #f8fafc;
}

.suggestion-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}

.suggestion-info strong {
  font-size: 13px;
  font-weight: 700;
  line-height: 1.4;
  color: #0f172a;
}

.suggestion-info span {
  font-size: 12px;
  color: #64748b;
}

.suggestion-info em {
  font-size: 12px;
  font-style: normal;
  font-weight: 800;
  color: #0d6efd;
}

@media (max-width: 991.98px) {
  .search-box {
    width: 100%;
  }
}
</style>
