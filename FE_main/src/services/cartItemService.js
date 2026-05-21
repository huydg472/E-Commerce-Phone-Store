import api from './api'

export const cartItemService = {
  getAll(params = {}) {
    return api.get('/cart-items', { params })
  },

  getById(id) {
    return api.get(`/cart-items/${id}`)
  },

  create(data) {
    return api.post('/cart-items', data)
  },

  update(id, data) {
    return api.put(`/cart-items/${id}`, data)
  },

  delete(id) {
    return api.delete(`/cart-items/${id}`)
  },
}
