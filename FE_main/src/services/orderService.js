import api from './api'

export const orderService = {
  getAll(params = {}) {
    return api.get('/orders', { params })
  },

  getById(id) {
    return api.get(`/orders/${id}`)
  },

  create(data) {
    return api.post('/orders', data)
  },

  update(id, data) {
    return api.put(`/orders/${id}`, data)
  },

  delete(id) {
    return api.delete(`/orders/${id}`)
  },
}
