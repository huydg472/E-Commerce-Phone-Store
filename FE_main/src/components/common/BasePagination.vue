<script setup>
defineProps({
  currentPage: {
    type: Number,
    default: 1,
  },
})
</script>

<template>
  <nav class="pagination-wrap">
    <ul class="pagination-list">
      <li>
        <button class="page-btn" type="button">
          <i class="bi bi-chevron-left"></i>
        </button>
      </li>

      <li>
        <button class="page-btn active" type="button">1</button>
      </li>

      <li>
        <button class="page-btn" type="button">2</button>
      </li>

      <li>
        <button class="page-btn" type="button">3</button>
      </li>

      <li>
        <button class="page-btn" type="button">
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
  margin-top: 28px;
}

.pagination-list {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0;
  margin: 0;
  list-style: none;
}

.page-btn {
  min-width: 38px;
  height: 36px;
  padding: 0 12px;
  border: 1px solid #dbe3ef;
  border-radius: 8px;
  background: #ffffff;
  color: #334155;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 14px;
  font-weight: 700;
}

.page-btn:hover,
.page-btn.active {
  border-color: #0d6efd;
  background: #0d6efd;
  color: #ffffff;
}
</style>