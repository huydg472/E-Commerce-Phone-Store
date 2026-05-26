<script setup>
import { reactive } from "vue";

defineProps({
  loading: {type: Boolean, default: false},
  errorMessage: {type: String, default: ''},
})

const emit = defineEmits(["submit-login"]);

const form = reactive({
  username: '',
  password: '',
  remember: false,
})

const submitLogin = () => {
  emit("submit-login", {
    username: form.username,
    password: form.password,
    remember: form.remember,
  });
}
</script>

<template>
  <div class="login-form w-100">
    <!-- Tabs -->
    <div class="row border-bottom mb-5">
      <div class="col-6 text-center">
        <RouterLink to="/auth/login" class="tab-link active d-block text-decoration-none fw-bold">
          Đăng nhập
        </RouterLink>
      </div>

      <div class="col-6 text-center">
        <RouterLink to="/auth/register" class="tab-link d-block text-decoration-none fw-bold">
          Tạo tài khoản
        </RouterLink>
      </div>
    </div>

    <form @submit.prevent="submitLogin">
      <div class="mb-4">
        <label class="form-label fw-bold">Tên đăng nhập</label>

        <div class="input-group auth-input">
                    <span class="input-group-text">
                        <i class="bi bi-person"></i>
                    </span>

          <input v-model.trim="form.username" type="text" class="form-control" placeholder="Nhập tên đăng nhập"/>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold">Mật khẩu</label>

        <div class="input-group auth-input">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>

          <input v-model.trim="form.password" type="password"
                 class="form-control" placeholder="Nhập mật khẩu"/>
        </div>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
          <input v-model="form.remember" class="form-check-input" type="checkbox" id="rememberLogin"/>

          <label class="form-check-label" for="rememberLogin">
            Ghi nhớ đăng nhập
          </label>
        </div>

        <RouterLink to="/auth/forgot-password" class="forgot-link text-decoration-none fw-bold">
          Quên mật khẩu?
        </RouterLink>
      </div>

      <p v-if="errorMessage" class="text-danger small mb-3">
        {{ errorMessage }}
      </p>

      <button type="submit" class="btn btn-primary w-100 auth-btn" :disabled="loading">
        {{ loading ? 'Đang đăng nhập...' : 'Đăng nhập' }}
      </button>

      <p class="text-center mt-4 mb-0 text-secondary">
        Chưa có tài khoản?
        <RouterLink to="/auth/register" class="fw-bold text-decoration-none">
          Tạo tài khoản ngay
        </RouterLink>
      </p>
    </form>
  </div>
</template>

<style scoped>
.login-form {
  max-width: 680px;
  margin-top: 6px;
}

.tab-link {
  padding: 14px 0 20px;
  color: #334155;
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
  margin-bottom: 10px;
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
  padding: 0 18px;
}

.auth-input .form-control {
  border: none;
  box-shadow: none;
  font-size: 16px;
}

.auth-input .form-control::placeholder {
  color: #7b8aad;
}

.form-check-input:checked {
  background-color: #0066ff;
  border-color: #0066ff;
}

.form-check-label {
  color: #334155;
  font-size: 15px;
  margin-left: 4px;
}

.forgot-link {
  color: #0066ff;
  font-size: 15px;
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