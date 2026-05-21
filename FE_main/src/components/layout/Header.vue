<template>
  <header class="site-header">
    <div class="container header-inner">
      <router-link to="/" class="logo">Phone Store</router-link>

      <nav class="nav">
        <router-link to="/">Trang chủ</router-link>
        <router-link to="/products">Sản phẩm</router-link>
        <router-link to="/cart">Giỏ hàng</router-link>
        <router-link to="/auth/login">Đăng nhập</router-link>
      </nav>
    </div>
  </header>
</template>

<style scoped>
.site-header {
  background: #fff;
  border-bottom: 1px solid #e2e8f0;
}

.header-inner {
  height: 64px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  font-weight: 800;
  color: #2563eb;
}

.nav {
  display: flex;
  gap: 18px;
}
</style>
