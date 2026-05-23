<script setup>
const brands = ['Apple', 'Samsung', 'Xiaomi', 'OPPO', 'realme', 'vivo']

const prices = [
  'Dưới 5 triệu',
  '5 - 10 triệu',
  '10 - 20 triệu',
  'Trên 20 triệu',
]

const storages = ['64GB', '128GB', '256GB', '512GB', '1TB']
</script>

<template>
  <aside class="product-filter">
    <div class="filter-header">
      <h2>Bộ lọc</h2>
      <button type="button">Xóa lọc</button>
    </div>

    <div class="filter-group">
      <div class="filter-title">
        <span>Thương hiệu</span>
        <i class="bi bi-chevron-down"></i>
      </div>

      <div class="filter-body">
        <label
            v-for="brand in brands"
            :key="brand"
            class="filter-check"
        >
          <input type="checkbox" class="form-check-input"/>
          <span>{{ brand }}</span>
        </label>
      </div>
    </div>

    <div class="filter-group">
      <div class="filter-title">
        <span>Mức giá</span>
        <i class="bi bi-chevron-down"></i>
      </div>

      <div class="filter-body">
        <label
            v-for="price in prices"
            :key="price"
            class="filter-check"
        >
          <input type="checkbox" class="form-check-input"/>
          <span>{{ price }}</span>
        </label>
      </div>
    </div>

    <div class="filter-group">
      <div class="filter-title">
        <span>Dung lượng</span>
        <i class="bi bi-chevron-down"></i>
      </div>

      <div class="filter-body">
        <div class="storage-list">
          <button
              v-for="storage in storages"
              :key="storage"
              type="button"
              class="storage-btn"
          >
            {{ storage }}
          </button>
        </div>
      </div>
    </div>

    <div class="filter-group">
      <div class="filter-title">
        <span>Sắp xếp</span>
        <i class="bi bi-chevron-down"></i>
      </div>

      <div class="filter-body">
        <select class="form-select filter-select">
          <option>Mới nhất</option>
          <option>Giá tăng dần</option>
          <option>Giá giảm dần</option>
          <option>Tên A-Z</option>
        </select>
      </div>
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
  color: #111827;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 15px;
  font-weight: 800;
}

.filter-title i {
  font-size: 13px;
}

.filter-body {
  padding: 0 16px 16px;
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

.storage-btn:hover {
  border-color: #0d6efd;
  color: #0d6efd;
}

.filter-select {
  height: 40px;
  border-color: #dbe3ef;
  border-radius: 8px;
  color: #334155;
  font-size: 14px;
  font-weight: 600;
  box-shadow: none;
}
</style>