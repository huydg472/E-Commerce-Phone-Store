<script setup>
import {computed, onMounted, ref} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useUserStore} from '@/stores/userStore'
import {formatDate} from '@/utils/formatDate'

const route = useRoute()
const router = useRouter()
const userStore = useUserStore()

const errorMessage = ref('')

const user = computed(() => userStore.item)

const roleLabel = computed(() => {
  return user.value?.role?.display_name || user.value?.role?.name || 'Không xác định'
})

const statusLabel = computed(() => {
  return user.value?.status === 'active' ? 'Hoạt động' : 'Không hoạt động'
})

const statusClass = computed(() => {
  return user.value?.status === 'active' ? 'badge-active' : 'badge-inactive'
})

const fetchUser = async () => {
  errorMessage.value = ''

  try {
    await userStore.fetchById(route.params.id)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không tải được thông tin người dùng.'
  }
}

const handleDelete = async () => {
  if (!user.value) return

  const confirmed = window.confirm(`Xóa người dùng ${user.value.name}?`)
  if (!confirmed) return

  try {
    await userStore.remove(user.value.id)
    await router.replace('/admin/users')
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Xóa người dùng thất bại.'
  }
}

onMounted(fetchUser)
</script>

<template>
  <div class="detail-page">
    <div class="page-head">
      <div>
        <p class="eyebrow">Quản lý người dùng</p>
        <h1>Chi tiết người dùng</h1>
      </div>

      <div class="actions">
        <RouterLink to="/admin/users" class="secondary-action">
          <i class="bi bi-arrow-left"></i>
          Quay lại danh sách
        </RouterLink>

        <RouterLink v-if="user" :to="`/admin/users/${user.id}/edit`" class="secondary-action">
          <i class="bi bi-pencil"></i>
          Chỉnh sửa
        </RouterLink>

        <button v-if="user" type="button" class="danger-action" @click="handleDelete">
          <i class="bi bi-trash"></i>
          Xóa
        </button>
      </div>
    </div>

    <div v-if="userStore.loading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải thông tin người dùng...</p>
    </div>

    <div v-else-if="errorMessage" class="state-card error-state">
      <i class="bi bi-exclamation-triangle"></i>
      <p>{{ errorMessage }}</p>
    </div>

    <div v-else-if="user" class="detail-card">
      <div class="profile-head">
        <div class="avatar">
          {{ user.name?.charAt(0)?.toUpperCase() || 'U' }}
        </div>

        <div>
          <h2>{{ user.name }}</h2>
          <p>@{{ user.username }}</p>
        </div>

        <span class="status-badge" :class="statusClass">{{ statusLabel }}</span>
      </div>

      <div class="info-grid">
        <div class="info-item">
          <span>Vai trò</span>
          <strong>{{ roleLabel }}</strong>
        </div>
        <div class="info-item">
          <span>Email</span>
          <strong>{{ user.email }}</strong>
        </div>
        <div class="info-item">
          <span>Số điện thoại</span>
          <strong>{{ user.phone }}</strong>
        </div>
        <div class="info-item">
          <span>Ngày tạo</span>
          <strong>{{ formatDate(user.created_at) }}</strong>
        </div>
        <div class="info-item">
          <span>Cập nhật gần nhất</span>
          <strong>{{ formatDate(user.updated_at) }}</strong>
        </div>
        <div class="info-item">
          <span>Đã xác minh email</span>
          <strong>{{ user.email_verified_at ? formatDate(user.email_verified_at) : 'Chưa xác minh' }}</strong>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.detail-page {
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

.actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.secondary-action,
.danger-action {
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

.danger-action {
  background: #fee2e2;
  color: #b91c1c;
}

.state-card,
.detail-card {
  border: 1px solid #e5eaf3;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.state-card {
  min-height: 240px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  color: #475569;
}

.state-card.error-state {
  color: #dc2626;
}

.profile-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 22px;
  border-bottom: 1px solid #eef2f7;
}

.avatar {
  width: 60px;
  height: 60px;
  border-radius: 18px;
  background: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%);
  color: #ffffff;
  display: grid;
  place-items: center;
  font-size: 24px;
  font-weight: 900;
}

.profile-head h2 {
  margin: 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

.profile-head p {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 14px;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 7px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
}

.badge-active {
  background: #dcfce7;
  color: #166534;
}

.badge-inactive {
  background: #fef3c7;
  color: #92400e;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
  padding: 22px;
}

.info-item {
  padding: 16px;
  border: 1px solid #eef2f7;
  border-radius: 14px;
  background: #f8fbff;
}

.info-item span {
  display: block;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.info-item strong {
  display: block;
  margin-top: 8px;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
  word-break: break-word;
}

@media (max-width: 992px) {
  .page-head,
  .profile-head {
    flex-direction: column;
    align-items: flex-start;
  }

  .info-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 768px) {
  .info-grid {
    grid-template-columns: 1fr;
    padding: 16px;
  }

  .profile-head {
    padding: 16px;
  }
}
</style>
