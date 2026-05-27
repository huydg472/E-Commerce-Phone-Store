<script setup>
import {useRoute, useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/authStore.js'

defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const menuGroups = [
  {
    title: 'Tổng quan',
    items: [
      {
        label: 'Dashboard',
        icon: 'bi bi-grid',
        to: '/admin/dashboard',
        match: '/admin/dashboard',
      },
    ],
  },
  {
    title: 'Quản lý bán hàng',
    items: [
      {
        label: 'Sản phẩm',
        icon: 'bi bi-phone',
        to: '/admin/products',
        match: '/admin/products',
      },
      {
        label: 'Danh mục',
        icon: 'bi bi-grid-3x3-gap',
        to: '/admin/categories',
        match: '/admin/categories',
      },
      {
        label: 'Thương hiệu',
        icon: 'bi bi-award',
        to: '/admin/brands',
        match: '/admin/brands',
      },
      {
        label: 'Đơn hàng',
        icon: 'bi bi-receipt',
        to: '/admin/orders',
        match: '/admin/orders',
      },
    ],
  },
  {
    title: 'Quản lý người dùng',
    items: [
      {
        label: 'Khách hàng',
        icon: 'bi bi-people',
        to: '/admin/customers',
        match: '/admin/customers',
      },
      {
        label: 'Nhân viên',
        icon: 'bi bi-person-badge',
        to: '/admin/staff',
        match: '/admin/staff',
      },
    ],
  },
  {
    title: 'Nội dung & hệ thống',
    items: [
      {
        label: 'Tin tức',
        icon: 'bi bi-newspaper',
        to: '/admin/news',
        match: '/admin/news',
      },
      {
        label: 'Banner',
        icon: 'bi bi-image',
        to: '/admin/banners',
        match: '/admin/banners',
      },
      {
        label: 'Cài đặt',
        icon: 'bi bi-gear',
        to: '/admin/settings',
        match: '/admin/settings',
      },
    ],
  },
]

const isActive = (item) => {
  return route.path.startsWith(item.match)
}

const handleClose = () => {
  emit('close')
}

const handleLogout = async () => {
  await authStore.logout()
  emit('close')
  await router.replace('/auth/login')
}
</script>

<template>
  <div v-if="isOpen" class="sidebar-overlay d-lg-none" @click="handleClose"></div>

  <aside class="admin-sidebar" :class="{ 'is-open': isOpen }">
    <!-- Top -->
    <div class="sidebar-top">
      <RouterLink to="/admin/dashboard" class="brand" @click="handleClose">
        <span class="brand-logo">
          <i class="bi bi-phone"></i>
        </span>

        <span class="brand-text">
          <strong>ZinMobile</strong>
          <small>Admin Panel</small>
        </span>
      </RouterLink>

      <button class="sidebar-close d-lg-none" type="button" @click="handleClose">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <!-- Menu -->
    <div class="sidebar-body">
      <div v-for="group in menuGroups" :key="group.title" class="sidebar-group">
        <p class="group-title">
          {{ group.title }}
        </p>

        <ul class="menu-list">
          <li v-for="item in group.items" :key="item.to">
            <RouterLink :to="item.to" class="menu-link" :class="{ active: isActive(item) }" @click="handleClose">
              <span class="menu-icon">
                <i :class="item.icon"></i>
              </span>
              <span class="menu-label">
                {{ item.label }}
              </span>
            </RouterLink>
          </li>
        </ul>
      </div>
    </div>

    <!-- Bottom -->
    <div class="sidebar-bottom">
      <button class="logout-btn" type="button" @click="handleLogout">
        <span class="menu-icon">
          <i class="bi bi-box-arrow-right"></i>
        </span>
        <span class="menu-label">Đăng xuất</span>
      </button>
    </div>
  </aside>
</template>

<style scoped>
.admin-sidebar {
  width: 280px;
  min-height: 100vh;
  background: #ffffff;
  border-right: 1px solid #e5eaf3;
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  z-index: 1040;
}

/* Overlay */
.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.35);
  z-index: 1039;
}

