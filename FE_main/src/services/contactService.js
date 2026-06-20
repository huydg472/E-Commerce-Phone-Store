import api from './api'
import {API_ENDPOINTS} from '@/constants/apiEndpoints'

export const contactService = {
    submit(data) {
        return api.post(API_ENDPOINTS.CONTACT_MESSAGES, data)
    },
}
