<script setup>
import {computed, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'

import LoginForm from '@/components/auth/LoginForm.vue'
import {useAuthStore} from '@/stores/authStore.js'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const infoMessage = ref('')
const verificationNoticePinned = ref(false)

const routeMessage = computed(() =>
    String(route.query.verified ?? route.query['verification-sent'] ?? route.query['verification-still-valid'] ?? ''),
)

watch(
    routeMessage,
    async (value) => {
      if (value === '1') {
        successMessage.value = 'Email của bạn đã được xác minh. Bạn có thể đăng nhập lại ngay bây giờ.'
        errorMessage.value = ''
        infoMessage.value = ''
        verificationNoticePinned.value = true

        if (route.query.verified || route.query['verification-sent'] || route.query['verification-still-valid']) {
          await router.replace({path: '/auth/login', query: {}})
        }

        return
      }

      if (!verificationNoticePinned.value) {
        successMessage.value = ''
      }
    },
    {immediate: true},
)

const handleLogin = async (formData) => {
  errorMessage.value = ''
  successMessage.value = ''
  infoMessage.value = ''
  verificationNoticePinned.value = false

  if (!formData.username || !formData.password) {
    errorMessage.value = 'Vui lòng nhập đầy đủ tài khoản và mật khẩu.'
    return
  }

  try {
    loading.value = true

    await authStore.login(formData)

    if (authStore.isAdmin || authStore.isStaff) {
      await router.replace('/admin/dashboard')
      return
    }

    if (authStore.isCustomer) {
      await router.replace('/')
      return
    }

    await router.replace('/')
  } catch (error) {
    if (error.response?.status === 401) {
      errorMessage.value = 'Tài khoản hoặc mật khẩu không đúng.'
      return
    }

    if (error.response?.status === 409 || error.response?.data?.verification_required) {
      infoMessage.value =
          error.response?.data?.message ||
          'Email chưa được xác minh. Vui lòng kiểm tra hộp thư để xác minh tài khoản.'
      errorMessage.value = ''
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
  <div>
    <div v-if="successMessage" class="alert alert-success mb-4">
      {{ successMessage }}
    </div>

    <div v-if="infoMessage" class="alert alert-info mb-4">
      {{ infoMessage }}
    </div>

    <LoginForm :loading="loading" :error-message="errorMessage" @submit-login="handleLogin"/>
  </div>
</template>

<style scoped></style>
