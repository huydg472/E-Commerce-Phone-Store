import {defineStore} from 'pinia'
import {roleService} from '@/services/roleService'
import {useNotificationStore} from '@/stores/notificationStore.js'

export const useRoleStore = defineStore('role', {
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
                const response = await roleService.getAll(params)
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
                const response = await roleService.getById(id)
                this.item = response.data.data || response.data
                return response
            } finally {
                this.loading = false
            }
        },

        async create(payload) {
            const response = await roleService.create(payload)
            await this.fetchAll()
            useNotificationStore().success('Đã thêm vai trò.')
            return response
        },

        async update(id, payload) {
            const response = await roleService.update(id, payload)
            await this.fetchAll()
            useNotificationStore().success('Đã sửa vai trò.')
            return response
        },

        async remove(id) {
            const response = await roleService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            useNotificationStore().success('Đã xóa vai trò.')
            return response
        },
    },
})
