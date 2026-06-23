import {defineStore} from 'pinia'
import {productService} from '@/services/productService'
import {useNotificationStore} from '@/stores/notificationStore.js'

const productListCache = new Map()
const productListRequests = new Map()
const PRODUCT_CACHE_KEY = 'zinmobile:product-list-cache:v1'

const readPersistedProductCache = () => {
    if (typeof window === 'undefined') {
        return null
    }

    try {
        const raw = window.localStorage.getItem(PRODUCT_CACHE_KEY)
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

const persistProductCache = (items, pagination) => {
    if (typeof window === 'undefined') {
        return
    }

    try {
        window.localStorage.setItem(PRODUCT_CACHE_KEY, JSON.stringify({
            items,
            pagination,
        }))
    } catch (error) {
    }
}

const persistedCache = readPersistedProductCache()

export const useProductStore = defineStore('product', {
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

            if (!shouldForceReload && productListCache.has(cacheKey)) {
                const cached = productListCache.get(cacheKey)
                this.items = cached.items
                this.pagination = cached.pagination
                return cached.response
            }

            if (productListRequests.has(cacheKey)) {
                return productListRequests.get(cacheKey)
            }

            this.loading = true

            const request = productService.getAll(params)
                .then((response) => {
                    const payload = response.data?.data ?? response.data
                    const items = Array.isArray(payload) ? payload : (payload?.data ?? [])
                    const pagination = payload?.meta
                        ? payload.meta
                        : response.data?.meta || null

                    this.items = items
                    this.pagination = pagination
                    persistProductCache(items, pagination)
                    productListCache.set(cacheKey, {
                        items,
                        pagination,
                        response,
                    })

                    return response
                })
                .finally(() => {
                    productListRequests.delete(cacheKey)
                    this.loading = false
                })

            productListRequests.set(cacheKey, request)
            return request
        },

        async fetchById(id) {
            this.loading = true

            try {
                const response = await productService.getById(id)
                this.item = response.data?.data ?? response.data
                return response
            } finally {
                this.loading = false
            }
        },

        async fetchBySlug(slug) {
            this.loading = true

            try {
                const response = await productService.getBySlug(slug)
                this.item = response.data?.data ?? response.data
                return response
            } finally {
                this.loading = false
            }
        },

        async create(payload) {
            const response = await productService.create(payload)
            await this.fetchAll()
            useNotificationStore().success('Đã thêm sản phẩm.')
            return response
        },

        async update(id, payload) {
            const response = await productService.update(id, payload)

            const updatedItem = response.data?.data ?? response.data ?? null
            if (updatedItem?.id) {
                this.item = updatedItem
                this.items = this.items.map((item) => (
                    item.id === updatedItem.id ? {...item, ...updatedItem} : item
                ))
                persistProductCache(this.items, this.pagination)
            }
            useNotificationStore().success('Đã sửa sản phẩm.')

            return response
        },

        async remove(id) {
            const response = await productService.delete(id)
            this.items = this.items.filter((item) => item.id !== id)
            persistProductCache(this.items, this.pagination)
            useNotificationStore().success('Đã xóa sản phẩm.')
            return response
        },
    },
})
