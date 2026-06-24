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

    updateMe(data) {
        return api.put('/me', data)
    },

    forgotPassword(data) {
        return api.post('/forgot-password', data)
    },

    resetPassword(data) {
        return api.post('/reset-password', data)
    },

    sendVerificationNotification(data = {}) {
        return api.post('/email/verification-notification', data)
    },
}
