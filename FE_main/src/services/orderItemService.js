import api from './api'

export const orderItemService = {
  getAll(params = {}) {
    return api.get('/order-items', { params })
  },

  getById(id) {
    return api.get(`/order-items/${id}`)
  },

  create(data) {
    return api.post('/order-items', data)
  },

  update(id, data) {
    return api.put(`/order-items/${id}`, data)
  },

  delete(id) {
    return api.delete(`/order-items/${id}`)
  },
}
