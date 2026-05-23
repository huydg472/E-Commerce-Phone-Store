import api from './api'

export const productImageService = {
    getAll(params = {}) {
        return api.get('/product-images', {params})
    },

    getById(id) {
        return api.get(`/product-images/${id}`)
    },

    create(data) {
        return api.post('/product-images', data)
    },

    update(id, data) {
        return api.put(`/product-images/${id}`, data)
    },

    delete(id) {
        return api.delete(`/product-images/${id}`)
    },
}
