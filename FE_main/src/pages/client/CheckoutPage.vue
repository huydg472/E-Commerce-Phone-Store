<script setup>
import PaymentMethod from '@/components/payment/PaymentMethod.vue'
import OrderSummary from '@/components/order/OrderSummary.vue'

const shippingMethods = [
  {
    id: 'standard',
    title: 'Giao hàng tiêu chuẩn',
    fee: 'Miễn phí',
    icon: 'bi-truck',
    active: true,
  },
  {
    id: 'express',
    title: 'Giao hàng nhanh',
    fee: '+40.000đ',
    icon: 'bi-truck-flatbed',
    active: false,
  },
]
</script>

<template>
  <section class="checkout-page">
    <div class="checkout-container">
      <div class="breadcrumb-area">
        <RouterLink to="/">Trang chủ</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <RouterLink to="/cart">Giỏ hàng</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <span>Thanh toán</span>
      </div>

      <h1 class="page-title">Thanh toán</h1>

      <div class="checkout-layout">
        <div class="checkout-left">
          <div class="checkout-card">
            <div class="section-title">
              <i class="bi bi-person"></i>
              <h2>Thông tin nhận hàng</h2>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label>Họ và tên <span>*</span></label>
                <input type="text" class="form-control" placeholder="Nhập họ và tên" />
              </div>

              <div class="form-group">
                <label>Số điện thoại <span>*</span></label>
                <input type="text" class="form-control" placeholder="Nhập số điện thoại" />
              </div>

              <div class="form-group">
                <label>Email <span>*</span></label>
                <input type="email" class="form-control" placeholder="Nhập email" />
              </div>

              <div class="form-group">
                <label>Tỉnh/Thành phố <span>*</span></label>
                <select class="form-select">
                  <option>Chọn tỉnh/thành phố</option>
                </select>
              </div>

              <div class="form-group">
                <label>Quận/Huyện <span>*</span></label>
                <select class="form-select">
                  <option>Chọn quận/huyện</option>
                </select>
              </div>

              <div class="form-group">
                <label>Phường/Xã <span>*</span></label>
                <select class="form-select">
                  <option>Chọn phường/xã</option>
                </select>
              </div>
            </div>

            <div class="form-group form-group-full">
              <label>Địa chỉ cụ thể <span>*</span></label>
              <input
                  type="text"
                  class="form-control"
                  placeholder="Số nhà, tên đường, tòa nhà, căn hộ..."
              />
            </div>

            <div class="form-group form-group-full note-group">
              <label>Ghi chú đơn hàng</label>
              <textarea
                  class="form-control"
                  rows="3"
                  placeholder="Ví dụ: Giao hàng giờ hành chính, gọi trước khi giao..."
              ></textarea>
            </div>
          </div>

          <div class="checkout-card">
            <div class="section-title">
              <i class="bi bi-truck"></i>
              <h2>Phương thức giao hàng</h2>
            </div>

            <div class="shipping-methods">
              <label
                  v-for="method in shippingMethods"
                  :key="method.id"
                  class="shipping-card"
                  :class="{ active: method.active }"
              >
                <input
                    class="form-check-input"
                    type="radio"
                    name="shipping_method"
                    :checked="method.active"
                />

                <div class="shipping-icon">
                  <i :class="`bi ${method.icon}`"></i>
                </div>

                <div class="shipping-content">
                  <h3>{{ method.title }}</h3>
                  <p>{{ method.fee }}</p>
                </div>
              </label>
            </div>
          </div>

          <PaymentMethod />
        </div>

        <div class="checkout-right">
          <OrderSummary />
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.checkout-page {
  padding: 18px 0 40px;
  background: #ffffff;
}

.checkout-container {
  width: min(100% - 36px, 1560px);
  margin: 0 auto;
}

.breadcrumb-area {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
}

.breadcrumb-area a {
  color: #64748b;
  text-decoration: none;
}

.breadcrumb-area a:hover,
.breadcrumb-area span {
  color: #0d6efd;
}

.breadcrumb-area i {
  font-size: 11px;
}

.page-title {
  margin: 0 0 14px;
  color: #0f172a;
  font-size: 34px;
  font-weight: 900;
}

.checkout-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 500px;
  gap: 24px;
  align-items: start;
}

.checkout-left,
.checkout-right {
  min-width: 0;
}

.checkout-left {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.checkout-card {
  padding: 18px 20px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #ffffff;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}

.section-title i {
  color: #0d6efd;
  font-size: 20px;
}

.section-title h2 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
  font-weight: 800;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 6px;
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
}

.form-group label span {
  color: #ef4444;
}

.form-group .form-control,
.form-group .form-select {
  height: 42px;
  border: 1px solid #dbe3ef;
  border-radius: 9px;
  box-shadow: none;
  color: #334155;
  font-size: 14px;
  font-weight: 500;
}

.form-group textarea.form-control {
  height: auto;
  min-height: 56px;
  resize: none;
  padding-top: 10px;
}

.form-group-full {
  margin-top: 12px;
}

.note-group {
  margin-top: 12px;
}

.shipping-methods {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.shipping-card {
  padding: 12px 14px;
  border: 1px solid #dbe3ef;
  border-radius: 10px;
  background: #ffffff;
  display: grid;
  grid-template-columns: 18px 34px 1fr;
  gap: 12px;
  align-items: center;
  cursor: pointer;
}

.shipping-card.active {
  border-color: #0d6efd;
  box-shadow: inset 0 0 0 1px #0d6efd;
}

.shipping-card .form-check-input {
  box-shadow: none;
}

.shipping-card .form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.shipping-icon {
  width: 34px;
  height: 34px;
  color: #334155;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.shipping-content h3 {
  margin: 0 0 2px;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
}

.shipping-content p {
  margin: 0;
  color: #0d6efd;
  font-size: 14px;
  font-weight: 800;
}

@media (max-width: 1200px) {
  .checkout-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .checkout-container {
    width: min(100% - 24px, 1560px);
  }

  .page-title {
    font-size: 28px;
  }

  .form-grid,
  .shipping-methods {
    grid-template-columns: 1fr;
  }

  .checkout-card {
    padding: 16px;
  }
}
</style>