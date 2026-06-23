<script setup>
defineProps({
  visibleCount: {
    type: Number,
    required: true,
  },
  totalCount: {
    type: Number,
    required: true,
  },
  searchKeyword: {
    type: String,
    default: '',
  },
  selectedSort: {
    type: String,
    required: true,
  },
  selectedPageSize: {
    type: Number,
    required: true,
  },
  sortOptions: {
    type: Array,
    required: true,
  },
  pageSizeOptions: {
    type: Array,
    required: true,
  },
  entityLabel: {
    type: String,
    default: 'sản phẩm',
  },
})

defineEmits(['update:selectedSort', 'update:selectedPageSize'])
</script>

<template>
  <div class="page-heading">
    <div>
      <h1>Tất cả {{ entityLabel }}</h1>
      <p>Hiển thị {{ visibleCount }} / {{ totalCount }} {{ entityLabel }}</p>
      <p v-if="searchKeyword" class="search-summary">
        Kết quả cho: "{{ searchKeyword }}"
      </p>
    </div>

    <div class="heading-action">
      <select
          :value="selectedSort"
          class="form-select"
          @change="$emit('update:selectedSort', $event.target.value)"
      >
        <option
            v-for="option in sortOptions"
            :key="option.value"
            :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>

      <select
          :value="selectedPageSize"
          class="form-select show-select"
          @change="$emit('update:selectedPageSize', Number($event.target.value))"
      >
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
</template>

<style scoped>
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

.search-summary {
  margin-top: 6px !important;
  color: #0d6efd !important;
  font-weight: 700 !important;
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
}

@media (max-width: 768px) {
  .page-heading h1 {
    font-size: 25px;
  }

  .heading-action {
    flex-direction: column;
  }
}
</style>
