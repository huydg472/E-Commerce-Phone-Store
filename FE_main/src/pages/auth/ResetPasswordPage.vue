<script setup>
import {onMounted, reactive, ref} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()

const loading = ref(false)
const errorMessage = ref('')
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const form = reactive({
  password: '',
  password_confirmation: '',
})

onMounted(async () => {
  if (!route.query.email || !route.query.token) {
    await router.replace('/auth/forgot-password')
  }
})

const handleResetPassword = async () => {
  errorMessage.value = ''

  if (!form.password || !form.password_confirmation) {
    errorMessage.value = 'Vui lòng nhập đầy đủ mật khẩu.'
    return
  }

  if (form.password.length < 8) {
    errorMessage.value = 'Mật khẩu phải có ít nhất 8 ký tự.'
    return
  }

  if (form.password !== form.password_confirmation) {
    errorMessage.value = 'Mật khẩu xác nhận không khớp.'
    return
  }

  try {
    loading.value = true

    await api.post('/reset-password', {
      email: route.query.email,
      token: route.query.token,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })

    sessionStorage.setItem('reset_password_success', 'true')

    await router.replace('/auth/reset-password-success')
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data.errors

      if (errors) {
        const firstErrorKey = Object.keys(errors)[0]
        errorMessage.value = errors[firstErrorKey][0]
        return
      }

      errorMessage.value = 'Dữ liệu đặt lại mật khẩu không hợp lệ.'
      return
    }

    errorMessage.value = 'Liên kết không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="reset-page min-vh-100 d-flex align-items-center justify-content-center">
    <div class="reset-card">
      <div class="text-center">
        <div class="reset-icon mx-auto mb-4">
          <i class="bi bi-lock"></i>
          <i class="bi bi-arrow-clockwise refresh-icon"></i>
        </div>

        <h1 class="reset-title fw-bold mb-3">
          Đặt lại mật khẩu
        </h1>

        <p class="reset-desc mx-auto mb-5">
          Nhập mật khẩu mới cho tài khoản của bạn để hoàn tất việc đặt lại mật khẩu.
        </p>
      </div>

      <form @submit.prevent="handleResetPassword">
        <div class="mb-4">
          <label class="form-label fw-bold">Mật khẩu mới</label>

          <div class="input-group auth-input">
            <span class="input-group-text">
              <i class="bi bi-lock"></i>
            </span>

            <input v-model.trim="form.password" :type="showPassword ? 'text' : 'password'" class="form-control"
                   placeholder="Nhập mật khẩu mới"/>

            <button type="button" class="input-group-text eye-btn" @click="showPassword = !showPassword">
              <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-bold">Xác nhận mật khẩu</label>

          <div class="input-group auth-input">
            <span class="input-group-text">
              <i class="bi bi-lock"></i>
            </span>

            <input v-model.trim="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'"
                   class="form-control" placeholder="Nhập lại mật khẩu mới"/>

            <button type="button" class="input-group-text eye-btn" @click="showConfirmPassword = !showConfirmPassword">
              <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
        </div>

        <p v-if="errorMessage" class="text-danger small mb-3">
          {{ errorMessage }}
        </p>

        <button type="submit" class="btn btn-primary w-100 auth-btn" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
          {{ loading ? 'Đang đặt lại...' : 'Đặt lại mật khẩu' }}
        </button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.auth-input input::-ms-reveal,
.auth-input input::-ms-clear {
  display: none;
}

.reset-page {
  background: radial-gradient(circle at top left, rgba(0, 102, 255, 0.08), transparent 34%),
  radial-gradient(circle at bottom right, rgba(0, 102, 255, 0.08), transparent 34%),
  #f4f8ff;
  padding: 24px;
}

.reset-card {
  width: 100%;
  max-width: 760px;
  padding: 72px 86px;
  background: #ffffff;
  border: 1px solid #dfe8f5;
  border-radius: 18px;
  box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
}

.reset-icon {
  width: 112px;
  height: 112px;
  border-radius: 50%;
  background: #edf4ff;
  color: #0066ff;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  font-size: 52px;
}

.refresh-icon {
  position: absolute;
  right: 28px;
  bottom: 30px;
  font-size: 26px;
}

.reset-title {
  color: #061c46;
  font-size: 36px;
}

.reset-desc {
  max-width: 440px;
  color: #52627a;
  font-size: 18px;
  line-height: 1.6;
}

.form-label {
  font-size: 16px;
  color: #111827;
}

.auth-input {
  height: 58px;
  border: 1px solid #d8e1ee;
  border-radius: 10px;
  overflow: hidden;
  background: #ffffff;
}

.auth-input .input-group-text {
  background: #ffffff;
  border: none;
  color: #66789f;
  font-size: 20px;
  padding: 0 16px;
}

.auth-input .form-control {
  border: none;
  box-shadow: none;
  font-size: 16px;
}

.eye-btn {
  cursor: pointer;
}

.auth-btn {
  height: 64px;
  border-radius: 10px;
  background: #0066ff;
  border-color: #0066ff;
  font-size: 20px;
  font-weight: 700;
}

@media (max-width: 768px) {
  .reset-card {
    padding: 44px 24px;
  }
}
</style>