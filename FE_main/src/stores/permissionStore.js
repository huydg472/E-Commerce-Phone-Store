import {defineStore} from 'pinia'
import {permissionService} from '@/services/permissionService'
import {useNotificationStore} from '@/stores/notificationStore.js'

export const usePermissionStore = defineStore('permission', {
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
                const response = await permissionService.getAll(params)
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
                const response = await permissionService.getById(id)
                this.item = response.data.data || response.data
                return response
            } finally {
                this.loading = false
            }
        },

        async create(payload) {
            const response = await permissionService.create(payload)
            await this.fetchAll()
            useNotificationStore().success('Đã thêm quyền.')
            return response
        },

        async update(id, payload) {
            const response = await permissionService.update(id, payload)
            await this.fetchAll()
            useNotificationStore().success('Đã sửa quyền.')
            return response
        },

        async remove(id) {
            const response = await permissionService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            useNotificationStore().success('Đã xóa quyền.')
            return response
        },
    },
})
