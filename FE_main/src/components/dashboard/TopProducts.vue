<script setup>
import {computed} from 'vue'

const props = defineProps({
  products: {
    type: Array,
    required: true,
  },
  title: {
    type: String,
    default: 'Sản phẩm bán chạy',
  },
  period: {
    type: String,
    default: 'month',
  },
})

const emit = defineEmits(['update:period'])

const selectedPeriod = computed({
  get: () => props.period,
  set: (value) => emit('update:period', value),
})
</script>

<template>
  <section class="dashboard-panel top-products-panel">
    <div class="panel-header">
      <h2>{{ title }}</h2>

      <select v-model="selectedPeriod" class="form-select form-select-sm period-select" aria-label="Lọc thời gian">
        <option value="7d">7 ngày gần đây</option>
        <option value="30d">30 ngày gần đây</option>
        <option value="month">Tháng này</option>
      </select>
    </div>

    <div class="top-product-list">
      <div v-for="product in products" :key="product.id" class="top-product-item">
        <span class="rank" :class="`rank-${product.rank}`">{{ product.rank }}</span>

        <div class="phone-thumb" :class="product.thumbClass">
          <span></span>
        </div>

        <h3>{{ product.name }}</h3>

        <div class="sold-info">
          <strong>{{ product.sold }}</strong>
          <small>đã bán</small>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.dashboard-panel {
  height: 100%;
  overflow: hidden;
  background: #ffffff;
  border: 1px solid #e5e9f1;
  border-radius: 10px;
  box-shadow: 0 9px 25px rgba(15, 23, 42, 0.05);
}

.panel-header {
  height: 62px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 0 22px 0 24px;
  border-bottom: 1px solid #edf0f5;
}

.panel-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 19px;
  font-weight: 800;
}

.period-select {
  width: 162px;
  height: 40px;
  color: #0f172a;
  border-color: #d8dee9;
}

.top-product-list {
  display: flex;
  flex-direction: column;
}

.top-product-item {
  min-height: 74px;
  display: grid;
  grid-template-columns: 34px 46px minmax(0, 1fr) 54px;
  align-items: center;
  gap: 14px;
  padding: 0 20px;
  border-bottom: 1px solid #edf0f5;
}

.top-product-item:last-child {
  border-bottom: 0;
}

.rank {
  width: 29px;
  height: 29px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  color: #ffffff;
  background: #adb5bd;
  font-size: 14px;
  font-weight: 800;
}

.rank-1 {
  background: #f7c538;
}

.rank-2 {
  background: #a7a9ac;
}

.rank-3 {
  background: #d58a38;
}

.rank-4 {
  color: #111827;
  background: #d7dce3;
}

.phone-thumb {
  width: 47px;
  height: 54px;
  position: relative;
  border-radius: 8px;
  background: linear-gradient(135deg, #111827, #27272a);
  box-shadow: inset -8px 0 10px rgba(255, 255, 255, 0.08), 0 7px 12px rgba(15, 23, 42, 0.16);
}

.phone-thumb::before {
  content: '';
  position: absolute;
  top: 6px;
  left: 7px;
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.75);
  box-shadow: 9px 0 rgba(255, 255, 255, 0.35);
}

.phone-thumb span {
  position: absolute;
  right: 5px;
  bottom: 5px;
  width: 2px;
  height: 40px;
  border-radius: 2px;
  background: rgba(255, 255, 255, 0.16);
}

.phone-graphite {
  background: linear-gradient(135deg, #08090c, #3f3f46);
}

.phone-titanium {
  background: linear-gradient(135deg, #22252b, #b7aa9a);
}

.phone-purple {
  background: linear-gradient(135deg, #1f1b24, #a855f7);
}

.phone-green {
  background: linear-gradient(135deg, #142d27, #8fd6b5);
}

.top-product-item h3 {
  margin: 0;
  color: #111827;
  font-size: 16px;
  font-weight: 800;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.sold-info {
  text-align: center;
  line-height: 1.2;
}

.sold-info strong {
  display: block;
  color: #0f172a;
  font-size: 17px;
  font-weight: 800;
}

.sold-info small {
  color: #475569;
  font-size: 14px;
}

@media (max-width: 767.98px) {
  .panel-header {
    height: auto;
    align-items: flex-start;
    flex-direction: column;
    padding: 18px;
  }

  .period-select {
    width: 100%;
  }
}
</style>
