<script setup>
import StatisticCard from '@/components/dashboard/StatisticCard.vue'
import RevenueChart from '@/components/dashboard/RevenueChart.vue'
import TopProducts from '@/components/dashboard/TopProducts.vue'
import RecentOrders from '@/components/dashboard/RecentOrders.vue'

const statistics = [
    {
        title: 'Doanh thu',
        value: '128.500.000đ',
        percent: '18,6%',
        icon: 'bi-currency-dollar',
        variant: 'primary',
    },
    {
        title: 'Đơn hàng',
        value: '245',
        percent: '12,4%',
        icon: 'bi-cart-fill',
        variant: 'success',
    },
    {
        title: 'Sản phẩm',
        value: '86',
        percent: '55,2%',
        icon: 'bi-box-seam-fill',
        variant: 'purple',
    },
    {
        title: 'Khách hàng',
        value: '1.248',
        percent: '15,8%',
        icon: 'bi-people-fill',
        variant: 'warning',
    },
]

const revenueItems = [
    { day: 'T2', label: '16.8M', percent: 56 },
    { day: 'T3', label: '18.3M', percent: 61 },
    { day: 'T4', label: '21.7M', percent: 72 },
    { day: 'T5', label: '24.6M', percent: 82 },
    { day: 'T6', label: '19.2M', percent: 64 },
    { day: 'T7', label: '15.4M', percent: 51 },
    { day: 'CN', label: '12.4M', percent: 41 },
]

const topProducts = [
    {
        id: 1,
        rank: 1,
        name: 'iPhone 15 Pro Max',
        sold: 56,
        thumbClass: 'phone-graphite',
    },
    {
        id: 2,
        rank: 2,
        name: 'Samsung Galaxy S24 Ultra',
        sold: 42,
        thumbClass: 'phone-titanium',
    },
    {
        id: 3,
        rank: 3,
        name: 'iPhone 14 Pro',
        sold: 34,
        thumbClass: 'phone-purple',
    },
    {
        id: 4,
        rank: 4,
        name: 'Xiaomi 14',
        sold: 28,
        thumbClass: 'phone-green',
    },
]

const recentOrders = [
    {
        code: '#DH10045',
        customer: 'Nguyễn Văn A',
        product: 'iPhone 15 Pro Max 256GB',
        total: '31.990.000đ',
        status: 'pending',
        date: '20/05/2024 10:23',
        thumbClass: 'phone-graphite',
    },
    {
        code: '#DH10044',
        customer: 'Trần Thị B',
        product: 'Samsung Galaxy S24 Ultra 512GB',
        total: '28.990.000đ',
        status: 'shipping',
        date: '20/05/2024 09:15',
        thumbClass: 'phone-titanium',
    },
    {
        code: '#DH10043',
        customer: 'Lê Minh C',
        product: 'Xiaomi 14 256GB',
        total: '22.990.000đ',
        status: 'completed',
        date: '19/05/2024 16:45',
        thumbClass: 'phone-green',
    },
    {
        code: '#DH10042',
        customer: 'Phạm Thùy D',
        product: 'iPhone 14 Pro 128GB',
        total: '24.990.000đ',
        status: 'cancelled',
        date: '19/05/2024 11:30',
        thumbClass: 'phone-purple',
    },
]
</script>

<template>
    <div class="dashboard-page">
        <div class="row g-4 statistic-row">
            <div v-for="statistic in statistics" :key="statistic.title" class="col-12 col-sm-6 col-xl-3">
                <StatisticCard v-bind="statistic" />
            </div>
        </div>

        <div class="row g-4 dashboard-grid">
            <div class="col-12 col-xl-6">
                <RevenueChart :items="revenueItems" />
            </div>

            <div class="col-12 col-xl-6">
                <TopProducts :products="topProducts" />
            </div>
        </div>

        <RecentOrders :orders="recentOrders" />
    </div>
</template>

<style scoped>
.dashboard-page {
    color: #0f172a;
}

.statistic-row {
    margin-bottom: 24px;
}

.dashboard-grid {
    margin-bottom: 24px;
}
</style>