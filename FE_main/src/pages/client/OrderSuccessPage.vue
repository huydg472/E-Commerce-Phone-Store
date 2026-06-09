<script setup>
import {computed} from 'vue'
import {useRoute, useRouter} from 'vue-router'

const route = useRoute()
const router = useRouter()

const orderId = computed(() => String(route.query.order_id || ''))

const goToOrders = () => {
  router.push({name: 'orders.history'})
}

const goToDetail = () => {
  if (!orderId.value) {
    goToOrders()
    return
  }

  router.push({name: 'orders.show', params: {id: orderId.value}})
}
</script>

<template>
  <section class="success-page">
    <div class="success-card">
      <div class="success-icon">
        <i class="bi bi-check-lg"></i>
      </div>

      <h1>Đặt hàng thành công</h1>
      <p>
        Đơn hàng của bạn đã được ghi nhận. Bạn có thể xem chi tiết đơn hàng hoặc quay lại danh sách đơn hàng.
      </p>

      <div v-if="orderId" class="order-pill">
        Mã đơn hàng: <strong>#{{ orderId }}</strong>
      </div>

      <div class="actions">
        <button type="button" class="primary-btn" @click="goToDetail">
          Xem chi tiết đơn hàng
        </button>
        <button type="button" class="secondary-btn" @click="goToOrders">
          Xem tất cả đơn hàng
        </button>
      </div>
    </div>
  </section>
</template>

<style scoped>
.success-page {
  min-height: 100vh;
  padding: 24px;
  display: grid;
  place-items: center;
  background: radial-gradient(circle at top left, rgba(0, 102, 255, 0.08), transparent 34%),
  radial-gradient(circle at bottom right, rgba(0, 102, 255, 0.08), transparent 34%),
  #f4f8ff;
}

.success-card {
  width: 100%;
  max-width: 640px;
  padding: 52px 40px;
  border: 1px solid #dfe8f5;
  border-radius: 18px;
  background: #ffffff;
  text-align: center;
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
}

.success-icon {
  width: 112px;
  height: 112px;
  margin: 0 auto 18px;
  border-radius: 50%;
  background: linear-gradient(135deg, #0066ff, #1d7cff);
  color: #ffffff;
  display: grid;
  place-items: center;
  font-size: 54px;
}

h1 {
  margin: 0 0 12px;
  color: #061c46;
  font-size: 32px;
}

p {
  margin: 0;
  color: #52627a;
  font-size: 17px;
  line-height: 1.6;
}

.order-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  margin-top: 18px;
  padding: 10px 14px;
  border-radius: 999px;
  background: #eef5ff;
  color: #2d3f61;
}

.order-pill strong {
  color: #0066ff;
}

.actions {
  margin-top: 28px;
  display: flex;
  justify-content: center;
  gap: 12px;
  flex-wrap: wrap;
}

.primary-btn,
.secondary-btn {
  min-width: 180px;
  height: 48px;
  padding: 0 18px;
  border-radius: 10px;
  font-weight: 800;
  border: none;
}

.primary-btn {
  background: #0066ff;
  color: #ffffff;
}

.secondary-btn {
  background: #eaf2ff;
  color: #0066ff;
}

@media (max-width: 768px) {
  .success-card {
    padding: 40px 20px;
  }

  h1 {
    font-size: 26px;
  }
}
</style>
