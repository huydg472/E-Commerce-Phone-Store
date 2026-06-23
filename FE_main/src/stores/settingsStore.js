import {defineStore} from 'pinia'
import {settingsService} from '@/services/settingsService'
import {useNotificationStore} from '@/stores/notificationStore.js'

const SETTINGS_SYNC_KEY = 'zinmobile:settings-sync-ts'

const normalizeSettings = (payload) => {
    return payload?.data?.data ?? payload?.data ?? payload ?? null
}

const broadcastSettingsUpdate = () => {
    if (typeof window === 'undefined') {
        return
    }

    try {
        window.localStorage.setItem(SETTINGS_SYNC_KEY, String(Date.now()))
    } catch (error) {
        // Ignore storage failures; the current tab already has the new state.
    }
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
                broadcastSettingsUpdate()
                useNotificationStore().success('Đã cập nhật cài đặt hệ thống.')
                return response
            } finally {
                this.saving = false
            }
        },
    },
})
