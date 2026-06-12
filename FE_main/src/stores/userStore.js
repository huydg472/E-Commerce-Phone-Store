import {defineStore} from 'pinia'
import {userService} from '@/services/userService'
import {useNotificationStore} from '@/stores/notificationStore.js'

export const useUserStore = defineStore('user', {
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
                const response = await userService.getAll(params)
                const responseData = response.data ?? {}
                const payload = responseData.data ?? responseData ?? null

                const list = Array.isArray(payload)
                    ? payload
                    : (Array.isArray(payload?.data) ? payload.data : [])

                const paginationSource = (!Array.isArray(payload) && payload && (
                    Object.prototype.hasOwnProperty.call(payload, 'current_page')
                    || Object.prototype.hasOwnProperty.call(payload, 'last_page')
                    || Object.prototype.hasOwnProperty.call(payload, 'total')
                ))
                    ? payload
                    : ((!Array.isArray(responseData) && responseData && (
                        Object.prototype.hasOwnProperty.call(responseData, 'current_page')
                        || Object.prototype.hasOwnProperty.call(responseData, 'last_page')
                        || Object.prototype.hasOwnProperty.call(responseData, 'total')
                    )) ? responseData : null)

                this.items = list
                this.pagination = paginationSource
                    ? {
                        current_page: Number(paginationSource.current_page) || 1,
                        last_page: Number(paginationSource.last_page) || 1,
                        per_page: Number(paginationSource.per_page) || list.length || 10,
                        total: Number(paginationSource.total) || list.length,
                        from: Number(paginationSource.from) || (list.length ? 1 : 0),
                        to: Number(paginationSource.to) || list.length,
                    }
                    : null
                return response
            } finally {
                this.loading = false
            }
        },

        async fetchById(id) {
            this.loading = true

            try {
                const response = await userService.getById(id)
                this.item = response.data.data || response.data
                return response
            } finally {
                this.loading = false
            }
        },

        async create(payload) {
            const response = await userService.create(payload)
            await this.fetchAll()
            useNotificationStore().success('Đã thêm người dùng.')
            return response
        },

        async update(id, payload) {
            const response = await userService.update(id, payload)
            await this.fetchAll()
            useNotificationStore().success('Đã sửa người dùng.')
            return response
        },

        async remove(id) {
            const response = await userService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            useNotificationStore().success('Đã xóa người dùng.')
            return response
        },
    },
})
