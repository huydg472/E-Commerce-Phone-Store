<script setup>
import {computed} from 'vue'

const props = defineProps({
  currentPage: {
    type: Number,
    default: 1,
  },
  totalPages: {
    type: Number,
    default: 1,
  },
  siblingCount: {
    type: Number,
    default: 1,
  },
})

const emit = defineEmits(['update:currentPage'])

const safeTotalPages = computed(() => Math.max(1, Number(props.totalPages) || 1))
const safeCurrentPage = computed(() => {
  const current = Number(props.currentPage) || 1
  return Math.min(Math.max(current, 1), safeTotalPages.value)
})

const pages = computed(() => {
  const total = safeTotalPages.value
  const current = safeCurrentPage.value

  if (total <= 5) {
    return Array.from({length: total}, (_, index) => index + 1)
  }

  const start = Math.max(2, current - props.siblingCount)
  const end = Math.min(total - 1, current + props.siblingCount)
  const items = [1]

  if (start > 2) {
    items.push('ellipsis-start')
  }

  for (let page = start; page <= end; page += 1) {
    items.push(page)
  }

  if (end < total - 1) {
    items.push('ellipsis-end')
  }

  items.push(total)
  return items
})

const goToPage = (page) => {
  const nextPage = Math.min(Math.max(page, 1), safeTotalPages.value)
  if (nextPage !== safeCurrentPage.value) {
    emit('update:currentPage', nextPage)
  }
}
</script>

<template>
  <nav class="pagination-wrap" aria-label="Pagination">
    <ul class="pagination-list">
      <li>
        <button
            class="page-btn"
            type="button"
            :disabled="safeCurrentPage === 1"
            @click="goToPage(safeCurrentPage - 1)"
        >
          <i class="bi bi-chevron-left"></i>
        </button>
      </li>

      <template v-for="page in pages" :key="page">
        <li v-if="typeof page === 'number'">
          <button
              class="page-btn"
              :class="{ active: page === safeCurrentPage }"
              type="button"
              @click="goToPage(page)"
          >
            {{ page }}
          </button>
        </li>

        <li v-else>
          <span class="page-ellipsis">...</span>
        </li>
      </template>

      <li>
        <button
            class="page-btn"
            type="button"
            :disabled="safeCurrentPage === safeTotalPages"
            @click="goToPage(safeCurrentPage + 1)"
        >
          <i class="bi bi-chevron-right"></i>
        </button>
      </li>
    </ul>
  </nav>
</template>

<style scoped>
.pagination-wrap {
  display: flex;
  justify-content: center;
  margin-top: 0;
  min-width: 158px;
  flex: 0 0 auto;
}

.pagination-list {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 0;
  margin: 0;
  list-style: none;
  min-width: 158px;
}

.page-btn {
  flex: 0 0 auto;
  min-width: 40px;
  height: 40px;
  padding: 0 11px;
  border: 1px solid #dbe3ef;
  border-radius: 11px;
  background: #ffffff;
  color: #334155;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 13px;
  font-weight: 700;
  transition: background-color 0.18s ease, border-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
}

.page-btn:disabled {
  opacity: 0.45;
  cursor: not-allowed;
}

.page-btn:hover,
.page-btn.active {
  border-color: #0d6efd;
  background: #0d6efd;
  color: #ffffff;
}

.page-ellipsis {
  min-width: 18px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #94a3b8;
  font-size: 13px;
  font-weight: 700;
}
</style>
