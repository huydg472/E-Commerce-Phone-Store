<script setup>
import {ref} from 'vue'
import {useRouter} from 'vue-router'
import LoginForm from '@/components/auth/LoginForm.vue'
import {useAuthStore} from '@/stores/authStore.js'

const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const errorMessage = ref('')

const handleLogin = async (formData) => {
  console.log('Đã nhận dữ liệu từ LoginForm:', formData)

  errorMessage.value = ''

  if (!formData.username || !formData.password) {
    errorMessage.value = 'Vui lòng nhập đầy đủ tài khoản hoặc mật khẩu'
    return
  }

  try {
    loading.value = true

    const response = await authStore.login({
      username: formData.username,
      password: formData.password,
      remember: formData.remember,
    })

    console.log('Response login:', response)
    console.log('Auth store user:', authStore.user)

    const user = authStore.user

    const roleId = Number(user?.role_id)

    if (roleId === 1 || roleId === 2) {
      router.push('/admin/dashboard')
      return
    }

    if (roleId === 3) {
      router.push('/')
      return
    }

    console.warn('Không xác định được role, chuyển về home:', user)
    router.push('/')
  } catch (error) {
    console.log('Lỗi đăng nhập:', error.response?.data || error)

    if (error.response?.status === 401) {
      errorMessage.value = 'Tài khoản hoặc mật khẩu không đúng.'
      return
    }

    if (error.response?.status === 422) {
      errorMessage.value = 'Dữ liệu đăng nhập không hợp lệ.'
      return
    }

    errorMessage.value = 'Đăng nhập thất bại. Vui lòng thử lại.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <LoginForm
      :loading="loading"
      :error-message="errorMessage"
      @submit-login="handleLogin"
  />
</template>