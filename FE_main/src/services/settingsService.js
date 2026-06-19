import api from './api'

export const settingsService = {
    getPublic() {
        return api.get('/settings')
    },

    getAdmin() {
        return api.get('/admin/settings')
    },

    update(data) {
        return api.put('/admin/settings', data)
    },
}
