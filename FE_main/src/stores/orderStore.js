import { defineStore } from 'pinia'
import { orderService } from '@/services/orderService'

export const useOrderStore = defineStore('order', {
  state: () => ({
    items: [],
    item: null,
    loading: false,
    pagination: null,
  }),

  actions: {
    async fetchAll(params = {}) {
      this.loading = true

      try {
        const response = await orderService.getAll(params)
        this.items = response.data.data || response.data
        this.pagination = response.data.meta || null
        return response
      } finally {
        this.loading = false
      }
    },

    async fetchById(id) {
      this.loading = true

      try {
        const response = await orderService.getById(id)
        this.item = response.data.data || response.data
        return response
      } finally {
        this.loading = false
      }
    },

    async create(payload) {
      const response = await orderService.create(payload)
      await this.fetchAll()
      return response
    },

    async update(id, payload) {
      const response = await orderService.update(id, payload)
      await this.fetchAll()
      return response
    },

    async remove(id) {
      const response = await orderService.delete(id)
      this.items = this.items.filter((item) => item.id !== id)
      return response
    },
  },
})
