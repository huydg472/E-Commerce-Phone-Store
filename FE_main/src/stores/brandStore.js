import {defineStore} from 'pinia'
import {brandService} from '@/services/brandService'

export const useBrandStore = defineStore('brand', {
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
                const response = await brandService.getAll(params)
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
                const response = await brandService.getById(id)
                this.item = response.data.data || response.data
                return response
            } finally {
                this.loading = false
            }
        },

        async create(payload) {
            const response = await brandService.create(payload)
            const created = response.data?.data ?? response.data ?? null
            if (created) {
                this.items = [created, ...this.items]
            }
            return response
        },

        async update(id, payload) {
            const response = await brandService.update(id, payload)
            const updated = response.data?.data ?? response.data ?? null
            if (updated) {
                this.item = updated
                this.items = this.items.map((item) => (item.id === id ? updated : item))
            }
            return response
        },

        async remove(id) {
            const response = await brandService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            return response
        },
    },
})
