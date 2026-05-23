<script setup lang="ts">
import {RouterView} from 'vue-router';

</script>

<template>
  <div class="auth-page container-fluid min-vh-100 d-flex align-items-center justify-content-center p-2">
    <div class="auth-card row g-0 w-100 overflow-hidden">
      <!-- LEFT -->
      <div class="auth-left col-lg-7 d-none d-lg-block position-relative">
        <div class="left-content position-relative z-2">
          <h2 class="fw-bold mb-3">Chào mừng bạn đến với</h2>

          <h1 class="brand-title fw-bold mb-4">Zin<span>Mobile</span></h1>

          <p class="description mb-5">
            Đăng nhập để trải nghiệm mua sắm dễ dàng, nhận nhiều ưu đãi và quản
            lý đơn hàng nhanh chóng.
          </p>

          <div class="d-flex flex-column gap-4">
            <div class="d-flex align-items-start gap-4">
              <div class="icon-box">
                <i class="bi bi-bullseye"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-2">Ưu đãi dành riêng cho bạn</h5>
                <p class="mb-0 text-dark">
                  Nhận thông tin khuyến mãi và giảm giá độc quyền mỗi ngày.
                </p>
              </div>
            </div>

            <div class="d-flex align-items-start gap-4">
              <div class="icon-box">
                <i class="bi bi-cart-check"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-2">Quản lý đơn hàng dễ dàng</h5>
                <p class="mb-0 text-dark">
                  Theo dõi đơn hàng, lịch sử mua sắm và bảo hành nhanh chóng.
                </p>
              </div>
            </div>

            <div class="d-flex align-items-start gap-4">
              <div class="icon-box">
                <i class="bi bi-shield-check"></i>
              </div>
              <div>
                <h5 class="fw-bold mb-2">Thanh toán nhanh chóng, an toàn</h5>
                <p class="mb-0 text-dark">
                  Lưu thông tin, thanh toán tiện lợi và bảo mật tuyệt đối.
                </p>
              </div>
            </div>
          </div>

          <div class="safe-box d-flex align-items-start gap-3 mt-5">
            <div class="safe-icon">
              <i class="bi bi-check-lg"></i>
            </div>

            <div>
              <h5 class="fw-bold mb-2">Mua sắm an tâm tại ZinMobile</h5>
              <p class="mb-0">
                Cam kết hàng chính hãng 100%, bảo hành đầy đủ, hỗ trợ tận tâm.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="auth-right col-12 col-lg-5 bg-white d-flex justify-content-center align-items-start">
        <RouterView/>
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  background: #f4f8ff;
  font-family: Arial, Helvetica, sans-serif;
}

.auth-card {
  max-width: 1480px;
  min-height: 820px;
  border-radius: 18px;
  background: #ffffff;
  border: 1px solid #e4ecf7;
  box-shadow: 0 10px 35px rgba(15, 23, 42, 0.08);
}

.auth-left {
  padding: 64px 70px;
  background: linear-gradient(135deg, #eaf4ff 0%, #d9eaff 55%, #eef6ff 100%);
  overflow: hidden;
}

.left-content {
  max-width: 570px;
}

.left-content h2 {
  font-size: 34px;
  color: #061c46;
}

.brand-title {
  font-size: 62px;
  color: #061c46;
  letter-spacing: -2px;
}

.brand-title span {
  color: #0066ff;
}

.description {
  max-width: 590px;
  font-size: 20px;
  line-height: 1.65;
  color: #1e355c;
}

.icon-box {
  width: 64px;
  height: 64px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.78);
  border: 1px solid rgba(255, 255, 255, 0.9);
  color: #0066ff;
  font-size: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.auth-left h5 {
  color: #061c46;
  font-size: 20px;
}

.auth-left p {
  font-size: 18px;
  line-height: 1.6;
}

.safe-box {
  width: 710px;
  padding: 24px 28px;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.55);
  border: 1px solid rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
}

.safe-box h5 {
  color: #0066ff;
  font-size: 18px;
}

.safe-box p {
  color: #334155;
  font-size: 16px;
}

.safe-icon {
  width: 58px;
  height: 58px;
  border: 3px solid #0066ff;
  border-radius: 16px;
  color: #0066ff;
  font-size: 26px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.auth-right {
  padding: 40px 64px;
}

@media (max-width: 1400px) {
  .auth-left {
    padding: 56px 52px;
  }

  .logo-preview {
    width: 330px;
    height: 250px;
    right: 32px;
    bottom: 60px;
  }

  .safe-box {
    width: 390px;
  }
}

@media (max-width: 990px) {
  .auth-card {
    min-height: auto;
  }

  .auth-right {
    padding: 32px 20px;
  }
}
</style>
