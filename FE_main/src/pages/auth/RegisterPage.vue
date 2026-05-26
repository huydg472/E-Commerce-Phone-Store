<script setup>
import {ref} from 'vue';
import {useRouter} from 'vue-router'

import RegisterForm from '@/components/auth/RegisterForm.vue';
import {authService} from "@/services/authService.js";

const router = useRouter();

const loading = ref(false);

const errorMessage = ref('');
const successMessage = ref('')

const handleRegister = async (formData) => {
  errorMessage.value = '';
  successMessage.value = ''

  if (!formData.name || !formData.email || !formData.phone || !formData.username || !formData.password || !formData.password_confirmation) {
    errorMessage.value = 'Vui lòng nhập đầy đủ thông tin';
    return
  }

  if (formData.password !== formData.password_confirmation) {
    errorMessage.value = 'Mật khẩu xác nhận không đúng';
    return
  }

  try {
    loading.value = true

    await authService.register({
      name: formData.name,
      email: formData.email,
      phone: formData.phone,
      username: formData.username,
      password: formData.password,
      password_confirmation: formData.password_confirmation,

    })
    successMessage.value = 'Đăng ký tài khoản thành công'

    setTimeout(async () => {
      await router.push('/auth/login')
    }, 800)
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors

      if (errors) {
        const firstErrorKey = Object.keys(errors)[0]
        errorMessage.value = errors[firstErrorKey][0]
        return
      }

      errorMessage.value = 'Dữ liệu đăng ký không hợp lệ.'
      return
    }

    errorMessage.value = 'Đăng ký thất bại. Vui lòng thử lại.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <RegisterForm
      :loading="loading"
      :error-message="errorMessage"
      :success-message="successMessage"
      @submit-register="handleRegister"
  />
</template>

<style scoped></style>
