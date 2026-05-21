<template>
  <aside class="admin-sidebar">
    <h2>Admin Store</h2>

    <nav>
      <router-link to="/admin/dashboard">Dashboard</router-link>
      <router-link to="/admin/products">Sản phẩm</router-link>
      <router-link to="/admin/brands">Thương hiệu</router-link>
      <router-link to="/admin/categories">Danh mục</router-link>
      <router-link to="/admin/orders">Đơn hàng</router-link>
      <router-link to="/admin/payments">Thanh toán</router-link>
      <router-link to="/admin/users">Người dùng</router-link>
      <router-link to="/admin/roles">Vai trò</router-link>
      <router-link to="/admin/permissions">Quyền</router-link>
      <router-link to="/admin/stock-logs">Kho</router-link>
      <router-link to="/admin/reports/revenue">Báo cáo</router-link>
      <router-link to="/admin/settings">Cài đặt</router-link>
    </nav>
  </aside>
</template>

<style scoped>
.admin-sidebar {
  width: 250px;
  min-height: 100vh;
  padding: 20px;
  background: var(--sidebar-bg);
  color: #fff;
}

.admin-sidebar h2 {
  margin: 0 0 24px;
}

.admin-sidebar nav {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.admin-sidebar a {
  padding: 10px 12px;
  border-radius: 8px;
  color: #cbd5e1;
}

.admin-sidebar a.router-link-active {
  background: #2563eb;
  color: #fff;
}
</style>
