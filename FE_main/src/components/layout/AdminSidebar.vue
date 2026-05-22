<script setup>
const props = defineProps({
  collapsed: {
    type: Boolean,
    default: false,
  },
  mobileOpen: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['toggle', 'close-mobile'])

const menuGroups = [
  {
    title: 'TỔNG QUAN',
    items: [
      {
        label: 'Dashboard',
        icon: 'bi-house-fill',
        to: '/admin/dashboard',
      },
    ],
  },
  {
    title: 'QUẢN LÝ CỬA HÀNG',
    items: [
      {
        label: 'Sản phẩm',
        icon: 'bi-phone',
        to: '/admin/products',
      },
      {
        label: 'Danh mục',
        icon: 'bi-list-ul',
        to: '/admin/categories',
      },
      {
        label: 'Đơn hàng',
        icon: 'bi-cart3',
        to: '/admin/orders',
      },
      {
        label: 'Khách hàng',
        icon: 'bi-people-fill',
        to: '/admin/customers',
      },
      {
        label: 'Khuyến mãi',
        icon: 'bi-patch-check-fill',
        to: '/admin/promotions',
      },
    ],
  },
  {
    title: 'QUẢN TRỊ HỆ THỐNG',
    items: [
      {
        label: 'Tài khoản',
        icon: 'bi-person-fill',
        to: '/admin/users',
      },
      {
        label: 'Phân quyền',
        icon: 'bi-shield-shaded',
        to: '/admin/roles',
      },
      {
        label: 'Thống kê',
        icon: 'bi-bar-chart-line-fill',
        to: '/admin/reports',
      },
      {
        label: 'Cài đặt',
        icon: 'bi-gear-fill',
        to: '/admin/settings',
      },
    ],
  },
]
</script>

<template>
  <aside class="admin-sidebar" :class="{
    collapsed: props.collapsed,
    'mobile-open': props.mobileOpen,
  }">
    <div class="sidebar-brand">
      <div class="brand-logo">
        <i class="bi bi-phone"></i>
      </div>

      <div class="brand-content">
        <h1>Zin Mobile</h1>
        <p>Admin Dashboard</p>
      </div>

      <button class="btn close-sidebar-btn d-lg-none" type="button" @click="emit('close-mobile')">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <nav class="sidebar-nav">
      <div v-for="group in menuGroups" :key="group.title" class="nav-group">
        <p class="nav-title">{{ group.title }}</p>

        <RouterLink v-for="item in group.items" :key="item.label" :to="item.to" class="nav-link-item"
          active-class="active" @click="emit('close-mobile')">
          <i class="bi" :class="item.icon"></i>
          <span>{{ item.label }}</span>
        </RouterLink>
      </div>
    </nav>

    <button class="collapse-btn" type="button" @click="emit('toggle')">
      <span class="collapse-icon">
        <i class="bi" :class="props.collapsed ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
      </span>
      <span>Thu gọn</span>
    </button>
  </aside>
</template>

<style scoped>
.admin-sidebar {
  position: fixed;
  inset: 0 auto 0 0;
  z-index: 1040;
  width: 290px;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  color: #dbeafe;
  background:
    radial-gradient(circle at top left, rgba(14, 165, 233, 0.2), transparent 36%),
    linear-gradient(180deg, #061833 0%, #021124 100%);
  box-shadow: 16px 0 34px rgba(15, 23, 42, 0.12);
  transition: width 0.25s ease, transform 0.25s ease;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 27px 18px 26px 32px;
}

.brand-logo {
  width: 38px;
  height: 58px;
  flex: 0 0 auto;
  display: grid;
  place-items: center;
  border: 3px solid #1294ff;
  border-radius: 9px;
  color: #1294ff;
  font-size: 24px;
  box-shadow: inset 0 0 14px rgba(18, 148, 255, 0.22);
}

.brand-content h1 {
  margin: 0;
  color: #ffffff;
  font-size: 27px;
  font-weight: 800;
  line-height: 1.15;
  white-space: nowrap;
}

.brand-content p {
  margin: 4px 0 0;
  color: #d4d9e4;
  font-size: 18px;
  line-height: 1.2;
  white-space: nowrap;
}

.close-sidebar-btn {
  margin-left: auto;
  color: #ffffff;
  border: 1px solid rgba(255, 255, 255, 0.18);
}

.sidebar-nav {
  flex: 1;
  padding: 0 18px 16px;
  overflow-y: auto;
}

.sidebar-nav::-webkit-scrollbar {
  width: 0;
}

.nav-group {
  margin-bottom: 24px;
}

.nav-title {
  margin: 0 0 12px 12px;
  color: #cbd5e1;
  font-size: 14px;
  font-weight: 800;
  letter-spacing: 0.045em;
}

.nav-link-item {
  min-height: 54px;
  display: flex;
  align-items: center;
  gap: 19px;
  padding: 0 18px;
  margin-bottom: 8px;
  color: #e2e8f0;
  text-decoration: none;
  border-radius: 8px;
  font-size: 18px;
  font-weight: 500;
  transition: all 0.18s ease;
}

.nav-link-item i {
  width: 28px;
  font-size: 24px;
  text-align: center;
  color: rgba(226, 232, 240, 0.86);
}

.nav-link-item:hover,
.nav-link-item.active {
  color: #ffffff;
  background: linear-gradient(135deg, #0d8cff, #0069ff);
  box-shadow: 0 12px 25px rgba(0, 105, 255, 0.25);
}

.nav-link-item:hover i,
.nav-link-item.active i {
  color: #ffffff;
}

.collapse-btn {
  display: flex;
  align-items: center;
  gap: 20px;
  min-height: 70px;
  margin: 0 23px 24px;
  padding: 0 13px;
  color: #dbeafe;
  background: transparent;
  border: 0;
  border-top: 1px solid rgba(148, 163, 184, 0.18);
  font-size: 18px;
}

.collapse-icon {
  width: 32px;
  height: 32px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  color: #0f172a;
  background: #cbd5e1;
}

.admin-sidebar.collapsed {
  width: 94px;
}

.admin-sidebar.collapsed .brand-content,
.admin-sidebar.collapsed .nav-title,
.admin-sidebar.collapsed .nav-link-item span,
.admin-sidebar.collapsed .collapse-btn span:last-child {
  display: none;
}

.admin-sidebar.collapsed .sidebar-brand {
  justify-content: center;
  padding-inline: 16px;
}

.admin-sidebar.collapsed .sidebar-nav {
  padding-inline: 14px;
}

.admin-sidebar.collapsed .nav-link-item {
  justify-content: center;
  padding-inline: 0;
}

.admin-sidebar.collapsed .collapse-btn {
  justify-content: center;
  margin-inline: 14px;
}

@media (max-width: 991.98px) {

  .admin-sidebar,
  .admin-sidebar.collapsed {
    width: 290px;
    transform: translateX(-105%);
  }

  .admin-sidebar.mobile-open,
  .admin-sidebar.collapsed.mobile-open {
    transform: translateX(0);
  }

  .admin-sidebar.collapsed .brand-content,
  .admin-sidebar.collapsed .nav-title,
  .admin-sidebar.collapsed .nav-link-item span,
  .admin-sidebar.collapsed .collapse-btn span:last-child {
    display: block;
  }

  .admin-sidebar.collapsed .sidebar-brand,
  .admin-sidebar.collapsed .nav-link-item,
  .admin-sidebar.collapsed .collapse-btn {
    justify-content: flex-start;
  }
}
</style>
