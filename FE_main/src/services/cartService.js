import api from './api'

export const cartService = {
    getAll(params = {}) {
        return api.get('/carts', {params})
    },

    getById(id) {
        return api.get(`/carts/${id}`)
    },

    create(data) {
        return api.post('/carts', data)
    },
    s
    update(id, data) {
        return api.put(`/carts/${id}`, data)
    },

    delete(id) {
        return api.delete(`/carts/${id}`)
    },
}
