<script setup>
import {reactive} from 'vue'

defineProps({
  loading: {
    type: Boolean,
    default: false
  },

  errorMessage: {
    type: String,
    default: ''
  },

  successMessage: {
    type: String,
    default: ''
  },
})

const emit = defineEmits(['submit-forgot-password'])

const form = reactive({
  email: '',
})

const submitForgotPassword = () => {
  emit('submit-forgot-password', {
    email: form.email,
  })
}
</script>

<template>
  <div class="forgot-form w-100">
    <RouterLink to="/auth/login"
                class="back-link d-inline-flex align-items-center gap-2 text-decoration-none fw-semibold mb-5">
      <i class="bi bi-arrow-left"></i>
      Quay lại đăng nhập
    </RouterLink>

    <div class="text-center forgot-content">
      <div class="forgot-icon mx-auto mb-4">
        <i class="bi bi-lock"></i>
        <i class="bi bi-key key-icon"></i>
      </div>

      <h1 class="forgot-title fw-bold mb-3">
        Quên mật khẩu
      </h1>

      <p class="forgot-desc mx-auto mb-5">
        Nhập email của bạn, chúng tôi sẽ gửi liên kết đặt lại mật khẩu về email đó.
      </p>
    </div>

    <form @submit.prevent="submitForgotPassword">
      <div class="mb-4">
        <label class="form-label fw-bold">Email</label>
        <div class="input-group auth-input">
          <span class="input-group-text">
            <i class="bi bi-envelope"></i>
          </span>
          <input v-model.trim="form.email" type="email" class="form-control" placeholder="Nhập email của bạn"/>
        </div>
      </div>

      <p v-if="errorMessage" class="text-danger small mb-3">
        {{ errorMessage }}
      </p>

      <p v-if="successMessage" class="text-success small mb-3">
        {{ successMessage }}
      </p>

      <button type="submit" class="btn btn-primary w-100 auth-btn" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
        {{ loading ? 'Đang gửi...' : 'Gửi liên kết đặt lại mật khẩu' }}
      </button>
    </form>
  </div>
</template>

<style scoped>
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

.auth-input:focus-within {
  border-color: #0066ff;
  box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.08);
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

.auth-input .form-control::placeholder {
  color: #7b8aad;
}

.auth-input input::-ms-reveal,
.auth-input input::-ms-clear {
  display: none;
}

.auth-btn {
  height: 64px;
  border-radius: 10px;
  background: #0066ff;
  border-color: #0066ff;
  font-size: 20px;
  font-weight: 700;
}

.auth-btn:hover {
  background: #0055d6;
  border-color: #0055d6;
}

.auth-btn:disabled {
  background: #7aadff;
  border-color: #7aadff;
  cursor: not-allowed;
}
</style>