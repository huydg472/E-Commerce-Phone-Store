import {defineStore} from 'pinia'
import {authService} from '@/services/authService'
import {getToken, removeToken, setToken, getUser, setUser, removeUser} from '@/utils/storage'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: getUser(),
        token: getToken(),
        loading: false,
    }),

    getters: {
        isLoggedIn: (state) => Boolean(state.token),

        isAdmin: (state) => {
            return Number(state.user?.['role_id']) === 1
        },

        isStaff: (state) => {
            return Number(state.user?.['role_id']) === 2
        },

        isCustomer: (state) => {
            return Number(state.user?.['role_id']) === 3
        },
    },

    actions: {
        async login(payload) {
            this.loading = true

            try {
                const response = await authService.login(payload)

                const token = response.data.token || response.data.access_token
                const user = response.data.user || response.data.data || null

                if (token) {
                    this.token = token
                    setToken(token)
                }

                if (user) {
                    this.user = user
                    setUser(user)
                }

                return response
            } finally {
                this.loading = false
            }
        },

        async fetchMe() {
            const response = await authService.me()

            const user = response.data.user || response.data.data || response.data

            this.user = user
            setUser(user)

            return response
        },

        async logout() {
            try {
                await authService.logout()
            } finally {
                this.user = null
                this.token = null

                removeToken()
                removeUser()
            }
        },
    },
})