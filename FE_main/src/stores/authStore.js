import { defineStore } from 'pinia'
import { authService } from '@/services/authService'
import { getToken, removeToken, setToken } from '@/utils/storage'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: getToken(),
    loading: false,
  }),

  getters: {
    isLoggedIn: (state) => Boolean(state.token),
    isAdmin: (state) => {
      return state.user?.role?.name === 'admin' || state.user?.role === 'admin'
    },
  },

  actions: {
    async login(payload) {
      this.loading = true

      try {
        const response = await authService.login(payload)
        const token = response.data.token || response.data.access_token

        if (token) {
          this.token = token
          setToken(token)
        }

        this.user = response.data.user || response.data.data || null
        return response
      } finally {
        this.loading = false
      }
    },

    async fetchMe() {
      const response = await authService.me()
      this.user = response.data.data || response.data.user || response.data
      return response
    },

    async logout() {
      try {
        await authService.logout()
      } finally {
        this.user = null
        this.token = null
        removeToken()
      }
    },
  },
})
