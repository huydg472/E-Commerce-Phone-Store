<template>
  <main class="account-page">
    <div class="container py-4">
      <!-- Breadcrumb -->
      <nav class="account-breadcrumb mb-2">
        <span>Trang chủ</span>
        <span>/</span>
        <strong>Tài khoản của tôi</strong>
      </nav>

      <h1 class="page-title mb-3">Tài khoản của tôi</h1>

      <div class="row g-4">
        <!-- Sidebar -->
        <div class="col-lg-3">
          <aside class="account-sidebar">
            <button
                v-for="item in menuItems"
                :key="item.key"
                class="sidebar-item"
                :class="{ active: activeMenu === item.key }"
                @click="activeMenu = item.key"
            >
              <i :class="item.icon"></i>
              <span>{{ item.label }}</span>
            </button>

            <div class="sidebar-divider"></div>

            <button class="sidebar-item logout">
              <i class="bi bi-box-arrow-right"></i>
              <span>Đăng xuất</span>
            </button>
          </aside>
        </div>

        <!-- Main Content -->
        <div class="col-lg-9">
          <!-- Account Overview -->
          <section class="account-card mb-3">
            <h5 class="section-title mb-3">Tổng quan tài khoản</h5>

            <div class="account-overview">
              <div class="user-profile">
                <div class="avatar-box">
                  <img :src="user.avatar" alt="Avatar"/>
                </div>

                <button class="btn btn-outline-primary btn-upload">
                  <i class="bi bi-camera"></i>
                  Cập nhật ảnh
                </button>
              </div>

              <div class="user-info">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                  <h4 class="user-name mb-0">{{ user.name }}</h4>
                  <span class="member-badge">Thành viên Bạc</span>
                </div>

                <p>
                  <i class="bi bi-telephone"></i>
                  {{ user.phone }}
                </p>

                <p>
                  <i class="bi bi-envelope"></i>
                  {{ user.email }}
                </p>

                <p>
                  <i class="bi bi-clock-history"></i>
                  Tham gia ngày {{ user.joinedAt }}
                </p>
              </div>

              <div class="overview-stats">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="stat-item"
                >
                  <div class="stat-icon">
                    <i :class="stat.icon"></i>
                  </div>

                  <strong>{{ stat.value }}</strong>
                  <span>{{ stat.label }}</span>
                  <a href="#">Xem chi tiết</a>
                </div>
              </div>
            </div>
          </section>

          <!-- Info + Default Address -->
          <div class="row g-3 mb-3">
            <div class="col-xl-7">
              <section class="account-card h-100">
                <h5 class="section-title mb-3">Thông tin cá nhân</h5>

                <form @submit.prevent="saveProfile">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label">
                        Họ và tên <span>*</span>
                      </label>
                      <input
                          v-model="form.name"
                          type="text"
                          class="form-control"
                      />
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Ngày sinh</label>
                      <div class="input-icon">
                        <input
                            v-model="form.birthday"
                            type="text"
                            class="form-control"
                        />
                        <i class="bi bi-calendar-event"></i>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">
                        Email <span>*</span>
                      </label>
                      <input
                          v-model="form.email"
                          type="email"
                          class="form-control"
                      />
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">Giới tính</label>

                      <div class="gender-group">
                        <label>
                          <input
                              v-model="form.gender"
                              type="radio"
                              value="Nam"
                          />
                          Nam
                        </label>

                        <label>
                          <input
                              v-model="form.gender"
                              type="radio"
                              value="Nữ"
                          />
                          Nữ
                        </label>

                        <label>
                          <input
                              v-model="form.gender"
                              type="radio"
                              value="Khác"
                          />
                          Khác
                        </label>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <label class="form-label">
                        Số điện thoại <span>*</span>
                      </label>
                      <input
                          v-model="form.phone"
                          type="text"
                          class="form-control"
                      />
                    </div>
                  </div>

                  <div class="form-actions mt-3">
                    <button type="submit" class="btn btn-primary">
                      <i class="bi bi-save"></i>
                      Lưu thay đổi
                    </button>

                    <button type="button" class="btn btn-light border">
                      Hủy bỏ
                    </button>
                  </div>
                </form>
              </section>
            </div>

            <div class="col-xl-5">
              <section class="account-card h-100">
                <h5 class="section-title mb-4">Địa chỉ mặc định</h5>

                <div class="default-address">
                  <div class="address-title">
                    <i class="bi bi-geo-alt"></i>
                    <strong>Nhà riêng</strong>
                    <span>Mặc định</span>
                  </div>

                  <p>
                    123 Đường Nguyễn Huệ, Phường Bến Nghé,<br/>
                    Quận 1, TP. Hồ Chí Minh
                  </p>

                  <p>
                    <strong>Số điện thoại:</strong>
                    {{ user.phone }}
                  </p>

                  <button class="btn btn-outline-primary btn-manage">
                    Quản lý địa chỉ
                  </button>
                </div>
              </section>
            </div>
          </div>

          <!-- Bottom Cards -->
          <div class="row g-3">
            <div class="col-md-4">
              <section class="mini-card">
                <div class="mini-content">
                  <div>
                    <h6>
                      <i class="bi bi-geo-alt"></i>
                      Sổ địa chỉ
                    </h6>

                    <p>
                      Bạn có <strong>3 địa chỉ</strong> đã lưu
                    </p>

                    <small>Quản lý và cập nhật địa chỉ giao hàng của bạn</small>
                  </div>

                  <img src="https://placehold.co/92x64/eaf1ff/0d6efd?text=Map" alt=""/>
                </div>

                <button class="btn btn-outline-primary btn-sm">
                  Xem tất cả
                  <i class="bi bi-arrow-right"></i>
                </button>
              </section>
            </div>

            <div class="col-md-4">
              <section class="mini-card">
                <div class="mini-content">
                  <div>
                    <h6>
                      <i class="bi bi-shield-lock"></i>
                      Bảo mật tài khoản
                    </h6>

                    <p>Đổi mật khẩu định kỳ để bảo mật tài khoản</p>

                    <small>Cập nhật mật khẩu, quản lý thiết bị đăng nhập</small>
                  </div>

                  <img src="https://placehold.co/76x76/eaf1ff/0d6efd?text=Lock" alt=""/>
                </div>

                <button class="btn btn-outline-primary btn-sm">
                  Đổi mật khẩu
                  <i class="bi bi-arrow-right"></i>
                </button>
              </section>
            </div>

            <div class="col-md-4">
              <section class="mini-card">
                <div class="mini-content">
                  <div>
                    <h6>
                      <i class="bi bi-award"></i>
                      Ưu đãi thành viên
                    </h6>

                    <p>Thành viên Bạc</p>

                    <small>
                      Bạn còn <strong>1.250</strong> điểm thưởng
                    </small>
                  </div>

                  <img src="https://placehold.co/76x76/eaf1ff/0d6efd?text=Gift" alt=""/>
                </div>

                <button class="btn btn-outline-primary btn-sm">
                  Xem chi tiết
                  <i class="bi bi-arrow-right"></i>
                </button>
              </section>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script setup>
