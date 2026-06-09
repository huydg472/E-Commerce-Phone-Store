<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import {useRouter} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useAuthStore} from '@/stores/authStore'
import {useUserStore} from '@/stores/userStore'
import UserForm from '@/components/user/UserForm.vue'

const router = useRouter()
const authStore = useAuthStore()
const userStore = useUserStore()
const {user} = storeToRefs(authStore)

const loadingError = ref('')
const isSubmitting = ref(false)

const form = reactive({
  role_id: '',
  name: '',
  email: '',
  phone: '',
})

const fillForm = () => {
  const currentUser = user.value
  if (!currentUser) return

  form.name = currentUser.name ?? ''
  form.email = currentUser.email ?? ''
  form.phone = currentUser.phone ?? ''
}

const loadData = async () => {
  loadingError.value = ''

  try {
    if (!authStore.user) {
      await authStore.fetchMe()
    }

    fillForm()
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được thông tin tài khoản.'
  }
}

const handleSubmit = async () => {
  loadingError.value = ''

  try {
    isSubmitting.value = true

    if (!user.value?.id) {
      loadingError.value = 'Không tìm thấy tài khoản hiện tại.'
      return
    }

    const payload = {
      name: form.name,
      email: form.email,
      phone: form.phone,
    }

    await userStore.update(user.value.id, payload)
    await authStore.fetchMe()
    await router.push({name: 'admin.account'})
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

const cancelTo = computed(() => ({name: 'admin.account'}))

onMounted(loadData)
</script>

<template>
  <div class="edit-page">
    <div class="page-head">
      <div>
        <p class="eyebrow">Thông tin tài khoản</p>
        <h1>Chỉnh sửa hồ sơ quản trị</h1>
      </div>

      <RouterLink :to="{name: 'admin.account'}" class="secondary-action">
        <i class="bi bi-arrow-left"></i>
        Quay lại
      </RouterLink>
    </div>

    <div v-if="userStore.loading || authStore.loading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải dữ liệu...</p>
    </div>

    <UserForm
      v-else
      :form="form"
      :roles="[]"
      :error-message="loadingError"
      :submitting="isSubmitting"
      submit-label="Lưu hồ sơ"
      :cancel-to="cancelTo"
      :show-password-fields="false"
      :show-role-select="false"
      :show-status-select="false"
      :show-username-field="false"
      :show-email-verified-at="false"
      :password-required="false"
      @submit="handleSubmit"
    />
  </div>
</template>

<style scoped>
.edit-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.page-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.page-head h1 {
  margin: 0;
  color: #0f172a;
  font-size: 30px;
  font-weight: 900;
}

.secondary-action {
  min-height: 42px;
  padding: 0 14px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}

.state-card {
  border: 1px solid #e5eaf3;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.state-card {
  min-height: 220px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  color: #475569;
}

@media (max-width: 992px) {
  .page-head {
    flex-direction: column;
  }
}
</style>
