import {defineStore} from 'pinia'
import {brandService} from '@/services/brandService'
import {categoryService} from '@/services/categoryService'
import {orderService} from '@/services/orderService'
import {paymentService} from '@/services/paymentService'
import {productService} from '@/services/productService'
import {userService} from '@/services/userService'

const unwrapList = (response) => response?.data?.data ?? response?.data ?? []

const unwrapPaginated = (response) => {
    const payload = response?.data?.data ?? response?.data ?? {}

    return {
        items: payload?.data ?? [],
        total: payload?.total ?? 0,
    }
}

const toNumber = (value) => Number(value ?? 0) || 0

const recomputeOrderStats = (state) => {
    state.totalOrders = state.orders.length
    state.pendingOrders = state.orders.filter((order) => order.order_status === 'pending').length
    state.confirmedOrders = state.orders.filter((order) => order.order_status === 'confirmed').length
    state.shippingOrders = state.orders.filter((order) => order.order_status === 'shipping').length
    state.completedOrders = state.orders.filter((order) => order.order_status === 'completed').length
    state.cancelledOrders = state.orders.filter((order) => order.order_status === 'cancelled').length
    state.revenue = state.orders.reduce((sum, order) => sum + toNumber(order.total_amount), 0)
}

export const useDashboardStore = defineStore('dashboard', {
    state: () => ({
        loading: false,
        error: null,
        lastUpdated: null,
        revenue: 0,
        orders: [],
        payments: [],
        products: [],
        latestProducts: [],
        brands: [],
        categories: [],
        totalProducts: 0,
        activeProducts: 0,
        featuredProducts: 0,
        totalUsers: 0,
        totalBrands: 0,
        activeBrands: 0,
        totalCategories: 0,
        activeCategories: 0,
        totalOrders: 0,
        pendingOrders: 0,
        confirmedOrders: 0,
        shippingOrders: 0,
        completedOrders: 0,
        cancelledOrders: 0,
        paidPayments: 0,
        pendingPayments: 0,
    }),

    actions: {
        async fetchDashboard() {
            this.loading = true
            this.error = null

            try {
                const [
                    totalProductsResponse,
                    activeProductsResponse,
                    featuredProductsResponse,
                    latestProductsResponse,
                    ordersResponse,
                    usersResponse,
                    brandsResponse,
                    categoriesResponse,
                    paymentsResponse,
                ] = await Promise.all([
                    productService.getAll({per_page: 1}),
                    productService.getAll({per_page: 1, status: 'active'}),
                    productService.getAll({per_page: 1, is_featured: 1}),
                    productService.getAll({per_page: 4, sort: 'latest'}),
                    orderService.getAll(),
                    userService.getAll({per_page: 1}),
                    brandService.getAll(),
                    categoryService.getAll(),
                    paymentService.getAll(),
                ])

                const totalProductsPage = unwrapPaginated(totalProductsResponse)
                const activeProductsPage = unwrapPaginated(activeProductsResponse)
                const featuredProductsPage = unwrapPaginated(featuredProductsResponse)
                const latestProductsPage = unwrapPaginated(latestProductsResponse)
                const orders = unwrapList(ordersResponse)
                const usersPage = unwrapPaginated(usersResponse)
                const brands = unwrapList(brandsResponse)
                const categories = unwrapList(categoriesResponse)
                const payments = unwrapList(paymentsResponse)

                this.totalProducts = totalProductsPage.total
                this.activeProducts = activeProductsPage.total
                this.featuredProducts = featuredProductsPage.total
                this.latestProducts = latestProductsPage.items
                this.orders = orders
                this.totalUsers = usersPage.total
                this.brands = brands
                this.categories = categories
                this.payments = payments
                this.totalBrands = brands.length
                this.activeBrands = brands.filter((brand) => brand.status === 'active').length
                this.totalCategories = categories.length
                this.activeCategories = categories.filter((category) => category.status === 'active').length
                this.paidPayments = payments.filter((payment) => payment.payment_status === 'paid').length
                this.pendingPayments = payments.filter((payment) => payment.payment_status === 'pending').length
                recomputeOrderStats(this)
                this.lastUpdated = new Date().toISOString()
            } catch (error) {
                this.error = error instanceof Error ? error.message : 'Khong the tai du lieu dashboard'
            } finally {
                this.loading = false
            }
        },

        upsertOrder(order) {
            if (!order?.id) {
                return
            }

            const index = this.orders.findIndex((item) => item.id === order.id)

            if (index === -1) {
                this.orders = [order, ...this.orders]
            } else {
                const next = [...this.orders]
                next.splice(index, 1, {...next[index], ...order})
                this.orders = next
            }

            recomputeOrderStats(this)
        },

        removeOrder(orderId) {
            this.orders = this.orders.filter((order) => order.id !== orderId)
            recomputeOrderStats(this)
        },
    },
})
