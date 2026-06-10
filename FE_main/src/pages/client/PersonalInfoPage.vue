<script setup>
import {reactive, ref, watch} from 'vue'
import {useAuthStore} from '@/stores/authStore'
import {setUser} from '@/utils/storage'

const authStore = useAuthStore()
const savedMessage = ref('')

const profileFallback = {
  name: 'Nguyễn Văn A',
  phone: '0901 234 567',
  email: 'nguyenvana@gmail.com',
}

const form = reactive({
  name: '',
  birthday: '15/06/1990',
  email: '',
  phone: '',
  gender: 'Nam',
})

const syncForm = (user = {}) => {
  form.name = user.name || user.full_name || user.username || profileFallback.name
  form.email = user.email || profileFallback.email
  form.phone = user.phone || user.phone_number || profileFallback.phone
  form.birthday = user.birthday || '15/06/1990'
  form.gender = user.gender || 'Nam'
}

watch(
    () => authStore.user,
    (user) => {
      syncForm(user ?? {})
    },
    {immediate: true, deep: true}
)

const saveProfile = () => {
  const nextUser = {
    ...(authStore.user || {}),
    name: form.name,
    email: form.email,
    phone: form.phone,
    birthday: form.birthday,
    gender: form.gender,
  }

  authStore.user = nextUser
  setUser(nextUser)
  savedMessage.value = 'Đã lưu thay đổi hồ sơ.'
}
</script>

<template>
  <section class="account-page">
    <nav class="account-breadcrumb mb-2">
      <span>Trang chủ</span>
      <span>/</span>
      <span>Tài khoản của tôi</span>
      <span>/</span>
      <strong>Thông tin cá nhân</strong>
    </nav>

    <div class="page-head">
      <div>
        <h1 class="page-title mb-1">Thông tin cá nhân</h1>
        <p class="page-subtitle mb-0">Cập nhật tên hiển thị, liên hệ và ảnh đại diện của bạn.</p>
      </div>
    </div>

    <div v-if="savedMessage" class="success-banner mb-3">
      <i class="bi bi-check-circle"></i>
      <span>{{ savedMessage }}</span>
    </div>

    <div class="row g-3">
      <div class="col-12">
        <section class="account-card h-100">
          <h5 class="section-title mb-3">Biểu mẫu chỉnh sửa</h5>

          <form @submit.prevent="saveProfile">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">
                  Họ và tên <span>*</span>
                </label>
                <input v-model="form.name" type="text" class="form-control"/>
              </div>

              <div class="col-md-6">
                <label class="form-label">Ngày sinh</label>
                <div class="input-icon">
                  <input v-model="form.birthday" type="text" class="form-control"/>
                  <i class="bi bi-calendar-event"></i>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">
                  Email <span>*</span>
                </label>
                <input v-model="form.email" type="email" class="form-control"/>
              </div>

              <div class="col-md-6">
                <label class="form-label">Giới tính</label>

                <div class="gender-group">
                  <label>
                    <input v-model="form.gender" type="radio" value="Nam"/>
                    Nam
                  </label>

                  <label>
                    <input v-model="form.gender" type="radio" value="Nữ"/>
                    Nữ
                  </label>

                  <label>
                    <input v-model="form.gender" type="radio" value="Khác"/>
                    Khác
                  </label>
                </div>
              </div>

              <div class="col-md-6">
                <label class="form-label">
                  Số điện thoại <span>*</span>
                </label>
                <input v-model="form.phone" type="text" class="form-control"/>
              </div>
            </div>

            <div class="form-actions mt-3">
              <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i>
                Lưu thay đổi
              </button>

              <button type="button" class="btn btn-light border" @click="syncForm(authStore.user || {})">
                Hủy bỏ
              </button>
            </div>
          </form>
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

.form-label {
  font-size: 14px;
  font-weight: 600;
  color: #334155;
  margin-bottom: 6px;
}

.form-label span {
  color: #dc3545;
}

.form-control {
  height: 36px;
  border-color: #dfe3ea;
  font-size: 14px;
}

.form-control:focus {
  box-shadow: none;
  border-color: #0d6efd;
}

.input-icon {
  position: relative;
}

.input-icon i {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  color: #64748b;
}

.gender-group {
  height: 36px;
  display: flex;
  align-items: center;
  gap: 28px;
}

.gender-group label {
  display: flex;
  align-items: center;
  gap: 7px;
  color: #334155;
  font-weight: 500;
}

.gender-group input {
  accent-color: #0d6efd;
}

.form-actions {
  display: flex;
  gap: 12px;
}

.form-actions .btn {
  height: 36px;
  min-width: 118px;
  font-size: 14px;
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
