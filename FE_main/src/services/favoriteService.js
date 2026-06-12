import api from './api'

export const favoriteService = {
    getAll(params = {}) {
        return api.get('/favorites', {params})
    },

    create(productVariantId) {
        return api.post('/favorites', {product_variant_id: productVariantId})
    },

    status(productVariantId) {
        return api.get(`/favorites/${productVariantId}/status`)
    },

    toggle(productVariantId) {
        return api.post(`/favorites/${productVariantId}/toggle`)
    },

    delete(productVariantId) {
        return api.delete(`/favorites/${productVariantId}`)
    },
}
