<template>
  <div class="navbar-wrap">
    <div class="container-fluid px-4">
      <div class="header-nav">
        <button class="category-btn" type="button">
          <i class="bi bi-list"></i>
          <span>Danh mục sản phẩm</span>
        </button>

        <nav class="main-menu">
          <RouterLink
              v-slot="{ href, navigate, isExactActive }"
              to="/"
              custom
          >
            <a :href="href" :class="{ 'is-active': isExactActive }" @click="navigate">Trang chủ</a>
          </RouterLink>

          <RouterLink
              v-slot="{ href, navigate, isExactActive }"
              to="/products"
              custom
          >
            <a :href="href" :class="{ 'is-active': isExactActive }" @click="navigate">Sản phẩm</a>
          </RouterLink>

          <RouterLink
              v-slot="{ href, navigate, isExactActive }"
              to="/phu-kien"
              custom
          >
            <a :href="href" :class="{ 'is-active': isExactActive }" @click="navigate">Phụ kiện</a>
          </RouterLink>

          <RouterLink
              v-slot="{ href, navigate, isExactActive }"
              to="/tin-tuc"
              custom
          >
            <a :href="href" :class="{ 'is-active': isExactActive }" @click="navigate">Tin tức</a>
          </RouterLink>

          <RouterLink
              v-slot="{ href, navigate, isExactActive }"
              to="/lien-he"
              custom
          >
            <a :href="href" :class="{ 'is-active': isExactActive }" @click="navigate">Liên hệ</a>
          </RouterLink>
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
  gap: 150px;
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

.main-menu a.is-active,
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
