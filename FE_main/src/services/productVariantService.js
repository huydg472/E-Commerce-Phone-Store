import api from './api'

export const productVariantService = {
  getAll(params = {}) {
    return api.get('/product-variants', { params })
  },

  getById(id) {
    return api.get(`/product-variants/${id}`)
  },

  create(data) {
    return api.post('/product-variants', data)
  },

  update(id, data) {
    return api.put(`/product-variants/${id}`, data)
  },

  delete(id) {
    return api.delete(`/product-variants/${id}`)
  },
}
