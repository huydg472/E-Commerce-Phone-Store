import api from './api'

export const cartService = {
    getAll(params = {}) {
        return api.get('/cart', {params})
    },

    getById(id) {
        return api.get(`/cart/${id}`)
    },

    create(data) {
        return api.post('/cart', data)
    },

    update(id, data) {
        return api.put(`/cart/${id}`, data)
    },

    delete(id) {
        return api.delete(`/cart/${id}`)
    },
}
