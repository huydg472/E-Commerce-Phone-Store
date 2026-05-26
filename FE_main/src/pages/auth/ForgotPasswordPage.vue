<script setup>
import {ref} from 'vue'
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue'
import api from '@/services/api'

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const handleForgotPassword = async (formData) => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!formData.email) {
    errorMessage.value = 'Vui lòng nhập email.'
    return
  }

  try {
    loading.value = true

    await api.post('/forgot-password', {
      email: formData.email,
    })

    successMessage.value = 'Liên kết đặt lại mật khẩu đã được gửi về email của bạn.'
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors

      if (errors) {
        const firstErrorKey = Object.keys(errors)[0]
        errorMessage.value = errors[firstErrorKey][0]
        return
      }

      errorMessage.value = 'Email không hợp lệ.'
      return
    }

    errorMessage.value = 'Không thể gửi liên kết đặt lại mật khẩu. Vui lòng thử lại.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <ForgotPasswordForm
      :loading="loading"
      :error-message="errorMessage"
      :success-message="successMessage"
      @submit-forgot-password="handleForgotPassword"
  />
</template>

<style scoped></style>