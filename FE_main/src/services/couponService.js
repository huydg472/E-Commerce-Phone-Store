import api from './api'

export const couponService = {
    getAll(params = {}) {
        return api.get('/coupons', {params})
    },

    getById(id) {
        return api.get(`/coupons/${id}`)
    },

    create(data) {
        return api.post('/coupons', data)
    },

    update(id, data) {
        return api.put(`/coupons/${id}`, data)
    },

    toggleStatus(id) {
        return api.patch(`/coupons/${id}/toggle-status`)
    },

    delete(id) {
        return api.delete(`/coupons/${id}`)
    },

    apply(data) {
        return api.post('/coupons/apply', data)
    },
}

