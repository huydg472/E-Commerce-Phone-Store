import {defineStore} from 'pinia'
import {categoryService} from '@/services/categoryService'
import {useNotificationStore} from '@/stores/notificationStore.js'

export const useCategoryStore = defineStore('category', {
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
                const response = await categoryService.getAll(params)
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
                const response = await categoryService.getById(id)
                this.item = response.data.data || response.data
                return response
            } finally {
                this.loading = false
            }
        },

        async create(payload) {
            const response = await categoryService.create(payload)
            await this.fetchAll()
            useNotificationStore().success('Đã thêm danh mục.')
            return response
        },

        async update(id, payload) {
            const response = await categoryService.update(id, payload)

            const updatedItem = response.data?.data ?? response.data ?? null
            if (updatedItem?.id) {
                this.item = updatedItem
                this.items = this.items.map((item) => (
                    item.id === updatedItem.id ? {...item, ...updatedItem} : item
                ))
            }
            useNotificationStore().success('Đã sửa danh mục.')

            return response
        },

        async remove(id) {
            const response = await categoryService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            useNotificationStore().success('Đã xóa danh mục.')
            return response
        },
    },
})