/* Top */
.sidebar-top {
  height: 84px;
  padding: 20px 50px 15px;
  border-bottom: 1px solid #eef2f7;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.brand {
  min-width: 0;
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
}

.brand-logo {
  width: 46px;
  height: 46px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%);
  color: #ffffff;
  font-size: 22px;
  flex-shrink: 0;
  box-shadow: 0 10px 22px rgba(37, 99, 235, 0.2);
}

.brand-text {
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.brand-text strong {
  color: #0f172a;
  font-size: 22px;
  font-weight: 800;
  line-height: 1.1;
}

.brand-text small {
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
  line-height: 1.1;
}

.sidebar-close {
  width: 38px;
  height: 38px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #334155;
  display: grid;
  place-items: center;
  font-size: 16px;
  flex-shrink: 0;
}

.sidebar-close:hover {
  color: #2563eb;
  background: #eef5ff;
  border-color: #bfdbfe;
}

/* Body */
.sidebar-body {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 16px 12px 18px;
}

.sidebar-group + .sidebar-group {
  margin-top: 14px;
}

.group-title {
  margin: 0 10px 8px;
  color: #94a3b8;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  white-space: nowrap;
}

.menu-list {
  margin: 0;
  padding: 0;
  list-style: none;
}

.menu-list li + li {
  margin-top: 4px;
}

.menu-link,
.logout-btn {
  width: 100%;
  min-height: 46px;
  padding: 0 12px;
  border: none;
  border-radius: 12px;
  background: transparent;
  color: #334155;
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 12px;
  text-align: left;
  cursor: pointer;
  transition: 0.2s ease;
  position: relative;
}

.menu-link:hover,
.logout-btn:hover {
  background: #f4f8ff;
  color: #2563eb;
}

.menu-link.active {
  background: #eef5ff;
  color: #2563eb;
  font-weight: 700;
}

.menu-link.active .menu-icon,
.menu-link:hover .menu-icon,
.logout-btn:hover .menu-icon {
  color: #2563eb;
}

.menu-link.active::before {
  content: '';
  width: 4px;
  height: 24px;
  border-radius: 999px;
  background: #2563eb;
  position: absolute;
  left: 0;
}

.menu-icon {
  width: 20px;
  min-width: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 18px;
  transition: 0.2s ease;
}

.menu-label {
  font-size: 15px;
  font-weight: 600;
  line-height: 1.2;
  white-space: nowrap;
}

/* Bottom */
.sidebar-bottom {
  padding: 14px 12px 18px;
  border-top: 1px solid #eef2f7;
}

.logout-btn {
  color: #ef4444;
}

.logout-btn .menu-icon {
  color: #ef4444;
}

.logout-btn:hover {
  background: #fff1f2;
  color: #dc2626;
}

.logout-btn:hover .menu-icon {
  color: #dc2626;
}

/* Scrollbar */
.sidebar-body::-webkit-scrollbar {
  width: 6px;
}

.sidebar-body::-webkit-scrollbar-thumb {
  background: #d7e0ec;
  border-radius: 999px;
}

.sidebar-body::-webkit-scrollbar-track {
  background: transparent;
}

/* Desktop */
@media (min-width: 992px) {
  .admin-sidebar {
    transform: none !important;
  }
}

/* Mobile */
@media (max-width: 991.98px) {
  .admin-sidebar {
    width: 280px;
    position: fixed;
    top: 0;
    left: 0;
    transform: translateX(-100%);
    transition: transform 0.25s ease;
    box-shadow: 16px 0 34px rgba(15, 23, 42, 0.16);
  }

  .admin-sidebar.is-open {
    transform: translateX(0);
  }
}

@media (max-width: 575.98px) {
  .admin-sidebar {
    width: 260px;
  }

  .brand-text strong {
    font-size: 20px;
  }

  .menu-label {
    font-size: 14px;
  }
}
</style>