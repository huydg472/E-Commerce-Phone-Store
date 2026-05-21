import api from './api'

export const stockLogService = {
  getAll(params = {}) {
    return api.get('/stock-logs', { params })
  },

  getById(id) {
    return api.get(`/stock-logs/${id}`)
  },

  create(data) {
    return api.post('/stock-logs', data)
  },

  update(id, data) {
    return api.put(`/stock-logs/${id}`, data)
  },

  delete(id) {
    return api.delete(`/stock-logs/${id}`)
  },
}
