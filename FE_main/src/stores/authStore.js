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
        roleName: (state) => {
            return state.user?.role?.name || state.user?.role_name || null
        },

        isLoggedIn: (state) => Boolean(state.token),

        isAdmin: (state) => state.user?.role?.name === 'admin' || state.user?.role_name === 'admin',

        isStaff: (state) => state.user?.role?.name === 'staff' || state.user?.role_name === 'staff',

        isCustomer: (state) => state.user?.role?.name === 'customer' || state.user?.role_name === 'customer',
    },

    actions: {
        clearSession() {
            this.user = null
            this.token = null

            removeToken()
            removeUser()
        },

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
                this.clearSession()
            }
        },
    },
})