import {reactive, ref} from 'vue'

const activeMenu = ref('overview')

const user = reactive({
  name: 'Nguyễn Văn A',
  phone: '0901 234 567',
  email: 'nguyenvana@gmail.com',
  joinedAt: '12/03/2024',
  avatar: 'https://placehold.co/120x120/e9ecef/adb5bd?text=User',
})

const form = reactive({
  name: 'Nguyễn Văn A',
  birthday: '15/06/1990',
  email: 'nguyenvana@gmail.com',
  phone: '0901 234 567',
  gender: 'Nam',
})

const menuItems = [
  {
    key: 'overview',
    label: 'Tổng quan',
    icon: 'bi bi-house-door',
  },
  {
    key: 'profile',
    label: 'Thông tin cá nhân',
    icon: 'bi bi-person',
  },
  {
    key: 'address',
    label: 'Sổ địa chỉ',
    icon: 'bi bi-geo-alt',
  },
  {
    key: 'password',
    label: 'Đổi mật khẩu',
    icon: 'bi bi-lock',
  },
  {
    key: 'wishlist',
    label: 'Yêu thích',
    icon: 'bi bi-heart',
  },
  {
    key: 'orders',
    label: 'Đơn hàng của tôi',
    icon: 'bi bi-bag',
  },
]

const stats = [
  {
    value: 12,
    label: 'Đơn hàng',
    icon: 'bi bi-bag',
  },
  {
    value: 3,
    label: 'Địa chỉ',
    icon: 'bi bi-geo-alt',
  },
  {
    value: 8,
    label: 'Sản phẩm yêu thích',
    icon: 'bi bi-heart',
  },
]

const saveProfile = () => {
  console.log('Thông tin cá nhân:', form)
}
</script>

<style scoped>
.account-page {
  background: #fff;
  color: #0f172a;
  font-size: 14px;
}

.account-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6b7280;
  font-size: 14px;
}

.account-breadcrumb strong {
  color: #0d6efd;
  font-weight: 600;
}

.page-title {
  font-size: 28px;
  font-weight: 700;
  color: #111827;
}

.account-sidebar,
.account-card,
.mini-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
}

.account-sidebar {
  padding: 16px;
  min-height: 520px;
}

.sidebar-item {
  width: 100%;
  height: 42px;
  border: none;
  background: transparent;
  border-radius: 6px;
  padding: 0 14px;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #374151;
  font-weight: 500;
  margin-bottom: 4px;
  transition: 0.2s;
}

.sidebar-item i {
  font-size: 18px;
  color: #64748b;
}

