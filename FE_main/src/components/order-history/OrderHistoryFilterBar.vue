<script setup>
defineProps({
  searchKeyword: {
    type: String,
    default: '',
  },
  selectedStatus: {
    type: String,
    default: 'all',
  },
  orderTabs: {
    type: Array,
    required: true,
  },
})

defineEmits(['update:searchKeyword', 'update:selectedStatus'])
</script>

<template>
  <div class="top-row">
    <div class="filter-card">
      <div class="filter-row">
        <div class="input-box">
          <input
              :value="searchKeyword"
              type="text"
              placeholder="Tìm theo mã đơn hàng, sản phẩm hoặc địa chỉ"
              @input="$emit('update:searchKeyword', $event.target.value)"
          />
          <i class="bi bi-search"></i>
        </div>

        <select
            :value="selectedStatus"
            class="status-select"
            @change="$emit('update:selectedStatus', $event.target.value)"
        >
          <option value="all">Tất cả trạng thái</option>
          <option v-for="tab in orderTabs.slice(1)" :key="tab.key" :value="tab.key">
            {{ tab.label }}
          </option>
        </select>
      </div>

      <div class="tabs">
        <button
            v-for="tab in orderTabs"
            :key="tab.key"
            type="button"
            class="tab-btn"
            :class="{ active: selectedStatus === tab.key }"
            @click="$emit('update:selectedStatus', tab.key)"
        >
          {{ tab.label }}
        </button>
      </div>
    </div>
  </div>
</template>

