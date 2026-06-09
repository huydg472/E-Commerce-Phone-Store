<script setup>
import {computed, onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useAuthStore} from '@/stores/authStore'
import {formatDate} from '@/utils/formatDate'

const router = useRouter()
const authStore = useAuthStore()
const {user, loading} = storeToRefs(authStore)
const refreshing = ref(false)

const initials = computed(() => {
  const name = user.value?.name?.trim() || user.value?.username || 'Admin'
  return name
      .split(' ')
      .map((part) => part.charAt(0))
      .join('')
      .slice(0, 2)
      .toUpperCase()
})

const roleLabel = computed(() => user.value?.role?.display_name || user.value?.role?.name || 'Không xác định')

const emailVerifiedLabel = computed(() => {
  return user.value?.email_verified_at ? formatDate(user.value.email_verified_at) : 'Chưa xác minh'
})

const accountCards = computed(() => [
  {
    label: 'Tên đăng nhập',
    value: user.value?.username || '---',
    icon: 'bi bi-person-badge',
  },
  {
    label: 'Email',
    value: user.value?.email || '---',
    icon: 'bi bi-envelope',
  },
  {
    label: 'Số điện thoại',
    value: user.value?.phone || '---',
    icon: 'bi bi-telephone',
  },
  {
    label: 'Ngày tạo',
    value: user.value?.created_at ? formatDate(user.value.created_at) : '---',
    icon: 'bi bi-calendar-event',
  },
  {
    label: 'Cập nhật gần nhất',
    value: user.value?.updated_at ? formatDate(user.value.updated_at) : '---',
    icon: 'bi bi-clock-history',
  },
  {
    label: 'Xác minh email',
    value: emailVerifiedLabel.value,
    icon: 'bi bi-envelope-check',
    iconTone: 'orange',
  },
])

const refreshMe = async () => {
  if (refreshing.value) return
  refreshing.value = true

  try {
    await authStore.fetchMe()
  } finally {
    refreshing.value = false
  }
}

const goToEditProfile = () => {
  router.push({name: 'admin.account.edit'})
}

const goToChangePassword = () => {
  router.push({name: 'admin.account.change-password'})
}

onMounted(async () => {
  if (!authStore.user) {
    await refreshMe()
  }
})
</script>

<template>
  <div class="account-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Thông tin tài khoản</p>
        <h1>Tài khoản quản trị</h1>
        <p class="subtitle">
          Xem nhanh thông tin cá nhân, trạng thái hoạt động và các thao tác tài khoản đang dùng.
        </p>

        <div class="hero-actions">
          <button type="button" class="primary-action" :disabled="loading || refreshing" @click="refreshMe">
            <i :class="['bi', refreshing ? 'bi-arrow-repeat spin' : 'bi-arrow-clockwise']"></i>
            {{ refreshing ? 'Đang tải' : 'Làm mới' }}
          </button>

          <button type="button" class="secondary-action" :disabled="!user" @click="goToEditProfile">
            <i class="bi bi-pencil"></i>
            Chỉnh sửa hồ sơ
          </button>

          <button type="button" class="ghost-action" @click="goToChangePassword">
            <i class="bi bi-shield-lock"></i>
            Đổi mật khẩu
          </button>
        </div>
      </div>

      <div class="hero-profile">
        <div class="avatar">{{ initials }}</div>
        <div class="profile-copy">
          <h2>{{ user?.name || 'Tài khoản quản trị' }}</h2>
          <p class="profile-role-label">{{ roleLabel }}</p>
        </div>
      </div>
    </section>

    <section class="content-grid">
      <article class="panel-card info-panel">
        <div class="panel-head">
          <div>
            <p class="panel-label">Hồ sơ hiện tại</p>
            <h3>Thông tin cơ bản</h3>
          </div>
        </div>

        <div class="info-grid">
          <div v-for="item in accountCards" :key="item.label" class="info-item">
            <span class="info-icon" :class="item.iconTone ? `tone-${item.iconTone}` : ''">
              <i :class="item.icon"></i>
            </span>

            <div>
              <small>{{ item.label }}</small>
              <strong>{{ item.value }}</strong>
            </div>
          </div>
        </div>
      </article>

      <article class="panel-card action-panel">
        <div class="panel-head">
          <div>
            <p class="panel-label">Thao tác nhanh</p>
            <h3>Quản lý tài khoản</h3>
          </div>
        </div>

        <div class="action-list">
          <button type="button" class="action-row" @click="goToEditProfile">
            <span class="row-icon blue">
              <i class="bi bi-pencil-square"></i>
            </span>
            <span class="row-text">
              <strong>Chỉnh sửa hồ sơ</strong>
              <small>Cập nhật tên, email, số điện thoại.</small>
            </span>
            <i class="bi bi-chevron-right"></i>
          </button>

          <button type="button" class="action-row" @click="goToChangePassword">
            <span class="row-icon orange">
              <i class="bi bi-shield-lock"></i>
            </span>
            <span class="row-text">
              <strong>Đổi mật khẩu</strong>
              <small>Đặt lại mật khẩu để tăng bảo mật.</small>
            </span>
            <i class="bi bi-chevron-right"></i>
          </button>

          <RouterLink to="/admin/settings" class="action-row">
            <span class="row-icon green">
              <i class="bi bi-gear"></i>
            </span>
            <span class="row-text">
              <strong>Thiết lập hệ thống</strong>
              <small>Đi tới trang cài đặt chung của admin.</small>
            </span>
            <i class="bi bi-chevron-right"></i>
          </RouterLink>
        </div>
      </article>
    </section>
  </div>
