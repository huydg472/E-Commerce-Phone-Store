<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useRoleStore} from '@/stores/roleStore'
import {useUserStore} from '@/stores/userStore'
import UserForm from '@/components/user/UserForm.vue'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()
const roleStore = useRoleStore()

const loadingError = ref('')
const isSubmitting = ref(false)

const form = reactive({
  role_id: '',
  name: '',
  email: '',
  phone: '',
  username: '',
  password: '',
  password_confirmation: '',
  status: 'active',
  email_verified_at: '',
})

const roles = computed(() => (Array.isArray(roleStore.items) ? roleStore.items : []))

const fillForm = (user) => {
  if (!user) return

  form.role_id = String(user.role_id ?? user.role?.id ?? '')
  form.name = user.name ?? ''
  form.email = user.email ?? ''
  form.phone = user.phone ?? ''
  form.username = user.username ?? ''
  form.password = ''
  form.password_confirmation = ''
  form.status = user.status === 'inactive' ? 'inactive' : 'active'
  form.email_verified_at = user.email_verified_at ? String(user.email_verified_at).slice(0, 19).replace('T', ' ') : ''
}

const loadInitialData = async () => {
  loadingError.value = ''

  try {
    await Promise.all([
      userStore.fetchById(route.params.id),
      roleStore.fetchAll(),
    ])

    fillForm(userStore.item)
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được dữ liệu cần chỉnh sửa.'
  }
}

const handleSubmit = async () => {
  loadingError.value = ''

  try {
    isSubmitting.value = true

    const payload = {
      role_id: Number(form.role_id),
      name: form.name,
      email: form.email,
      phone: form.phone,
      username: form.username,
      status: form.status,
      email_verified_at: form.email_verified_at || null,
    }

    if (form.password) {
      payload.password = form.password
      payload.password_confirmation = form.password_confirmation
    }

    await userStore.update(route.params.id, payload)
    await router.replace(`/admin/users/${route.params.id}`)
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data?.errors
      if (errors) {
        const firstKey = Object.keys(errors)[0]
        loadingError.value = errors[firstKey]?.[0] || 'Dữ liệu không hợp lệ.'
        return
      }
    }

    loadingError.value = error.response?.data?.message || 'Cập nhật người dùng thất bại.'
  } finally {
    isSubmitting.value = false
  }
}

onMounted(loadInitialData)
</script>

<template>
  <div class="edit-page">
    <div class="page-head">
      <div>
        <p class="eyebrow">Quản lý người dùng</p>
        <h1>Chỉnh sửa người dùng</h1>
      </div>

      <RouterLink :to="`/admin/users/${route.params.id}`" class="secondary-action">
        <i class="bi bi-arrow-left"></i>
        Quay lại
      </RouterLink>
    </div>

    <div v-if="userStore.loading || roleStore.loading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải dữ liệu...</p>
    </div>

    <UserForm
      v-else
      :form="form"
      :roles="roles"
      :error-message="loadingError"
      :submitting="isSubmitting"
      submit-label="Lưu thay đổi"
      :cancel-to="`/admin/users/${route.params.id}`"
      :show-password-fields="true"
      :show-email-verified-at="true"
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

.secondary-action,
.primary-action {
  min-height: 42px;
  padding: 0 14px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
  border: none;
}

.secondary-action {
  background: #ffffff;
  border: 1px solid #dbe3ef;
  color: #334155;
}

.primary-action {
  background: #2563eb;
  color: #ffffff;
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
