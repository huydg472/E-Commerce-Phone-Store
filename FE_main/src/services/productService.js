import api from './api'

export const productService = {
    getAll(params = {}) {
        return api.get('/products', {params})
    },

    getById(id) {
        return api.get(`/products/${id}`)
    },

    getBySlug(slug) {
        return api.get(`/products/by-slug/${encodeURIComponent(slug)}`)
    },

    create(data) {
        return api.post('/products', data)
    },

    update(id, data) {
        if (data instanceof FormData) {
            data.append('_method', 'PUT')
            return api.post(`/products/${id}`, data)
        }

        return api.put(`/products/${id}`, data)
    },

    delete(id) {
        return api.delete(`/products/${id}`)
    },
}
