<script setup>
import {reactive, ref} from 'vue'

const form = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const errors = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
})

const successMessage = ref('')
const loading = ref(false)

const clearErrors = () => {
  errors.currentPassword = ''
  errors.newPassword = ''
  errors.confirmPassword = ''
}

const validate = () => {
  clearErrors()

  let isValid = true

  if (!form.currentPassword.trim()) {
    errors.currentPassword = 'Vui lòng nhập mật khẩu hiện tại.'
    isValid = false
  }

  if (!form.newPassword.trim()) {
    errors.newPassword = 'Vui lòng nhập mật khẩu mới.'
    isValid = false
  } else if (form.newPassword.length < 8) {
    errors.newPassword = 'Mật khẩu mới phải có ít nhất 8 ký tự.'
    isValid = false
  }

  if (form.confirmPassword !== form.newPassword) {
    errors.confirmPassword = 'Xác nhận mật khẩu không khớp.'
    isValid = false
  }

  return isValid
}

const resetForm = () => {
  form.currentPassword = ''
  form.newPassword = ''
  form.confirmPassword = ''
}

const handleSubmit = async () => {
  successMessage.value = ''

  if (!validate()) {
    return
  }

  loading.value = true

  try {
    await new Promise((resolve) => setTimeout(resolve, 400))
    successMessage.value = 'Đã cập nhật mật khẩu thành công.'
    resetForm()
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="account-page">
    <nav class="account-breadcrumb mb-2">
      <span>Trang chủ</span>
      <span>/</span>
      <span>Tài khoản của tôi</span>
      <span>/</span>
      <strong>Đổi mật khẩu</strong>
    </nav>

    <div class="page-head">
      <div>
        <h1 class="page-title mb-1">Đổi mật khẩu</h1>
        <p class="page-subtitle mb-0">Tạo mật khẩu đủ mạnh để bảo vệ tài khoản của bạn.</p>
      </div>
    </div>

    <div v-if="successMessage" class="success-banner mb-3">
      <i class="bi bi-check-circle"></i>
      <span>{{ successMessage }}</span>
    </div>

    <div class="row g-3">
      <div class="col-xl-7">
        <section class="account-card h-100">
          <h5 class="section-title mb-3">Biểu mẫu đổi mật khẩu</h5>

          <form @submit.prevent="handleSubmit">
            <div class="form-group">
              <label class="form-label">Mật khẩu hiện tại</label>
              <input v-model="form.currentPassword" type="password" class="form-control"
                     :class="{ invalid: errors.currentPassword }" placeholder="Nhập mật khẩu hiện tại"/>
              <small v-if="errors.currentPassword" class="field-error">{{ errors.currentPassword }}</small>
            </div>

            <div class="form-group">
              <label class="form-label">Mật khẩu mới</label>
              <input v-model="form.newPassword" type="password" class="form-control"
                     :class="{ invalid: errors.newPassword }" placeholder="Ít nhất 8 ký tự"/>
              <small v-if="errors.newPassword" class="field-error">{{ errors.newPassword }}</small>
            </div>

            <div class="form-group">
              <label class="form-label">Xác nhận mật khẩu mới</label>
              <input v-model="form.confirmPassword" type="password" class="form-control"
                     :class="{ invalid: errors.confirmPassword }" placeholder="Nhập lại mật khẩu mới"/>
              <small v-if="errors.confirmPassword" class="field-error">{{ errors.confirmPassword }}</small>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn btn-primary" :disabled="loading">
                <span v-if="loading" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                Lưu thay đổi
              </button>
              <button type="button" class="btn btn-light border" @click="resetForm">
                Nhập lại
              </button>
            </div>
          </form>
        </section>
      </div>

      <div class="col-xl-5">
        <section class="account-card h-100">
          <h5 class="section-title mb-3">Lưu ý bảo mật</h5>

          <ul class="security-list">
            <li>Không dùng lại mật khẩu cũ đã xuất hiện ở các dịch vụ khác.</li>
            <li>Nên kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt.</li>
            <li>Không chia sẻ mật khẩu cho bất kỳ ai.</li>
            <li>Đăng xuất khỏi thiết bị lạ sau khi đổi mật khẩu.</li>
          </ul>

          <div class="security-box">
            <strong>Gợi ý:</strong>
            <p>Đổi mật khẩu định kỳ mỗi 3 đến 6 tháng để giảm rủi ro lộ tài khoản.</p>
          </div>
        </section>
      </div>
    </div>
  </section>
</template>

<style scoped>
.account-page {
  color: #0f172a;
  font-size: 14px;
}

.account-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6b7280;
  font-size: 14px;
}

.account-breadcrumb strong {
  color: #2563eb;
  font-weight: 600;
}

.page-head {
  margin-bottom: 18px;
}

.page-title {
  font-size: 28px;
  font-weight: 750;
  color: #111827;
}

.page-subtitle {
  color: #64748b;
}

.account-card {
  padding: 18px 22px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
}

.section-title {
  font-size: 17px;
  font-weight: 750;
  color: #111827;
}

.success-banner {
  padding: 12px 14px;
  border: 1px solid #bbf7d0;
  border-radius: 12px;
  background: #f0fdf4;
  color: #166534;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
}

.form-group + .form-group {
  margin-top: 14px;
}

.form-label {
  margin-bottom: 6px;
  font-weight: 600;
  color: #334155;
}

.form-control {
  height: 42px;
  border: 1px solid #d8dee9;
  border-radius: 10px;
  box-shadow: none;
}

.form-control:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.08);
}

.form-control.invalid {
  border-color: #dc2626;
}

.field-error {
  display: block;
  margin-top: 6px;
  color: #dc2626;
}

.form-actions {
  display: flex;
  gap: 12px;
  margin-top: 18px;
}

.form-actions .btn {
  min-width: 140px;
  height: 42px;
  border-radius: 10px;
  font-weight: 700;
}

.security-list {
  margin: 0;
  padding-left: 18px;
  color: #334155;
  line-height: 1.7;
}

.security-list li + li {
  margin-top: 8px;
}

.security-box {
  margin-top: 18px;
  padding: 14px 16px;
  border: 1px solid #dbeafe;
  border-radius: 12px;
  background: #eff6ff;
}

.security-box strong {
  display: block;
  margin-bottom: 6px;
  color: #1d4ed8;
}

.security-box p {
  margin: 0;
  color: #334155;
  line-height: 1.6;
}

@media (max-width: 768px) {
  .page-title {
    font-size: 24px;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions .btn {
    width: 100%;
  }
}
</style>
