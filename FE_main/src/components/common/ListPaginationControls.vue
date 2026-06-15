<script setup>
import {computed} from 'vue'
import BasePagination from '@/components/common/BasePagination.vue'

const props = defineProps({
    currentPage: {
        type: Number,
        default: 1,
    },
    totalPages: {
        type: Number,
        default: 1,
    },
    pageSize: {
        type: Number,
        default: 5,
    },
    pageSizeOptions: {
      type: Array,
      default: () => [5, 10, 20, 50],
    },
    totalItems: {
        type: Number,
        default: 0,
    },
    pageStart: {
        type: Number,
        default: 0,
    },
    pageEnd: {
        type: Number,
        default: 0,
    },
    itemLabel: {
        type: String,
        default: 'mục',
    },
})

const emit = defineEmits(['update:currentPage', 'update:pageSize'])

const selectedPageSize = computed({
    get: () => props.pageSize,
    set: (value) => emit('update:pageSize', Number(value)),
})

const updatePage = (page) => {
    emit('update:currentPage', page)
}
</script>

<template>
    <div v-if="totalItems > 0" class="pagination-toolbar">
        <div class="pagination-summary">
            Hiển thị {{ pageStart }}-{{ pageEnd }} trong tổng số {{ totalItems }} {{ itemLabel }}
        </div>

        <div class="pagination-actions">
            <label class="page-size-filter">
                <span>Hiển thị</span>
                <select v-model="selectedPageSize">
                    <option
                        v-for="size in pageSizeOptions"
                        :key="size"
                        :value="size"
                    >
                        {{ size }}
                    </option>
                </select>
            </label>

            <BasePagination
                :current-page="currentPage"
                :total-pages="totalPages"
                @update:currentPage="updatePage"
            />
        </div>
    </div>
</template>

<style scoped>
.pagination-toolbar {
  width: 100%;
  align-self: stretch;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
  padding: 24px 0 4px;
  margin-top: 34px;
  border-top: 1px solid #eef2f7;
}

.pagination-summary {
  color: #64748b;
  font-size: 13px;
  flex: 1 1 auto;
  min-width: 0;
}

.pagination-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: nowrap;
  margin-left: auto;
  flex: 0 0 auto;
  justify-content: flex-end;
}

.pagination-summary {
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
}

.page-size-filter {
  flex: 0 0 auto;
  min-height: 34px;
  padding: 0 10px 0 12px;
  border: 1px solid #dbe3ef;
  border-radius: 11px;
  background: #ffffff;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #334155;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
  box-shadow: 0 1px 0 rgba(15, 23, 42, 0.02);
  transition: border-color 0.15s ease, box-shadow 0.15s ease, transform 0.15s ease;
}

.page-size-filter:hover,
.page-size-filter:focus-within {
  border-color: #bfd0ef;
  box-shadow: 0 6px 18px rgba(37, 99, 235, 0.06);
}

.page-size-filter span {
  line-height: 1;
}

.page-size-filter select {
  min-width: 42px;
  border: none;
  outline: none;
  background: transparent;
  color: #0f172a;
  font-size: 12px;
  font-weight: 700;
  cursor: pointer;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  padding-right: 16px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='13' height='13' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.25' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right center;
}

@media (max-width: 767.98px) {
  .pagination-toolbar {
    flex-direction: column;
    align-items: stretch;
    margin-top: 24px;
  }

  .pagination-summary,
  .pagination-actions,
  .page-size-filter {
    width: 100%;
  }

  .pagination-actions {
    flex-direction: column;
    align-items: stretch;
  }

  .pagination-actions {
    margin-left: 0;
  }
}
</style>
