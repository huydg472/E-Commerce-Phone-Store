<script setup>
import {reactive, ref} from "vue";

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
  }
})

const emit = defineEmits(['submit-register'])

const showPassword = ref(false)
const showConfirmPassword = ref(false)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  username: '',
  password: '',
  password_confirmation: ''
})

const submitRegister = () => {
  emit('submit-register', {
    name: form.name,
    email: form.email,
    phone: form.phone,
    username: form.username,
    password: form.password,
    password_confirmation: form.password_confirmation
  })
}

</script>

<template>
  <div class="register-form w-100">
    <!-- Tabs -->
    <div class="row border-bottom mb-4">
      <div class="col-6 text-center">
        <RouterLink to="/auth/login" class="tab-link d-block text-decoration-none fw-bold">
          Đăng nhập
        </RouterLink>
      </div>

      <div class="col-6 text-center">
        <RouterLink to="/auth/register" class="tab-link active d-block text-decoration-none fw-bold">
          Tạo tài khoản
        </RouterLink>
      </div>
    </div>

    <form @submit.prevent="submitRegister">
      <!-- Họ tên -->
      <div class="mb-4">
        <label class="form-label fw-bold">Họ và tên</label>
        <div class="input-group auth-input">
          <span class="input-group-text">
            <i class="bi bi-person-badge"></i>
          </span>
          <input v-model.trim="form.name" type="text" class="form-control" placeholder="Nhập họ và tên của bạn"/>
        </div>
      </div>

      <!-- Email -->
      <div class="mb-4">
        <label class="form-label fw-bold">Email</label>
        <div class="input-group auth-input">
          <span class="input-group-text">
            <i class="bi bi-envelope"></i>
          </span>
          <input v-model.trim="form.email" type="email" class="form-control" placeholder="Nhập email của bạn"/>
        </div>
      </div>

      <!-- Số điện thoại -->
      <div class="mb-4">
        <label class="form-label fw-bold">Số điện thoại</label>
        <div class="input-group auth-input">
          <span class="input-group-text">
            <i class="bi bi-telephone"></i>
          </span>
          <input v-model.trim="form.phone" type="text" class="form-control" placeholder="Nhập số điện thoại"/>
        </div>
      </div>

      <!-- Tên đăng nhập -->
      <div class="mb-4">
        <label class="form-label fw-bold">Tên đăng nhập</label>
        <div class="input-group auth-input">
          <span class="input-group-text">
            <i class="bi bi-person"></i>
          </span>
          <input v-model.trim="form.username" type="text" class="form-control" placeholder="Nhập tên đăng nhập"/>
        </div>
      </div>

      <!-- Mật khẩu -->
      <div class="mb-4">
        <label class="form-label fw-bold">Mật khẩu</label>
        <div class="input-group auth-input">
          <span class="input-group-text">
            <i class="bi bi-lock"></i>
          </span>
          <input v-model.trim="form.password" :type="showPassword ? 'text' : 'password'" class="form-control"
                 placeholder="Nhập mật khẩu"/>
          <button type="button" class="input-group-text eye-btn" @click="showPassword = !showPassword">
            <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
          </button>
        </div>
      </div>

      <!-- Xác nhận mật khẩu -->
      <div class="mb-3">
        <label class="form-label fw-bold">Xác nhận mật khẩu</label>

        <div class="input-group auth-input">
          <span class="input-group-text">
            <i class="bi bi-lock"></i>
          </span>

          <input v-model.trim="form.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'"
                 class="form-control" placeholder="Nhập lại mật khẩu"/>

          <button type="button" class="input-group-text eye-btn" @click="showConfirmPassword = !showConfirmPassword">
            <i :class="showConfirmPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
          </button>
        </div>
      </div>

      <!-- Message -->
      <p v-if="errorMessage" class="text-danger small mb-3">
        {{ errorMessage }}
      </p>

      <p v-if="successMessage" class="text-success small mb-3">
        {{ successMessage }}
      </p>

      <button type="submit" class="btn btn-primary w-100 auth-btn" :disabled="loading">
        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
        {{ loading ? 'Đang tạo tài khoản...' : 'Tạo tài khoản' }}
      </button>

      <p class="text-center mt-3 mb-0 text-secondary">
        Đã có tài khoản?
        <RouterLink to="/auth/login" class="fw-bold text-decoration-none">
          Đăng nhập ngay
        </RouterLink>
      </p>
    </form>
  </div>
</template>

<style scoped>
.auth-input input::-ms-reveal,
.auth-input input::-ms-clear {
  display: none;
}

.register-form {
  max-width: 620px;
}

.tab-link {
  padding: 14px 0 18px;
  color: #061c46;
  font-size: 18px;
  position: relative;
}

.tab-link.active {
  color: #0066ff;
}

.tab-link.active::after {
  content: '';
  position: absolute;
  left: 0;
  right: 0;
  bottom: -1px;
  height: 2px;
  background: #0066ff;
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

.auth-input:focus-within {
  border-color: #0066ff;
  box-shadow: 0 0 0 3px rgba(0, 102, 255, 0.08);
}

.auth-input .input-group-text {
  background: #ffffff;
  border: none;
  color: #66789f;
  font-size: 18px;
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

.eye-btn {
  cursor: pointer;
}

.auth-btn {
  height: 58px;
  border-radius: 10px;
  background: #0066ff;
  border-color: #0066ff;
  font-size: 17px;
  font-weight: 700;
}
</style>