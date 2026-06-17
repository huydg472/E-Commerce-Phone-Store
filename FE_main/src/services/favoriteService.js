import api from './api'

export const favoriteService = {
    getAll(params = {}) {
        return api.get('/favorites', {params})
    },

    toggle(productVariantId) {
        return api.post(`/favorites/${productVariantId}/toggle`)
    },
}
