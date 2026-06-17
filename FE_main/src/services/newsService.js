import api from './api'

export const newsService = {
    getPublicCategories(params = {}) {
        return api.get('/news/categories', {params})
    },

    getPublicCategory(slug) {
        return api.get(`/news/categories/${encodeURIComponent(slug)}`)
    },

    getPublicPosts(params = {}) {
        return api.get('/news/posts', {params})
    },

    getPublicPost(slug) {
        return api.get(`/news/posts/${encodeURIComponent(slug)}`)
    },

    getAdminCategories(params = {}) {
        return api.get('/admin/news/categories', {params})
    },

    getAdminCategory(id) {
        return api.get(`/admin/news/categories/${id}`)
    },

    createCategory(data) {
        return api.post('/admin/news/categories', data)
    },

    updateCategory(id, data) {
        return api.put(`/admin/news/categories/${id}`, data)
    },

    toggleCategoryStatus(id) {
        return api.patch(`/admin/news/categories/${id}/toggle-status`)
    },

    deleteCategory(id) {
        return api.delete(`/admin/news/categories/${id}`)
    },

    getAdminPosts(params = {}) {
        return api.get('/admin/news/posts', {params})
    },

    getAdminPost(id) {
        return api.get(`/admin/news/posts/${id}`)
    },

    createPost(data) {
        return api.post('/admin/news/posts', data)
    },

    updatePost(id, data) {
        return api.put(`/admin/news/posts/${id}`, data)
    },

    togglePostStatus(id) {
        return api.patch(`/admin/news/posts/${id}/toggle-status`)
    },

    deletePost(id) {
        return api.delete(`/admin/news/posts/${id}`)
    },
}
