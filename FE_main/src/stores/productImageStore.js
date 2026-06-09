import {defineStore} from 'pinia'
import {productImageService} from '@/services/productImageService'

export const useProductImageStore = defineStore('productImage', {
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
                const response = await productImageService.getAll(params)
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
                const response = await productImageService.getById(id)
                this.item = response.data.data || response.data
                return response
            } finally {
                this.loading = false
            }
        },

        async create(payload) {
            const response = await productImageService.create(payload)

            const createdItem = response.data?.data ?? response.data ?? null
            if (createdItem?.id) {
                this.item = createdItem
                this.items = [createdItem, ...this.items]
            }

            return response
        },

        async update(id, payload) {
            const response = await productImageService.update(id, payload)

            const updatedItem = response.data?.data ?? response.data ?? null
            if (updatedItem?.id) {
                this.item = updatedItem
                this.items = this.items.map((item) => (
                    item.id === updatedItem.id ? {...item, ...updatedItem} : item
                ))
            }

            return response
        },

        async remove(id) {
            const response = await productImageService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            return response
        },
    },
})
