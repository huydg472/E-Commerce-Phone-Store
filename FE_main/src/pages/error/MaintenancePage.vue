<script setup>
import {computed} from 'vue'
import {usePublicSiteSettings} from '@/composables/usePublicSiteSettings'

const {brandName, slogan, supportPhone, supportEmail, maintenanceMode} = usePublicSiteSettings({
  autoFetch: false,
})

const title = computed(() => brandName.value || 'ZinMobile')
</script>

<template>
  <section class="maintenance-page">
    <div class="maintenance-card">
      <div class="maintenance-badge">
        <i class="bi bi-tools"></i>
      </div>

      <p class="eyebrow">Đang bảo trì</p>
      <h1>{{ title }}</h1>
      <p class="description">
        {{ slogan || 'Hệ thống đang được nâng cấp để phục vụ bạn tốt hơn. Vui lòng quay lại sau ít phút.' }}
      </p>

      <div class="info-grid">
        <article>
          <span>Trạng thái</span>
          <strong>{{ maintenanceMode ? 'Đang bật chế độ bảo trì' : 'Hệ thống sẵn sàng' }}</strong>
        </article>
        <article>
          <span>Hỗ trợ</span>
          <strong>{{ supportPhone }}</strong>
        </article>
        <article>
          <span>Email</span>
          <strong>{{ supportEmail }}</strong>
        </article>
      </div>

      <p class="note">
        Nếu bạn là quản trị viên hoặc nhân viên, hãy đăng nhập vào trang admin để tiếp tục thao tác.
      </p>
    </div>
  </section>
</template>

<style scoped>
.maintenance-page {
  min-height: 100vh;
  padding: 24px;
  display: grid;
  place-items: center;
  background:
      radial-gradient(circle at top, rgba(37, 99, 235, 0.16), transparent 32%),
      linear-gradient(180deg, #eff6ff 0%, #f8fbff 48%, #ffffff 100%);
}

.maintenance-card {
  width: min(760px, 100%);
  padding: 32px;
  border: 1px solid #dbe7fb;
  border-radius: 28px;
  background: rgba(255, 255, 255, 0.92);
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
  text-align: center;
  backdrop-filter: blur(12px);
}

.maintenance-badge {
  width: 78px;
  height: 78px;
  margin: 0 auto 18px;
  border-radius: 24px;
  display: grid;
  place-items: center;
  color: #2563eb;
  background: linear-gradient(135deg, #dbeafe, #eff6ff);
  font-size: 34px;
}

.eyebrow {
  margin: 0;
  color: #2563eb;
  font-size: 13px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.12em;
}

h1 {
  margin: 10px 0 0;
  color: #0f172a;
  font-size: clamp(30px, 4vw, 44px);
  font-weight: 900;
  line-height: 1.05;
}

.description {
  max-width: 620px;
  margin: 14px auto 0;
  color: #475569;
  font-size: 16px;
  line-height: 1.8;
}

.info-grid {
  margin-top: 24px;
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
}

.info-grid article {
  padding: 14px 16px;
  border: 1px solid #e6eefc;
  border-radius: 18px;
  background: #f8fbff;
  text-align: left;
}

.info-grid span {
  display: block;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.info-grid strong {
  display: block;
  margin-top: 6px;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
}

.note {
  margin: 22px 0 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.7;
}

@media (max-width: 720px) {
  .maintenance-page {
    padding: 16px;
  }

  .maintenance-card {
    padding: 24px 18px;
    border-radius: 22px;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }
}
</style>
