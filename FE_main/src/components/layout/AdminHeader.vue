<script setup>
const emit = defineEmits(['open-sidebar'])
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

    <div class="header-right">
      <form class="search-box" @submit.prevent>
        <i class="bi bi-search"></i>
        <input type="search" placeholder="Tìm kiếm sản phẩm, đơn hàng, khách hàng..." />
      </form>

      <button class="notification-btn" type="button" aria-label="Thông báo">
        <i class="bi bi-bell"></i>
        <span>3</span>
      </button>

      <div class="dropdown">
        <button class="admin-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="admin-avatar">
            <i class="bi bi-person-fill"></i>
          </span>

          <span class="admin-info">
            <strong>Admin</strong>
            <small>Quản trị viên</small>
          </span>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li><a class="dropdown-item" href="#">Thông tin tài khoản</a></li>
          <li><a class="dropdown-item" href="#">Cài đặt</a></li>
          <li>
            <hr class="dropdown-divider" />
          </li>
          <li><a class="dropdown-item text-danger" href="#">Đăng xuất</a></li>
        </ul>
      </div>
    </div>
  </header>
</template>

<style scoped>
.admin-header {
  min-height: 104px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  padding: 22px 34px 0;
  background: #f6f8fc;
}

.header-left {
  min-width: 280px;
  display: flex;
  align-items: center;
  gap: 14px;
}

.page-title h1 {
  margin: 0;
  color: #0b1220;
  font-size: 32px;
  font-weight: 800;
  letter-spacing: 0.01em;
}

.page-title p {
  margin: 6px 0 0;
  color: #475569;
  font-size: 17px;
}

.header-right {
  min-width: 0;
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 22px;
  flex: 1;
}

.search-box {
  width: min(100%, 470px);
  height: 58px;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 0 18px;
  background: #ffffff;
  border: 1px solid #d8dee9;
  border-radius: 10px;
  box-shadow: 0 7px 22px rgba(15, 23, 42, 0.03);
}

.search-box i {
  color: #64748b;
  font-size: 20px;
}

.search-box input {
  width: 100%;
  min-width: 0;
  border: 0;
  outline: 0;
  color: #0f172a;
  background: transparent;
  font-size: 16px;
}

.search-box input::placeholder {
  color: #64748b;
}

.notification-btn {
  position: relative;
  width: 44px;
  height: 44px;
  flex: 0 0 auto;
  display: grid;
  place-items: center;
  color: #475569;
  background: transparent;
  border: 0;
  font-size: 25px;
}

.notification-btn span {
  position: absolute;
  top: 0;
  right: 1px;
  min-width: 24px;
  height: 24px;
  display: grid;
  place-items: center;
  padding: 0 6px;
  color: #ffffff;
  background: #ef174a;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 800;
  box-shadow: 0 0 0 3px #f6f8fc;
}

.admin-user {
  min-width: 205px;
  height: 70px;
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 8px 16px 8px 12px;
  background: #ffffff;
  border: 1px solid #d8dee9;
  border-radius: 10px;
  color: #0f172a;
}

.admin-avatar {
  width: 50px;
  height: 50px;
  flex: 0 0 auto;
  display: grid;
  place-items: center;
  overflow: hidden;
  color: #ffffff;
  background: linear-gradient(180deg, #bfdbfe 0%, #1d4ed8 72%);
  border-radius: 50%;
  font-size: 29px;
}

.admin-info {
  display: flex;
  flex-direction: column;
  text-align: left;
  line-height: 1.1;
}

.admin-info strong {
  font-size: 16px;
  font-weight: 800;
}

.admin-info small {
  margin-top: 5px;
  color: #64748b;
  font-size: 13px;
}

.menu-btn {
  width: 46px;
  height: 46px;
  flex: 0 0 auto;
  display: grid;
  place-items: center;
  background: #ffffff;
  border: 1px solid #d8dee9;
  border-radius: 10px;
  font-size: 25px;
  color: #0f172a;
}

@media (max-width: 1399.98px) {
  .admin-header {
    gap: 18px;
  }

  .header-left {
    min-width: 300px;
  }

  .search-box {
    width: min(100%, 430px);
  }

  .admin-user {
    min-width: 190px;
  }
}

@media (max-width: 1199.98px) {
  .admin-header {
    align-items: flex-start;
    flex-direction: column;
  }

  .header-left,
  .header-right,
  .search-box {
    width: 100%;
  }

  .header-left {
    min-width: 0;
  }
}

@media (max-width: 767.98px) {
  .admin-header {
    min-height: auto;
    padding: 16px 16px 0;
  }

  .page-title h1 {
    font-size: 27px;
  }

  .page-title p {
    font-size: 15px;
  }

  .header-right {
    flex-wrap: wrap;
    gap: 10px;
  }

  .search-box {
    order: 2;
    height: 52px;
  }

  .notification-btn {
    margin-left: auto;
  }

  .admin-user {
    min-width: auto;
    width: 62px;
    padding: 6px;
  }

  .admin-info {
    display: none;
  }
}
</style>