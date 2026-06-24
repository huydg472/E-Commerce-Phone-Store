<script setup>
import {computed, onMounted, onUnmounted, reactive, ref, watch} from 'vue'
import {storeToRefs} from 'pinia'

import {useAuthStore} from '@/stores/authStore'
import {authService} from '@/services/authService'
import {setUser} from '@/utils/storage'

const authStore = useAuthStore()
const {user} = storeToRefs(authStore)

const loadingError = ref('')
const savedMessage = ref('')
const isSubmitting = ref(false)

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

const syncForm = (source = {}) => {
  form.name = source.name || source.full_name || source.username || profileFallback.name
  form.email = source.email || profileFallback.email
  form.phone = source.phone || source.phone_number || profileFallback.phone
  form.birthday = source.birthday || '15/06/1990'
  form.gender = source.gender || 'Nam'
}

watch(
    () => authStore.user,
    (currentUser) => {
      syncForm(currentUser ?? {})
    },
    {immediate: true, deep: true},
)

watch(
    () => authStore.isEmailVerified,
    (isVerified) => {
      if (isVerified) {
        savedMessage.value = ''
      }
    },
)

const saveProfile = async () => {
  loadingError.value = ''
  savedMessage.value = ''

  if (!user.value?.id) {
    loadingError.value = 'Không tìm thấy tài khoản hiện tại.'
    return
  }

  try {
    isSubmitting.value = true

    const payload = {
      name: form.name,
      email: form.email,
      phone: form.phone,
    }

    const response = await authService.updateMe({
      ...payload,
      birthday: form.birthday,
      gender: form.gender,
    })
    const updatedUser = response.data?.data || response.data?.user || response.data || null

    if (updatedUser) {
      authStore.user = updatedUser
      setUser(updatedUser)
    }

    savedMessage.value =
        response.data?.message || 'Đã lưu thay đổi hồ sơ.'

    await authStore.fetchMe()
    syncForm(authStore.user ?? {})
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data?.errors
      if (errors) {
        const firstKey = Object.keys(errors)[0]
        loadingError.value = errors[firstKey]?.[0] || 'Dữ liệu không hợp lệ.'
        return
      }
    }

    loadingError.value = error.response?.data?.message || 'Cập nhật hồ sơ thất bại.'
  } finally {
    isSubmitting.value = false
  }
}

const resetForm = () => {
  syncForm(authStore.user ?? {})
  loadingError.value = ''
  savedMessage.value = ''
}

const refreshProfile = async () => {
  if (!authStore.token) {
    return
  }

  try {
    await authStore.fetchMe()
    syncForm(authStore.user ?? {})

    if (authStore.isEmailVerified) {
      savedMessage.value = ''
    }
  } catch {
    // Giữ dữ liệu hiện tại nếu refresh thất bại.
  }
}

const emailNotice = computed(() => {
  return !authStore.isEmailVerified && authStore.user?.email
      ? 'Email hiện tại chưa được xác minh.'
      : ''
})

const handleVisibilityChange = () => {
  if (document.visibilityState === 'visible') {
    refreshProfile()
  }
}

onMounted(async () => {
  await refreshProfile()
  syncForm(authStore.user ?? {})
  window.addEventListener('focus', refreshProfile)
  document.addEventListener('visibilitychange', handleVisibilityChange)
})

onUnmounted(() => {
  window.removeEventListener('focus', refreshProfile)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
})
</script>

<template>
  <section class="account-page">
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

    <div v-if="emailNotice" class="warning-banner mb-3">
      <i class="bi bi-exclamation-triangle"></i>
      <span>{{ emailNotice }}</span>
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
              <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                <i v-else class="bi bi-save"></i>
                {{ isSubmitting ? 'Đang lưu...' : 'Lưu thay đổi' }}
              </button>

              <button type="button" class="btn btn-light border" @click="resetForm">
                Hủy bỏ
              </button>
            </div>
          </form>

          <p v-if="loadingError" class="text-danger mt-3 mb-0">{{ loadingError }}</p>
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

.success-banner,
.warning-banner {
  padding: 12px 14px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
}

.success-banner {
  border: 1px solid #bbf7d0;
  background: #f0fdf4;
  color: #166534;
}

.warning-banner {
  border: 1px solid #fde68a;
  background: #fffbeb;
  color: #92400e;
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
