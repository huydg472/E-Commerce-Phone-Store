<template>
  <div class="navbar-wrap">
    <div class="container-fluid px-4">
      <div class="header-nav">
        <button class="category-btn" type="button">
          <i class="bi bi-list"></i>
          <span>Danh mục sản phẩm</span>
        </button>

        <nav class="main-menu">
          <RouterLink to="/">Trang chủ</RouterLink>
          <RouterLink to="/apple">Apple</RouterLink>
          <RouterLink to="/samsung">Samsung</RouterLink>
          <RouterLink to="/phu-kien">Phụ kiện</RouterLink>
          <RouterLink to="/tin-tuc">Tin tức</RouterLink>
          <RouterLink to="/lien-he">Liên hệ</RouterLink>
        </nav>
      </div>
    </div>
  </div>
</template>

<style scoped>
.navbar-wrap {
  background: var(--card-bg);
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 66px;
  z-index: 99;
}

.header-nav {
  min-height: 50px;
  display: grid;
  grid-template-columns: 255px 1fr;
  align-items: center;
  gap: 30px;
  padding: 8px 0;
}

.category-btn {
  height: 40px;
  border: none;
  border-radius: 8px;
  background: var(--primary-color);
  color: #ffffff;
  font-weight: 700;
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: flex-start;
  gap: 10px;
  padding: 0 10px;
  width: 100%;
  max-width: 205px;
}

.category-btn:hover {
  background: var(--primary-hover);
}

.category-btn i {
  font-size: 18px;
}

.main-menu {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 90px;
  max-width: 100%;
}

.main-menu a {
  color: var(--text-color);
  font-weight: 700;
  font-size: 16px;
  line-height: 1.4;
  text-decoration: none;
}

.main-menu a.router-link-active,
.main-menu a:hover {
  color: var(--primary-color);
}

@media (max-width: 1200px) {
  .navbar-wrap {
    top: 130px;
  }

  .header-nav {
    gap: 30px;
  }

  .main-menu {
    gap: 50px;
  }
}

@media (max-width: 900px) {
  .navbar-wrap {
    position: static;
  }

  .header-nav {
    grid-template-columns: 1fr;
    gap: 16px;
    padding-bottom: 12px;
  }

  .main-menu {
    flex-wrap: wrap;
    justify-content: center;
    gap: 18px;
    max-width: 100%;
  }
}

@media (max-width: 576px) {
  .main-menu {
    justify-content: flex-start;
  }
}
</style>