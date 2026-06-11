import api from './api'

export const paymentService = {
    getAll(params = {}) {
        return api.get('/payments', {params})
    },

    getById(id) {
        return api.get(`/payments/${id}`)
    },

    create(data) {
        return api.post('/payments', data)
    },

    createVnpayUrl(paymentId) {
        return api.post(`/payments/${paymentId}/vnpay-url`)
    },

    update(id, data) {
        return api.put(`/payments/${id}`, data)
    },

    delete(id) {
        return api.delete(`/payments/${id}`)
    },
}
