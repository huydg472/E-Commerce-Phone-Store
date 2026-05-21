import api from './api'

export const authService = {
  login(data) {
    return api.post('/login', data)
  },

  register(data) {
    return api.post('/register', data)
  },

  logout() {
    return api.post('/logout')
  },

  me() {
    return api.get('/me')
  },
}
