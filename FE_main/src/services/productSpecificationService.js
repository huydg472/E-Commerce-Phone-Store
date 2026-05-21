import api from './api'

export const productSpecificationService = {
  getAll(params = {}) {
    return api.get('/product-specifications', { params })
  },

  getById(id) {
    return api.get(`/product-specifications/${id}`)
  },

  create(data) {
    return api.post('/product-specifications', data)
  },

  update(id, data) {
    return api.put(`/product-specifications/${id}`, data)
  },

  delete(id) {
    return api.delete(`/product-specifications/${id}`)
  },
}
