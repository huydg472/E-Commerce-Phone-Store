<template>
  <footer class="site-footer">
    <div class="container-fluid px-4">
      <div class="footer-main">
        <div class="footer-col footer-brand">
          <RouterLink to="/" class="footer-logo-text">
            <span>Zin</span><strong>Mobile</strong>
          </RouterLink>

          <p>
            ZinMobile - Hệ thống bán lẻ điện thoại chính hãng,
            giá tốt, dịch vụ tận tâm.
          </p>

          <small>© 2024 ZinMobile. All rights reserved.</small>
        </div>

        <div class="footer-col">
          <h5>Về chúng tôi</h5>
          <RouterLink to="/gioi-thieu">Giới thiệu</RouterLink>
          <RouterLink to="/tuyen-dung">Tuyển dụng</RouterLink>
          <RouterLink to="/tin-tuc">Tin tức</RouterLink>
        </div>

        <div class="footer-col">
          <h5>Chính sách</h5>
          <RouterLink to="/bao-hanh">Chính sách bảo hành</RouterLink>
          <RouterLink to="/doi-tra">Chính sách đổi trả</RouterLink>
          <RouterLink to="/bao-mat">Chính sách bảo mật</RouterLink>
        </div>

        <div class="footer-col">
          <h5>Hỗ trợ khách hàng</h5>
          <RouterLink to="/huong-dan-mua-hang">Hướng dẫn mua hàng</RouterLink>
          <RouterLink to="/huong-dan-thanh-toan">Hướng dẫn thanh toán</RouterLink>
          <RouterLink to="/cau-hoi-thuong-gap">Câu hỏi thường gặp</RouterLink>
        </div>

        <div class="footer-col footer-newsletter">
          <h5>Đăng ký nhận tin</h5>
          <p>Nhận thông tin khuyến mãi mới nhất từ ZinMobile</p>

          <div class="newsletter-form">
            <input type="email" placeholder="Nhập email của bạn" />
            <button>Đăng ký</button>
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>

<style scoped>
.site-footer {
  margin-top: 18px;
  border-top: 1px solid var(--border-color);
  background: var(--card-bg);
}

.footer-main {
  padding: 24px 0 28px;
  display: grid;
  grid-template-columns: 1.3fr 0.9fr 1fr 1.1fr 1.45fr;
  gap: 38px;
}

.footer-logo-text {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  margin-bottom: 14px;
  color: var(--text-color);
  font-size: 28px;
  line-height: 1.3;
  font-weight: 700;
  letter-spacing: 0;
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  text-rendering: optimizeLegibility;
}

.footer-logo-text strong {
  color: var(--primary-color);
  font-weight: 700;
}

.footer-brand p,
.footer-newsletter p {
  color: var(--muted-color);
  font-size: 16px;
  line-height: 1.55;
  margin: 0 0 12px;
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  letter-spacing: 0;
  text-rendering: optimizeLegibility;
}

.footer-brand small {
  display: block;
  margin-top: 14px;
  color: var(--muted-color);
  font-size: 15px;
  line-height: 1.5;
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  letter-spacing: 0;
  text-rendering: optimizeLegibility;
}

.footer-col h5 {
  margin: 0 0 18px;
  font-size: 19px;
  line-height: 1.3;
  font-weight: 800;
  color: var(--text-color);
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  letter-spacing: 0;
  text-rendering: optimizeLegibility;
}

.footer-col a {
  display: block;
  margin-bottom: 14px;
  color: var(--muted-color);
  font-size: 15px;
  line-height: 1.4;
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  letter-spacing: 0;
  text-rendering: optimizeLegibility;
}

.footer-col a:hover {
  color: var(--primary-color);
}

.newsletter-form {
  height: 42px;
  display: grid;
  grid-template-columns: 1fr 110px;
  margin-top: 10px;
}

.newsletter-form input {
  border: 1px solid var(--border-color);
  border-right: none;
  border-radius: 8px 0 0 8px;
  padding: 0 16px;
  outline: none;
  background: var(--card-bg);
  color: var(--text-color);
  font-size: 14px;
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  letter-spacing: 0;
  text-rendering: optimizeLegibility;
}

.newsletter-form input:focus {
  border-color: var(--primary-color);
}

.newsletter-form button {
  border: none;
  border-radius: 0 8px 8px 0;
  background: var(--primary-color);
  color: #ffffff;
  font-weight: 700;
  font-size: 14px;
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  letter-spacing: 0;
  text-rendering: optimizeLegibility;
}

.newsletter-form button:hover {
  background: var(--primary-hover);
}

@media (max-width: 1200px) {
  .footer-main {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 900px) {
  .footer-main {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 576px) {
  .footer-main {
    gap: 22px;
  }

  .newsletter-form {
    grid-template-columns: 1fr;
    height: auto;
    gap: 8px;
  }

  .newsletter-form input,
  .newsletter-form button {
    height: 44px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
  }
}
</style>