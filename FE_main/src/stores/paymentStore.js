import {defineStore} from 'pinia'
import {paymentService} from '@/services/paymentService'

export const usePaymentStore = defineStore('payment', {
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
                const response = await paymentService.getAll(params)
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
                const response = await paymentService.getById(id)
                this.item = response.data.data || response.data
                return response
            } finally {
                this.loading = false
            }
        },

        async create(payload) {
            const response = await paymentService.create(payload)
            await this.fetchAll()
            return response
        },

        async update(id, payload) {
            const response = await paymentService.update(id, payload)
            await this.fetchAll()
            return response
        },

        async remove(id) {
            const response = await paymentService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            return response
        },
    },
})
