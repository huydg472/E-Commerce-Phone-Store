<script setup>
import StatisticCard from '@/components/dashboard/StatisticCard.vue'
import RevenueChart from '@/components/dashboard/RevenueChart.vue'
import RecentOrders from '@/components/dashboard/RecentOrders.vue'
import TopProducts from '@/components/dashboard/TopProducts.vue'
</script>

<template>
  <section>
    <div class="admin-page-header">
      <h1>Dashboard</h1>
    </div>

    <div class="dashboard-grid">
      <StatisticCard title="Sản phẩm" value="120" />
      <StatisticCard title="Đơn hàng" value="35" />
      <RevenueChart />
      <RecentOrders />
      <TopProducts />
    </div>
  </section>
</template>

<style scoped>
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
}
</style>