</template>

<style scoped>
.account-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-card {
  border: 1px solid #dbe5f6;
  border-radius: 24px;
  background:
      radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 34%),
      linear-gradient(180deg, #ffffff 0%, #f7faff 100%);
  box-shadow: 0 16px 38px rgba(15, 23, 42, 0.06);
  padding: 26px;
  display: grid;
  grid-template-columns: minmax(0, 1.35fr) minmax(300px, 0.85fr);
  gap: 24px;
}

.hero-copy {
  min-width: 0;
}

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 32px;
  font-weight: 900;
  line-height: 1.08;
}

.subtitle {
  margin: 12px 0 0;
  max-width: 760px;
  color: #5b6f8c;
  font-size: 16px;
  line-height: 1.6;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 22px;
}

.primary-action,
.secondary-action,
.ghost-action {
  min-height: 44px;
  padding: 0 18px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1px solid transparent;
  font-size: 14px;
  font-weight: 800;
  text-decoration: none;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
}

.primary-action {
  background: linear-gradient(135deg, #2d63ea 0%, #2563eb 100%);
  color: #ffffff;
  box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
}

.secondary-action {
  background: #ffffff;
  border-color: #dbe3ef;
  color: #1e3a8a;
}

.ghost-action {
  background: #f8fbff;
  border-color: #dbe3ef;
  color: #334155;
}

.primary-action:hover,
.secondary-action:hover,
.ghost-action:hover {
  transform: translateY(-1px);
}

.spin {
  animation: spin 0.8s linear infinite;
}

.hero-profile {
  border-radius: 20px;
  background: #ffffff;
  border: 1px solid #e5eaf3;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9);
  padding: 18px 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  justify-self: end;
  align-self: start;
  width: min(100%, 540px);
  min-height: 0;
}

.avatar {
  width: 72px;
  height: 72px;
  border-radius: 22px;
  display: grid;
  place-items: center;
  background: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%);
  color: #ffffff;
  font-size: 26px;
  font-weight: 900;
  box-shadow: 0 14px 26px rgba(37, 99, 235, 0.22);
}

.profile-copy {
  min-width: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.profile-copy h2 {
  margin: 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

.profile-role-label {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
  line-height: 1.25;
}

.profile-copy strong {
  color: #0f172a;
  font-size: 15px;
  font-weight: 900;
  line-height: 1.25;
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.2fr) minmax(340px, 0.8fr);
  gap: 18px;
}

.panel-card {
  border: 1px solid #e5eaf3;
  border-radius: 22px;
  background: #ffffff;
  box-shadow: 0 14px 30px rgba(15, 23, 42, 0.04);
}

.panel-head {
  padding: 20px 20px 0;
}

.panel-label {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.panel-head h3 {
  margin: 0;
  color: #0f172a;
  font-size: 20px;
  font-weight: 900;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  padding: 20px;
}

.info-item {
  border: 1px solid #edf2f7;
  border-radius: 16px;
  background: #fbfdff;
  padding: 14px;
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.info-icon {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  background: #eff6ff;
  color: #2563eb;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.info-icon.tone-orange {
  background: #fff4e8;
  color: #f97316;
}

.info-item small {
  display: block;
  margin-bottom: 5px;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.info-item strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
  line-height: 1.5;
  word-break: break-word;
}

.action-panel {
  padding-bottom: 8px;
}

.action-list {
  padding: 18px 20px 20px;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.action-row {
  width: 100%;
  border: 1px solid #e6edf8;
  border-radius: 18px;
  background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
  padding: 13px 14px;
  display: grid;
  grid-template-columns: 42px minmax(0, 1fr) 16px;
  align-items: center;
  column-gap: 12px;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.action-row:hover {
  transform: translateY(-1px);
  border-color: #c8d7f2;
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.04);
}

.row-icon {
  width: 42px;
  height: 42px;
  border-radius: 13px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
  font-size: 18px;
}

.row-icon.blue {
  background: #eef4ff;
  color: #2563eb;
}

.row-icon.orange {
  background: #fff4e8;
  color: #f97316;
}

.row-icon.green {
  background: #ecfdf5;
  color: #16a34a;
}

.row-text {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 3px;
  text-align: left;
}

.row-text strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
}

.row-text small {
  color: #64748b;
  font-size: 13px;
  line-height: 1.45;
}

.action-row > i {
  color: #94a3b8;
  font-size: 13px;
  flex-shrink: 0;
  justify-self: end;
}

.action-row:nth-child(2) .row-icon {
  background: #fff4e8;
  color: #f97316;
}

.action-row:nth-child(2):hover {
  border-color: #ffd8b8;
}

.action-row:nth-child(3) .row-icon {
  background: #eefbf2;
  color: #16a34a;
}

.action-row:nth-child(3):hover {
  border-color: #cdefd7;
}

@media (max-width: 1200px) {
  .hero-card,
  .content-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .hero-card {
    padding: 20px;
  }

  .hero-profile {
    width: 100%;
    justify-self: stretch;
  }

  .hero-copy h1 {
    font-size: 28px;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }

  to {
    transform: rotate(360deg);
  }
}
</style>
