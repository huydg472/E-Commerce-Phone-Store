<template>
  <header class="site-header">
    <div class="container-fluid px-4">
      <div class="header-top">
        <RouterLink to="/" class="logo-wrap">
          <div class="logo-icon">
            <i class="bi bi-bag"></i>
          </div>
          <span class="logo-text">
            Zin <strong>Moblie</strong>
          </span>
        </RouterLink>

        <div class="search-box">
          <input type="text" placeholder="Bạn cần tìm gì?" />
          <button>Tìm kiếm</button>
        </div>

        <div class="header-actions">
          <RouterLink to="/auth/login" class="header-action">
            <i class="bi bi-person"></i>
            <span>
              Tài khoản<br />
              Đăng nhập
            </span>
          </RouterLink>

          <RouterLink to="/yeu-thich" class="header-action">
            <i class="bi bi-heart"></i>
            <span>Yêu thích</span>
          </RouterLink>

          <RouterLink to="/gio-hang" class="header-action cart-action">
            <i class="bi bi-cart3"></i>
            <span>Giỏ hàng</span>
            <em>0</em>
          </RouterLink>
        </div>
      </div>

      <div class="header-nav">
        <button class="category-btn">
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
  </header>
</template>

<style scoped>
.site-header {
  background: var(--card-bg);
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 0;
  z-index: 99;
}

.header-top {
  min-height: 66px;
  display: grid;
  grid-template-columns: 300px minmax(420px, 1fr) 520px;
  align-items: center;
  gap: 28px;
  padding: 8px 0;
}

.logo-wrap {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  color: var(--text-color);
  font-weight: 700;
  white-space: nowrap;
}

.logo-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: var(--primary-color);
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 23px;
  flex-shrink: 0;
}

.logo-text {
  font-size: 26px;
  line-height: 1;
  letter-spacing: 0.5px;
}

.logo-text strong {
  color: var(--primary-color);
}

.search-box {
  width: 100%;
  height: 42px;
  display: grid;
  grid-template-columns: 1fr 150px;
}

.search-box input {
  border: 1px solid var(--border-color);
  border-right: none;
  border-radius: 8px 0 0 8px;
  padding: 0 20px;
  outline: none;
  font-size: 14px;
  background: var(--card-bg);
  color: var(--text-color);
}

.search-box input:focus {
  border-color: var(--primary-color);
}

.search-box button {
  border: none;
  background: var(--primary-color);
  color: #ffffff;
  border-radius: 0 8px 8px 0;
  font-weight: 700;
  font-size: 14px;
}

.search-box button:hover {
  background: var(--primary-hover);
}

.header-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 34px;
  overflow: visible;
}

.header-action {
  min-height: 42px;
  color: var(--text-color);
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  line-height: 1.25;
  font-weight: 600;
  position: relative;
  white-space: nowrap;
  overflow: visible;
}

.header-action i {
  font-size: 28px;
  line-height: 1;
  color: var(--text-color);
}

.header-action:hover,
.header-action:hover i {
  color: var(--primary-color);
}

.cart-action {
  padding-right: 12px;
}

.header-action em {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 20px;
  height: 20px;
  border: 2px solid #ffffff;
  border-radius: 999px;
  background: var(--primary-color);
  color: #ffffff;
  font-size: 11px;
  font-style: normal;
  font-weight: 700;
  line-height: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  z-index: 3;
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
}

.main-menu a.router-link-active,
.main-menu a:hover {
  color: var(--primary-color);
}

@media (max-width: 1200px) {
  .header-top {
    grid-template-columns: 240px 1fr;
  }

  .header-actions {
    grid-column: 1 / -1;
    justify-content: center;
    padding-bottom: 12px;
  }

  .header-nav {
    gap: 30px;
  }
}

@media (max-width: 900px) {

  .header-top,
  .header-nav {
    grid-template-columns: 1fr;
  }

  .header-top {
    gap: 14px;
  }

  .header-nav {
    gap: 16px;
    padding-bottom: 12px;
  }

  .header-actions,
  .main-menu {
    flex-wrap: wrap;
    justify-content: center;
    gap: 18px;
  }

  .main-menu {
    max-width: 100%;
  }

  .search-box {
    grid-template-columns: 1fr;
    height: auto;
    gap: 8px;
  }

  .search-box input,
  .search-box button {
    height: 42px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
  }
}

@media (max-width: 576px) {
  .logo-text {
    font-size: 22px;
  }

  .header-actions {
    align-items: flex-start;
  }

  .header-action i {
    font-size: 24px;
  }

  .main-menu {
    justify-content: flex-start;
  }
}
</style>
