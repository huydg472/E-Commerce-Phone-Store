import axios from 'axios'
import {getToken} from '@/utils/storage'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
})

api.interceptors.request.use((config) => {
    const token = getToken()

    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }

    if (config.data instanceof FormData) {
        if (typeof config.headers?.delete === 'function') {
            config.headers.delete('Content-Type')
        } else if (config.headers) {
            delete config.headers['Content-Type']
        }
    }

    return config
})
api.interceptors.response.use(
    (response) => response,
    (error) => Promise.reject(error),
)

export default api
