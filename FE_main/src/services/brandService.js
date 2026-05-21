import api from './api'

export const brandService = {
  getAll(params = {}) {
    return api.get('/brands', { params })
  },

  getById(id) {
    return api.get(`/brands/${id}`)
  },

  create(data) {
    return api.post('/brands', data)
  },

  update(id, data) {
    return api.put(`/brands/${id}`, data)
  },

  delete(id) {
    return api.delete(`/brands/${id}`)
  },
}
