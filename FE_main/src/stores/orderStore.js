import {defineStore} from 'pinia'
import {orderService} from '@/services/orderService'
import { useDashboardStore } from '@/stores/dashboardStore'

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
            const created = response.data?.data ?? response.data ?? null
            if (created) {
                this.items = [created, ...this.items]
                useDashboardStore().upsertOrder(created)
            }
            return response
        },

        async update(id, payload) {
            const response = await orderService.update(id, payload)
            const updated = response.data?.data ?? response.data ?? null
            if (updated) {
                const existing = this.item?.id === id
                    ? this.item
                    : this.items.find((item) => item.id === id) || null
                const merged = existing ? { ...existing, ...updated, ...payload } : { ...updated, ...payload }

                this.item = merged
                this.items = this.items.map((item) => (item.id === id ? { ...item, ...updated, ...payload } : item))
                useDashboardStore().upsertOrder(merged)
            }
            return response
        },

        async remove(id) {
            const response = await orderService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            useDashboardStore().removeOrder(id)
            return response
        },
    },
})
