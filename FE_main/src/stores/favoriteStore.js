import {defineStore} from 'pinia'
import {favoriteService} from '@/services/favoriteService'
import {useAuthStore} from '@/stores/authStore'
import {useNotificationStore} from '@/stores/notificationStore.js'

const unwrapList = (response) => {
    const payload = response?.data?.data ?? response?.data ?? []

    if (Array.isArray(payload)) {
        return payload
    }

    if (Array.isArray(payload?.data)) {
        return payload.data
    }

    return []
}

const getFavoriteVariantId = (favorite) => {
    return Number(favorite?.product_variant_id ?? favorite?.productVariant?.id ?? favorite?.product_variant?.id ?? 0)
}

export const useFavoriteStore = defineStore('favorite', {
    state: () => ({
        items: [],
        variantIds: [],
        loading: false,
        loaded: false,
        loadPromise: null,
    }),

    getters: {
        isFavorite: (state) => (productVariantId) => {
            return state.variantIds.includes(Number(productVariantId))
        },
    },

    actions: {
        applyItems(items) {
            this.items = Array.isArray(items) ? items : []
            this.variantIds = this.items
                .map(getFavoriteVariantId)
                .filter(Boolean)
        },

        clear() {
            this.items = []
            this.variantIds = []
            this.loaded = false
            this.loadPromise = null
        },

        async fetchAll(params = {}) {
            const authStore = useAuthStore()

            if (!authStore.isLoggedIn) {
                this.clear()
                return null
            }

            this.loading = true

            try {
                const response = await favoriteService.getAll(params)
                this.applyItems(unwrapList(response))
                this.loaded = true
                return response
            } finally {
                this.loading = false
                this.loadPromise = null
            }
        },

        ensureLoaded() {
            const authStore = useAuthStore()

            if (!authStore.isLoggedIn) {
                this.clear()
                return Promise.resolve(null)
            }

            if (this.loaded) {
                return Promise.resolve(null)
            }

            if (!this.loadPromise) {
                this.loadPromise = this.fetchAll()
            }

            return this.loadPromise
        },

        async toggle(productVariantId) {
            const normalizedId = Number(productVariantId)
            const notificationStore = useNotificationStore()

            if (!normalizedId) {
                return null
            }

            const wasFavorite = this.variantIds.includes(normalizedId)

            this.variantIds = wasFavorite
                ? this.variantIds.filter((id) => id !== normalizedId)
                : [...this.variantIds, normalizedId]

            if (wasFavorite) {
                this.items = this.items.filter((favorite) => getFavoriteVariantId(favorite) !== normalizedId)
            }

            try {
                const response = await favoriteService.toggle(normalizedId)
                const data = response.data?.data ?? response.data ?? {}
                const isFavorite = Boolean(data?.is_favorite)
                const favorite = data?.favorite ?? null

                this.variantIds = isFavorite
                    ? [...new Set([...this.variantIds, normalizedId])]
                    : this.variantIds.filter((id) => id !== normalizedId)

                if (favorite) {
                    this.items = [
                        favorite,
                        ...this.items.filter((item) => getFavoriteVariantId(item) !== normalizedId),
                    ]
                } else if (!isFavorite) {
                    this.items = this.items.filter((item) => getFavoriteVariantId(item) !== normalizedId)
                }

                notificationStore.favorite(
                    isFavorite
                        ? 'Đã thêm vào yêu thích.'
                        : 'Đã bỏ khỏi yêu thích.'
                )

                return response
            } catch (error) {
                this.variantIds = wasFavorite
                    ? [...new Set([...this.variantIds, normalizedId])]
                    : this.variantIds.filter((id) => id !== normalizedId)

                throw error
            }
        },

    },
})
