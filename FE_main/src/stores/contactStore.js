import {defineStore} from 'pinia'
import {contactService} from '@/services/contactService'
import {useNotificationStore} from '@/stores/notificationStore.js'

const normalizeErrors = (errors) => {
    return errors && typeof errors === 'object' ? errors : {}
}

export const useContactStore = defineStore('contact', {
    state: () => ({
        loading: false,
        successMessage: '',
        errorMessage: '',
        fieldErrors: {},
    }),

    actions: {
        clearFeedback() {
            this.successMessage = ''
            this.errorMessage = ''
            this.fieldErrors = {}
        },

        async submit(payload) {
            this.loading = true
            this.clearFeedback()

            try {
                const response = await contactService.submit(payload)
                this.successMessage = response.data?.message || 'Yeu cau da duoc tiep nhan.'
                useNotificationStore().success(this.successMessage)
                return response
            } catch (error) {
                const isValidationError = error.response?.status === 422
                const message = isValidationError
                    ? 'Vui long kiem tra lai cac truong da nhap.'
                    : (error.response?.data?.message || 'Gui yeu cau that bai.')

                this.errorMessage = message
                this.fieldErrors = normalizeErrors(error.response?.data?.errors)

                if (!isValidationError) {
                    useNotificationStore().error(message)
                }

                throw error
            } finally {
                this.loading = false
            }
        },
    },
})
