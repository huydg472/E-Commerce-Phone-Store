<script setup>
import {computed} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/authStore'
import {useFavoriteStore} from '@/stores/favoriteStore'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()
const favoriteStore = useFavoriteStore()

const items = [
  {
    key: 'overview',
    label: 'Tổng quan',
    icon: 'bi bi-house-door',
    to: {name: 'profile'},
  },
  {
    key: 'profile',
    label: 'Thông tin cá nhân',
    icon: 'bi bi-person',
    to: {name: 'profile.edit'},
  },
  {
    key: 'orders',
    label: 'Đơn hàng của tôi',
    icon: 'bi bi-bag',
    to: {name: 'orders.history'},
  },
  {
    key: 'favorites',
    label: 'Yêu thích',
    icon: 'bi bi-heart',
    to: {name: 'favorites'},
  },
  {
    key: 'address',
    label: 'Sổ địa chỉ',
    icon: 'bi bi-geo-alt',
    to: {name: 'shipping-addresses'},
  },
  {
    key: 'password',
    label: 'Đổi mật khẩu',
    icon: 'bi bi-lock',
    to: {name: 'change-password'},
  },
]

const activeKey = computed(() => {
  const routeName = String(route.name || '')

  if (routeName === 'profile') {
    return 'overview'
  }

  if (routeName === 'profile.edit') {
    return 'profile'
  }

  if (routeName === 'orders.history') {
    return 'orders'
  }

  if (routeName === 'favorites') {
    return 'favorites'
  }

  if (routeName === 'shipping-addresses') {
    return 'address'
  }

  if (routeName === 'change-password') {
    return 'password'
  }

  return ''
})

const handleLogout = async () => {
  await authStore.logout()
  favoriteStore.clear()
  await router.replace('/auth/login')
}
</script>

<template>
  <aside class="account-sidebar">
    <div class="sidebar-header">
      <span class="sidebar-caption">Khu vực tài khoản</span>
      <strong>{{ authStore.user?.name || authStore.user?.username || 'Khách hàng' }}</strong>
    </div>

    <nav class="sidebar-nav" aria-label="Tài khoản">
      <RouterLink
          v-for="item in items"
          :key="item.key"
          class="sidebar-item"
          :class="{ active: activeKey === item.key }"
          :to="item.to"
      >
        <i :class="item.icon"></i>
        <span>{{ item.label }}</span>
      </RouterLink>
    </nav>

    <div class="sidebar-divider"></div>

    <button type="button" class="sidebar-item logout" @click="handleLogout">
      <i class="bi bi-box-arrow-right"></i>
      <span>Đăng xuất</span>
    </button>
  </aside>
</template>

<style scoped>
.account-sidebar {
  padding: 16px;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
}

.sidebar-header {
  padding: 4px 6px 12px;
}

.sidebar-caption {
  display: block;
  margin-bottom: 4px;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.sidebar-header strong {
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
  line-height: 1.35;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.sidebar-item {
  width: 100%;
  min-height: 42px;
  padding: 0 14px;
  border: 0;
  border-radius: 10px;
  background: transparent;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #334155;
  font-weight: 600;
  text-decoration: none;
  transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
}

.sidebar-item i {
  width: 18px;
  color: #94a3b8;
  font-size: 17px;
}

.sidebar-item:hover,
.sidebar-item.active {
  background: #eef4ff;
  color: #2563eb;
}

.sidebar-item:hover i,
.sidebar-item.active i {
  color: #2563eb;
}

.sidebar-divider {
  height: 1px;
  margin: 14px 4px 12px;
  background: #e5e7eb;
}

.logout {
  color: #475569;
}

.logout:hover {
  background: #f8fafc;
  color: #dc2626;
}

.logout:hover i {
  color: #dc2626;
}
</style>
