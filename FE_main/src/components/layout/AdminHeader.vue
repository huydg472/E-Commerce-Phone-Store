<script setup>
import {computed} from 'vue'
import {useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/authStore.js'

const emit = defineEmits(['open-sidebar'])

const router = useRouter()
const authStore = useAuthStore()

const displayName = computed(() => {
  return authStore.user?.['name'] || authStore.user?.['username'] || 'Admin'
})

const displayRole = computed(() => {
  const roleId = Number(authStore.user?.['role_id'])

  if (roleId === 1) return 'Quản trị viên'
  if (roleId === 2) return 'Nhân viên'

  return 'Tài khoản'
})

const handleLogout = async () => {
  await authStore.logout()
  await router.replace('/auth/login')
}
</script>

<template>
  <header class="admin-header">
    <div class="header-left">
      <button class="menu-btn d-lg-none" type="button" @click="emit('open-sidebar')">
        <i class="bi bi-list"></i>
      </button>
      <div class="page-title">
        <h1>Dashboard</h1>
        <p>Tổng quan cửa hàng bán điện thoại</p>
      </div>
    </div>

    <div class="header-center">
      <form class="search-box" @submit.prevent>
        <i class="bi bi-search"></i>
        <input type="search" placeholder="Tìm kiếm sản phẩm, đơn hàng, khách hàng..."/>
      </form>
    </div>

    <div class="header-right">
      <button class="notification-btn" type="button" aria-label="Thông báo">
        <i class="bi bi-bell"></i>
        <span>3</span>
      </button>
      <div class="dropdown admin-account-dropdown">
        <button class="admin-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="admin-avatar">
            <i class="bi bi-person-fill"></i>
          </span>

          <span class="admin-info">
            <strong>{{ displayName }}</strong>
            <small>{{ displayRole }}</small>
          </span>

          <i class="bi bi-chevron-down admin-arrow"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end admin-account-menu">
          <li>
            <RouterLink class="account-menu-item" to="/admin/account">
              <i class="bi bi-person"></i>
              <span>Thông tin tài khoản</span>
            </RouterLink>
          </li>

          <li>
            <RouterLink class="account-menu-item" to="/admin/settings">
              <i class="bi bi-gear"></i>
              <span>Cài đặt</span>
            </RouterLink>
          </li>

          <li>
            <hr class="dropdown-divider"/>
          </li>

          <li>
            <button class="account-menu-item logout-item" type="button" @click="handleLogout">
              <i class="bi bi-box-arrow-right"></i>
              <span>Đăng xuất</span>
            </button>
          </li>
        </ul>
      </div>
    </div>
  </header>
</template>

<style scoped>
.admin-header {
  min-height: 96px;
  padding: 18px 28px;
  background: #f6f8fc;
  display: grid;
  grid-template-columns: minmax(250px, 1fr) minmax(380px, 560px) auto;
  align-items: center;
  gap: 24px;
  border-bottom: 1px solid #e9eef6;
}

.header-left {
  min-width: 0;
  display: flex;
  align-items: center;
  gap: 14px;
}

.page-title h1 {
  margin: 0;
  color: #0f172a;
  font-size: 28px;
  font-weight: 800;
  line-height: 1.2;
}

.page-title p {
  margin: 5px 0 0;
  color: #64748b;
  font-size: 15px;
  line-height: 1.4;
}

.header-center {
  display: flex;
  justify-content: center;
  align-items: center;
}

.search-box {
  width: 100%;
  max-width: 560px;
  height: 54px;
  padding: 0 18px;
  background: #ffffff;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  box-shadow: 0 6px 18px rgba(15, 23, 42, 0.04);
  display: flex;
  align-items: center;
  gap: 12px;
}

.search-box i {
  color: #64748b;
  font-size: 20px;
  flex-shrink: 0;
}

.search-box input {
  width: 100%;
  min-width: 0;
  border: none;
  outline: none;
  background: transparent;
  color: #0f172a;
  font-size: 16px;
}

.search-box input::placeholder {
  color: #94a3b8;
}

.header-right {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 16px;
  flex-shrink: 0;
}

.notification-btn {
  position: relative;
  width: 48px;
  height: 48px;
  border: none;
  outline: none;
  border-radius: 14px;
  background: transparent;
  color: #334155;
  font-size: 24px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.notification-btn:hover {
  color: #2563eb;
  background: #eef5ff;
}

.notification-btn span {
  position: absolute;
  top: -4px;
  right: -3px;
  min-width: 22px;
  height: 22px;
  padding: 0 6px;
  border-radius: 999px;
  background: #ef4444;
  color: #ffffff;
  font-size: 12px;
  font-weight: 800;
  display: grid;
  place-items: center;
  box-shadow: 0 0 0 3px #f6f8fc;
}

.admin-account-dropdown {
  position: relative;
  flex-shrink: 0;
}

.admin-user {
  min-width: 190px;
  height: 58px;
  padding: 6px 6px 6px 0;
  border: none;
  outline: none;
  background: transparent;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #0f172a;
  cursor: pointer;
  transition: 0.2s ease;
}

.admin-user:hover {
  color: #2563eb;
}

.admin-user:focus {
  outline: none;
  box-shadow: none;
}

.admin-avatar {
  width: 42px;
  height: 42px;
  border-radius: 50%;
  flex-shrink: 0;
  display: grid;
  place-items: center;
  color: #ffffff;
  background: linear-gradient(180deg, #93c5fd 0%, #2563eb 76%);
  font-size: 24px;
}

.admin-info {
  min-width: 0;
  flex: 1;
  display: flex;
  flex-direction: column;
  text-align: left;
}

.admin-info strong {
  max-width: 110px;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
  line-height: 1.2;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.admin-info small {
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  line-height: 1.2;
}

.admin-arrow {
  color: #94a3b8;
  font-size: 14px;
  flex-shrink: 0;
}

.dropdown-toggle::after {
  display: none;
}

.admin-account-menu {
  min-width: 240px;
  padding: 10px;
  margin-top: 10px;
  border: 1px solid #e5eaf3;
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.14);
}

.admin-account-menu li {
  list-style: none;
}

.account-menu-item {
  width: 100%;
  min-height: 44px;
  padding: 0 13px;
  border: none;
  border-radius: 10px;
  background: transparent;
  color: #111827;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 15px;
  font-weight: 600;
  text-align: left;
  cursor: pointer;
  transition: 0.18s ease;
}

.account-menu-item i {
  width: 20px;
  min-width: 20px;
  color: #64748b;
  font-size: 18px;
  display: flex;
  justify-content: center;
}

.account-menu-item:hover {
  background: #eef5ff;
  color: #2563eb;
}

.account-menu-item:hover i {
  color: #2563eb;
}

.logout-item {
  color: #ef4444;
}

.logout-item i {
  color: #ef4444;
}

.logout-item:hover {
  background: #fff1f2;
  color: #dc2626;
}

.logout-item:hover i {
  color: #dc2626;
}

.admin-account-menu .dropdown-divider {
  margin: 9px 0;
  border-color: #e5e7eb;
}

.menu-btn {
  width: 44px;
  height: 44px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #0f172a;
  font-size: 24px;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

@media (max-width: 1400px) {
  .admin-header {
    grid-template-columns: minmax(220px, 1fr) minmax(320px, 480px) auto;
    padding: 16px 22px;
    gap: 18px;
  }
}

@media (max-width: 1200px) {
  .admin-header {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .header-left,
  .header-center,
  .header-right {
    width: 100%;
  }

  .header-right {
    justify-content: space-between;
  }

  .search-box {
    max-width: 100%;
  }
}

@media (max-width: 770px) {
  .admin-header {
    min-height: auto;
    padding: 14px 16px;
  }

  .page-title h1 {
    font-size: 24px;
  }

  .page-title p {
    font-size: 14px;
  }

  .header-right {
    flex-wrap: wrap;
    gap: 10px;
  }

  .header-center {
    order: 3;
  }

  .search-box {
    width: 100%;
    height: 48px;
  }

  .notification-btn {
    margin-left: auto;
  }

  .admin-user {
    min-width: auto;
    width: 50px;
    height: 50px;
    padding: 4px;
    justify-content: center;
  }

  .admin-avatar {
    width: 38px;
    height: 38px;
    font-size: 22px;
  }

  .admin-info,
  .admin-arrow {
    display: none;
  }

  .admin-account-menu {
    min-width: 230px;
  }
}
</style>