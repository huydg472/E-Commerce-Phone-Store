<script setup>
import {usePublicSiteSettings} from '@/composables/usePublicSiteSettings'

const {brandName, description, supportPhone, supportEmail, contactEmail, address, socials} = usePublicSiteSettings()
</script>

<template>
  <footer class="site-footer">
    <div class="container-fluid px-4">
      <div class="footer-main">
        <div class="footer-col footer-brand">
          <RouterLink to="/" class="footer-logo-text">
            <span>{{ brandName }}</span>
          </RouterLink>

          <p>
            {{ description }}
          </p>

          <small>{{ address }}</small>
          <small>Hotline: {{ supportPhone }}</small>
        </div>

        <div class="footer-col">
          <h5>Liên hệ</h5>
          <a :href="`tel:${supportPhone.replace(/\s+/g, '')}`">{{ supportPhone }}</a>
          <a :href="`mailto:${supportEmail}`">{{ supportEmail }}</a>
          <a :href="`mailto:${contactEmail}`">{{ contactEmail }}</a>
        </div>

        <div class="footer-col">
          <h5>Mạng xã hội</h5>
          <a v-for="social in socials" :key="social.label" :href="social.url" target="_blank" rel="noreferrer">
            {{ social.label }}
          </a>
          <small v-if="!socials.length">Chưa cấu hình liên kết mạng xã hội</small>
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
  grid-template-columns: 1.3fr 1fr 1fr 1fr 1.1fr;
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

.footer-brand p {
  color: var(--muted-color);
  font-size: 16px;
  line-height: 1.55;
  margin: 0 0 12px;
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  letter-spacing: 0;
  text-rendering: optimizeLegibility;
}

.footer-logo-text span {
  color: var(--primary-color);
  font-weight: 700;
}

.footer-brand small {
  display: block;
  margin-top: 10px;
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

.footer-col a,
.footer-col small {
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
</style>
