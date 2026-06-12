import {defineStore} from 'pinia'
import {productVariantService} from '@/services/productVariantService'
import {useNotificationStore} from '@/stores/notificationStore.js'

export const useProductVariantStore = defineStore('productVariant', {
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
                const response = await productVariantService.getAll(params)
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
                const response = await productVariantService.getById(id)
                this.item = response.data.data || response.data
                return response
            } finally {
                this.loading = false
            }
        },

        async create(payload) {
            const response = await productVariantService.create(payload)

            const createdItem = response.data?.data ?? response.data ?? null
            if (createdItem?.id) {
                this.item = createdItem
                this.items = [...this.items, createdItem]
            }
            useNotificationStore().success('Đã thêm biến thể.')

            return response
        },

        async update(id, payload) {
            const response = await productVariantService.update(id, payload)

            const updatedItem = response.data?.data ?? response.data ?? null
            if (updatedItem?.id) {
                this.item = updatedItem
                this.items = this.items.map((item) => (
                    item.id === updatedItem.id ? {...item, ...updatedItem} : item
                ))
            }
            useNotificationStore().success('Đã sửa biến thể.')

            return response
        },

        async remove(id) {
            const response = await productVariantService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            useNotificationStore().success('Đã xóa biến thể.')
            return response
        },
    },
})
