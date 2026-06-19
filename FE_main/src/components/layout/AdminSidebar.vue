<script setup>
import {computed} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/authStore.js'
import {usePublicSiteSettings} from '@/composables/usePublicSiteSettings'
import {PERMISSIONS} from '@/constants/permissions'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['close'])

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const {brandName, logoUrl} = usePublicSiteSettings()

const menuGroups = computed(() => [
  {
    title: 'Tổng quan',
    items: [
      {label: 'Dashboard', icon: 'bi bi-grid', to: '/admin/dashboard', match: '/admin/dashboard', permission: null},
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
        permission: PERMISSIONS.PRODUCTS.VIEW
      },
      {
        label: 'Danh mục',
        icon: 'bi bi-grid-3x3-gap',
        to: '/admin/categories',
        match: '/admin/categories',
        permission: PERMISSIONS.CATEGORIES.VIEW
      },
      {
        label: 'Thương hiệu',
        icon: 'bi bi-award',
        to: '/admin/brands',
        match: '/admin/brands',
        permission: PERMISSIONS.BRANDS.VIEW
      },
      {
        label: 'Đơn hàng',
        icon: 'bi bi-receipt',
        to: '/admin/orders',
        match: '/admin/orders',
        permission: PERMISSIONS.ORDERS.VIEW
      },
      {
        label: 'Coupon',
        icon: 'bi bi-ticket-perforated',
        to: '/admin/coupons',
        match: '/admin/coupons',
        permission: PERMISSIONS.COUPONS.VIEW
      },
    ],
  },
  {
    title: 'Quản lý người dùng',
    items: [
      {
        label: 'Người dùng',
        icon: 'bi bi-people',
        to: '/admin/users',
        match: '/admin/users',
        permission: PERMISSIONS.USERS.VIEW
      },
      {
        label: 'Vai trò',
        icon: 'bi bi-shield-lock',
        to: '/admin/roles',
        match: '/admin/roles',
        permission: PERMISSIONS.ROLES.VIEW
      },
      {
        label: 'Quyền',
        icon: 'bi bi-key',
        to: '/admin/permissions',
        match: '/admin/permissions',
        permission: PERMISSIONS.PERMISSION.VIEW
      },
    ],
  },
  {
    title: 'Nội dung & hệ thống',
    items: [
      {
        label: 'Báo cáo doanh thu',
        icon: 'bi bi-graph-up',
        to: '/admin/reports/revenue',
        match: '/admin/reports/revenue',
        permission: null
      },
      {
        label: 'Báo cáo sản phẩm',
        icon: 'bi bi-bar-chart',
        to: '/admin/reports/products',
        match: '/admin/reports/products',
        permission: null
      },
      {
        label: 'Báo cáo đơn hàng',
        icon: 'bi bi-clipboard-data',
        to: '/admin/reports/orders',
        match: '/admin/reports/orders',
        permission: null
      },
      {
        label: 'Nhật ký kho',
        icon: 'bi bi-journal-text',
        to: '/admin/stock-logs',
        match: '/admin/stock-logs',
        permission: PERMISSIONS.STOCK_LOGS.VIEW
      },
      {
        label: 'Tin tức',
        icon: 'bi bi-newspaper',
        to: '/admin/news/posts',
        match: '/admin/news',
        permission: PERMISSIONS.NEWS_POSTS.VIEW
      },
      {
        label: 'Cài đặt',
        icon: 'bi bi-gear',
        to: '/admin/settings',
        match: '/admin/settings',
        permission: PERMISSIONS.SETTINGS.VIEW
      },
    ],
  },
])

const isActive = (item) => route.path.startsWith(item.match)
const canShowItem = (item) => !item.permission || authStore.can(item.permission)

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
    <div class="sidebar-top">
      <RouterLink to="/admin/dashboard" class="brand" @click="handleClose">
        <span class="brand-logo">
          <img v-if="logoUrl" :src="logoUrl" :alt="brandName" class="brand-image"/>
          <i v-else class="bi bi-phone"></i>
        </span>

        <span class="brand-text">
          <strong>{{ brandName }}</strong>
          <small>Admin Panel</small>
        </span>
      </RouterLink>

      <button class="sidebar-close d-lg-none" type="button" @click="handleClose">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <div class="sidebar-body">
      <div v-for="group in menuGroups" :key="group.title" class="sidebar-group">
        <p class="group-title">{{ group.title }}</p>

        <ul class="menu-list">
          <li v-for="item in group.items.filter(canShowItem)" :key="item.to">
            <RouterLink :to="item.to" class="menu-link" :class="{ active: isActive(item) }" @click="handleClose">
              <span class="menu-icon">
                <i :class="item.icon"></i>
              </span>
              <span class="menu-label">{{ item.label }}</span>
            </RouterLink>
          </li>
        </ul>
      </div>
    </div>

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

.sidebar-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.35);
  z-index: 1039;
}

.sidebar-top {
  height: 84px;
  padding: 20px 24px 15px;
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
  overflow: hidden;
}

.brand-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
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
}

.menu-list {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.menu-link,
.logout-btn {
  width: 100%;
  min-height: 46px;
  padding: 0 14px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #334155;
  text-decoration: none;
  background: transparent;
  border: 0;
  transition: all 0.2s ease;
}

.menu-link:hover,
.logout-btn:hover {
  background: #f1f6ff;
  color: #1d4ed8;
}

.menu-link.active {
  background: linear-gradient(135deg, #eaf2ff 0%, #dce9ff 100%);
  color: #2563eb;
  box-shadow: inset 0 0 0 1px #bfdbfe;
}

.menu-icon {
  width: 30px;
  height: 30px;
  border-radius: 10px;
  display: inline-grid;
  place-items: center;
  flex-shrink: 0;
  background: #f8fafc;
}

.menu-link.active .menu-icon {
  background: rgba(37, 99, 235, 0.12);
}

.menu-label {
  font-size: 14px;
  font-weight: 700;
}

.sidebar-bottom {
  padding: 14px 12px 18px;
  border-top: 1px solid #eef2f7;
}

.logout-btn {
  color: #dc2626;
}

.logout-btn:hover {
  background: #fff1f2;
}

.logout-btn .menu-icon {
  background: rgba(220, 38, 38, 0.08);
  color: #dc2626;
}

@media (max-width: 991.98px) {
  .admin-sidebar {
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    transform: translateX(-102%);
    transition: transform 0.25s ease;
    box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
  }

  .admin-sidebar.is-open {
    transform: translateX(0);
  }
}
</style>
