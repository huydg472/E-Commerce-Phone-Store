<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import {useRouter} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useAuthStore} from '@/stores/authStore'
import {userService} from '@/services/userService'

const router = useRouter()
const authStore = useAuthStore()
const {user} = storeToRefs(authStore)

const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const form = reactive({
  password: '',
  password_confirmation: '',
})

const canSubmit = computed(() => Boolean(form.password && form.password_confirmation))

const goBack = () => {
  router.push({name: 'admin.account'})
}

const handleSubmit = async () => {
  errorMessage.value = ''
  successMessage.value = ''

  if (!form.password || !form.password_confirmation) {
    errorMessage.value = 'Vui lòng nhập đầy đủ mật khẩu mới.'
    return
  }

  if (form.password !== form.password_confirmation) {
    errorMessage.value = 'Xác nhận mật khẩu không khớp.'
    return
  }

  if (!user.value?.id) {
    errorMessage.value = 'Không tìm thấy tài khoản hiện tại.'
    return
  }

  loading.value = true

  try {
    await userService.update(user.value.id, {
      password: form.password,
      password_confirmation: form.password_confirmation,
    })

    await authStore.fetchMe()
    successMessage.value = 'Đã đổi mật khẩu thành công.'
    form.password = ''
    form.password_confirmation = ''
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Đổi mật khẩu thất bại.'
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  if (!authStore.user) {
    await authStore.fetchMe().catch(() => {
    })
  }
})
</script>

<template>
  <div class="change-password-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Tài khoản quản trị</p>
        <h1>Đổi mật khẩu</h1>
        <p class="subtitle">
          Cập nhật mật khẩu mới cho tài khoản <strong>{{ user?.username || 'admin' }}</strong>.
        </p>
      </div>

      <div class="hero-actions">
        <button type="button" class="secondary-action" @click="goBack">
          <i class="bi bi-arrow-left"></i>
          Quay lại
        </button>
      </div>
    </section>

    <section class="content-card">
      <div class="section-head">
        <div>
          <p class="section-label">Bảo mật</p>
          <h2>Nhập mật khẩu mới</h2>
        </div>
      </div>

      <div v-if="errorMessage" class="alert alert-danger">
        {{ errorMessage }}
      </div>

      <div v-if="successMessage" class="alert alert-success">
        {{ successMessage }}
      </div>

      <form class="password-form" @submit.prevent="handleSubmit">
        <div class="field">
          <label class="form-label">Mật khẩu mới</label>
          <input
              v-model.trim="form.password"
              type="password"
              class="form-control"
              placeholder="Nhập mật khẩu mới"
              autocomplete="new-password"
              autofocus
          />
        </div>

        <div class="field">
          <label class="form-label">Xác nhận mật khẩu</label>
          <input
              v-model.trim="form.password_confirmation"
              type="password"
              class="form-control"
              placeholder="Nhập lại mật khẩu mới"
              autocomplete="new-password"
          />
        </div>

        <p class="hint">
          Mật khẩu sẽ được cập nhật cho chính tài khoản đang đăng nhập.
        </p>

        <div class="form-actions">
          <button type="button" class="secondary-action" @click="goBack">
            Hủy
          </button>

          <button type="submit" class="primary-action" :disabled="loading || !canSubmit">
            <span v-if="loading" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            <span>{{ loading ? 'Đang lưu...' : 'Lưu mật khẩu' }}</span>
          </button>
        </div>
      </form>
    </section>
  </div>
</template>

<style scoped>
.change-password-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-card,
.content-card {
  border: 1px solid #dbe5f6;
  border-radius: 24px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  box-shadow: 0 16px 38px rgba(15, 23, 42, 0.06);
}

.hero-card {
  padding: 24px 26px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 30px;
  font-weight: 900;
  line-height: 1.08;
}

.subtitle {
  margin: 10px 0 0;
  color: #64748b;
  font-size: 15px;
  line-height: 1.6;
}

.hero-actions {
  flex-shrink: 0;
  display: flex;
  align-items: center;
}

.content-card {
  padding: 22px 24px 24px;
}

.section-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.section-label {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.section-head h2 {
  margin: 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

.password-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.hint {
  margin: 0;
  color: #64748b;
  font-size: 13px;
  line-height: 1.5;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 6px;
}

.primary-action,
.secondary-action {
  min-height: 44px;
  padding: 0 18px;
  border-radius: 14px;
  border: 1px solid transparent;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
}

.primary-action {
  background: linear-gradient(135deg, #2d63ea 0%, #2563eb 100%);
  color: #ffffff;
}

.secondary-action {
  background: #ffffff;
  color: #334155;
  border-color: #dbe3ef;
}

.primary-action:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .hero-card {
    flex-direction: column;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .primary-action,
  .secondary-action {
    justify-content: center;
    width: 100%;
  }
}
</style>
