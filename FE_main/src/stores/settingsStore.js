import {defineStore} from 'pinia'
import {settingsService} from '@/services/settingsService'
import {useNotificationStore} from '@/stores/notificationStore.js'

const normalizeSettings = (payload) => {
    return payload?.data?.data ?? payload?.data ?? payload ?? null
}

export const useSettingsStore = defineStore('settings', {
    state: () => ({
        item: null,
        loading: false,
        saving: false,
        loaded: false,
        loadingPromise: null,
    }),

    getters: {
        settings: (state) => state.item || {},
    },

    actions: {
        async fetchPublic(force = false) {
            if (this.loaded && !force) {
                return this.item
            }

            if (this.loadingPromise && !force) {
                return this.loadingPromise
            }

            this.loading = true
            this.loadingPromise = (async () => {
                try {
                    const response = await settingsService.getPublic()
                    this.item = normalizeSettings(response)
                    this.loaded = true
                    return this.item
                } finally {
                    this.loading = false
                    this.loadingPromise = null
                }
            })()

            return this.loadingPromise
        },

        async fetchAdmin(force = false) {
            if (this.loaded && !force) {
                return this.item
            }

            if (this.loadingPromise && !force) {
                return this.loadingPromise
            }

            this.loading = true
            this.loadingPromise = (async () => {
                try {
                    const response = await settingsService.getAdmin()
                    this.item = normalizeSettings(response)
                    this.loaded = true
                    return this.item
                } finally {
                    this.loading = false
                    this.loadingPromise = null
                }
            })()

            return this.loadingPromise
        },

        async update(payload) {
            this.saving = true

            try {
                const response = await settingsService.update(payload)
                this.item = normalizeSettings(response)
                this.loaded = true
                useNotificationStore().success('Đã cập nhật cài đặt hệ thống.')
                return response
            } finally {
                this.saving = false
            }
        },
    },
})
