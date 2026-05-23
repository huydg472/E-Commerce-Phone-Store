import api from './api'

export const shippingAddressService = {
    getAll(params = {}) {
        return api.get('/shipping-addresses', {params})
    },

    getById(id) {
        return api.get(`/shipping-addresses/${id}`)
    },

    create(data) {
        return api.post('/shipping-addresses', data)
    },

    update(id, data) {
        return api.put(`/shipping-addresses/${id}`, data)
    },

    delete(id) {
        return api.delete(`/shipping-addresses/${id}`)
    },
}
