import {defineStore} from 'pinia'
import {brandService} from '@/services/brandService'
import {useNotificationStore} from '@/stores/notificationStore.js'

const brandListCache = new Map()
const brandListRequests = new Map()
const BRAND_CACHE_KEY = 'zinmobile:brand-list-cache:v1'

const readPersistedBrandCache = () => {
    if (typeof window === 'undefined') {
        return null
    }

    try {
        const raw = window.localStorage.getItem(BRAND_CACHE_KEY)
        if (!raw) {
            return null
        }

        const parsed = JSON.parse(raw)
        if (!Array.isArray(parsed?.items)) {
            return null
        }

        return {
            items: parsed.items,
            pagination: parsed.pagination ?? null,
        }
    } catch (error) {
        return null
    }
}

const persistBrandCache = (items, pagination) => {
    if (typeof window === 'undefined') {
        return
    }

    try {
        window.localStorage.setItem(BRAND_CACHE_KEY, JSON.stringify({
            items,
            pagination,
        }))
    } catch (error) {
    }
}

const persistedCache = readPersistedBrandCache()

export const useBrandStore = defineStore('brand', {
    state: () => ({
        items: persistedCache?.items ?? [],
        item: null,
        loading: false,
        pagination: persistedCache?.pagination ?? null,
    }),

    actions: {
        async fetchAll(params = {}, options = {}) {
            const cacheKey = JSON.stringify(params ?? {})
            const shouldForceReload = Boolean(options.force)

            if (!shouldForceReload && brandListCache.has(cacheKey)) {
                const cached = brandListCache.get(cacheKey)
                this.items = cached.items
                this.pagination = cached.pagination
                return cached.response
            }

            if (brandListRequests.has(cacheKey)) {
                return brandListRequests.get(cacheKey)
            }

            this.loading = true

            const request = brandService.getAll(params)
                .then((response) => {
                    const payload = response.data?.data ?? response.data
                    const items = Array.isArray(payload) ? payload : (payload?.data ?? [])
                    const pagination = payload?.meta
                        ? payload.meta
                        : response.data?.meta || null

                    this.items = items
                    this.pagination = pagination
                    persistBrandCache(items, pagination)
                    brandListCache.set(cacheKey, {
                        items,
                        pagination,
                        response,
                    })

                    return response
                })
                .finally(() => {
                    brandListRequests.delete(cacheKey)
                    this.loading = false
                })

            brandListRequests.set(cacheKey, request)
            return request
        },

        async fetchById(id) {
            this.loading = true

            try {
                const response = await brandService.getById(id)
                this.item = response.data?.data ?? response.data
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
                persistBrandCache(this.items, this.pagination)
            }
            useNotificationStore().success('Đã thêm thương hiệu.')
            return response
        },

        async update(id, payload) {
            const response = await brandService.update(id, payload)
            const updated = response.data?.data ?? response.data ?? null
            if (updated) {
                this.item = updated
                this.items = this.items.map((item) => (item.id === id ? updated : item))
                persistBrandCache(this.items, this.pagination)
            }
            useNotificationStore().success('Đã sửa thương hiệu.')
            return response
        },

        async remove(id) {
            const response = await brandService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            persistBrandCache(this.items, this.pagination)
            useNotificationStore().success('Đã xóa thương hiệu.')
            return response
        },
    },
})