.sidebar-item:hover,
.sidebar-item.active {
  background: #eef4ff;
  color: #0d6efd;
}

.sidebar-item:hover i,
.sidebar-item.active i {
  color: #0d6efd;
}

.sidebar-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 18px 0;
}

.sidebar-item.logout {
  color: #475569;
}

.account-card {
  padding: 18px 22px;
}

.section-title {
  font-size: 17px;
  font-weight: 700;
  color: #111827;
}

.account-overview {
  display: grid;
  grid-template-columns: 150px 1fr 1.6fr;
  align-items: center;
  gap: 24px;
}

.user-profile {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
}

.avatar-box {
  width: 116px;
  height: 116px;
  border-radius: 50%;
  overflow: hidden;
  background: #f1f5f9;
}

.avatar-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.btn-upload {
  min-width: 128px;
  height: 32px;
  font-size: 13px;
  padding: 0 12px;
}

.user-name {
  font-size: 20px;
  font-weight: 700;
}

.member-badge {
  background: #eef4ff;
  color: #0d6efd;
  padding: 5px 16px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 500;
}

.user-info p {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
  color: #334155;
}

.user-info i {
  color: #475569;
  font-size: 17px;
}

.overview-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  border-left: 1px solid #e5e7eb;
}

.stat-item {
  min-height: 105px;
  text-align: center;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.stat-item:last-child {
  border-right: none;
}

.stat-icon {
  width: 38px;
  height: 38px;
  background: #eef4ff;
  color: #0d6efd;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 18px;
  margin-bottom: 6px;
}

.stat-item strong {
  font-size: 22px;
  color: #111827;
  line-height: 1;
}

.stat-item span {
  color: #374151;
  margin-top: 4px;
}

.stat-item a {
  color: #0d6efd;
  text-decoration: none;
  font-size: 13px;
  margin-top: 6px;
}

.form-label {
  font-size: 14px;
  font-weight: 600;
  color: #334155;
  margin-bottom: 6px;
}

.form-label span {
  color: #dc3545;
}

.form-control {
  height: 36px;
  border-color: #dfe3ea;
  font-size: 14px;
}

.form-control:focus {
  box-shadow: none;
  border-color: #0d6efd;
}

.input-icon {
  position: relative;
}

.input-icon i {
  position: absolute;
  top: 50%;
  right: 12px;
  transform: translateY(-50%);
  color: #64748b;
}

.gender-group {
  height: 36px;
  display: flex;
  align-items: center;
  gap: 28px;
}

.gender-group label {
  display: flex;
  align-items: center;
  gap: 7px;
  color: #334155;
  font-weight: 500;
}

.gender-group input {
  accent-color: #0d6efd;
}

.form-actions {
  display: flex;
  gap: 12px;
}

.form-actions .btn {
  height: 36px;
  min-width: 118px;
  font-size: 14px;
}

.default-address {
  color: #334155;
}

.address-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 18px;
}

.address-title i {
  color: #0d6efd;
  font-size: 18px;
}

.address-title span {
  background: #eef4ff;
  color: #0d6efd;
  padding: 5px 14px;
  border-radius: 999px;
  font-size: 13px;
}

.default-address p {
  margin-bottom: 14px;
  line-height: 1.7;
}

.btn-manage {
  min-width: 140px;
  height: 36px;
  font-size: 14px;
}

.mini-card {
  padding: 16px;
  height: 100%;
}

.mini-content {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  min-height: 90px;
}

.mini-content h6 {
  font-weight: 700;
  color: #111827;
  margin-bottom: 10px;
}

.mini-content h6 i {
  color: #0d6efd;
  margin-right: 8px;
}

.mini-content p {
  margin-bottom: 2px;
  color: #334155;
}

.mini-content small {
  color: #64748b;
  line-height: 1.5;
}

.mini-content img {
  width: 76px;
  height: 76px;
  object-fit: contain;
  flex-shrink: 0;
}

.mini-card .btn {
  min-width: 126px;
  height: 34px;
  margin-top: 12px;
  font-size: 13px;
}

@media (max-width: 1200px) {
  .account-overview {
    grid-template-columns: 130px 1fr;
  }

  .overview-stats {
    grid-column: 1 / -1;
    border-left: none;
    border-top: 1px solid #e5e7eb;
    padding-top: 16px;
  }
}

@media (max-width: 768px) {
  .page-title {
    font-size: 24px;
  }

  .account-sidebar {
    min-height: auto;
  }

  .account-overview {
    grid-template-columns: 1fr;
    text-align: center;
  }

  .user-info p {
    justify-content: center;
  }

  .overview-stats {
    grid-template-columns: 1fr;
  }

  .stat-item {
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
  }

  .stat-item:last-child {
    border-bottom: none;
  }

  .form-actions {
    flex-direction: column;
  }

  .form-actions .btn {
    width: 100%;
  }
}
</style>