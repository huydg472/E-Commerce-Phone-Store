import { defineStore } from 'pinia'
import { reportService } from '@/services/reportService'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    revenue: null,
    topProducts: [],
    recentOrders: [],
    loading: false,
  }),

  actions: {
    async fetchDashboard() {
      this.loading = true

      try {
        const [revenueResponse, productResponse, orderResponse] = await Promise.all([
          reportService.revenue(),
          reportService.products(),
          reportService.orders(),
        ])

        this.revenue = revenueResponse.data.data || revenueResponse.data
        this.topProducts = productResponse.data.data || []
        this.recentOrders = orderResponse.data.data || []
      } finally {
        this.loading = false
      }
    },
  },
})
