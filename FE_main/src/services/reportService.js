import api from './api'

export const reportService = {
    dashboard(params = {}) {
        return api.get('/reports/dashboard', {params})
    },

    revenue(params = {}) {
        return api.get('/reports/revenue', {params})
    },

    products(params = {}) {
        return api.get('/reports/products', {params})
    },

    orders(params = {}) {
        return api.get('/reports/orders', {params})
    },
}
