<script setup>
import {computed, reactive} from 'vue'
import {usePublicSiteSettings} from '@/composables/usePublicSiteSettings'

const form = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
})

const subjects = [
  {label: 'Tư vấn sản phẩm', value: 'product_advice'},
  {label: 'Hỗ trợ đơn hàng', value: 'order_support'},
  {label: 'Bảo hành sản phẩm', value: 'warranty'},
  {label: 'Góp ý dịch vụ', value: 'feedback'},
]

const {brandName, supportPhone, supportEmail, address, socials} = usePublicSiteSettings()
const workingTime = computed(() => '8:30 - 22:30')

const contactItems = computed(() => [
  {
    title: 'Hotline hỗ trợ',
    value: supportPhone.value,
    hint: workingTime.value,
    href: `tel:${String(supportPhone.value).replace(/\s+/g, '')}`,
    icon: 'bi-telephone',
  },
  {
    title: 'Email',
    value: supportEmail.value,
    hint: 'Phản hồi trong 24h',
    href: `mailto:${supportEmail.value}`,
    icon: 'bi-envelope',
  },
  {
    title: 'Địa chỉ cửa hàng',
    value: address.value,
    hint: 'Xem trên bản đồ',
    href: `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address.value)}`,
    icon: 'bi-geo-alt',
  },
  {
    title: 'Giờ làm việc',
    value: workingTime.value,
    hint: 'Tất cả các ngày trong tuần',
    href: '',
    icon: 'bi-clock',
  },
])

const mapEmbedUrl = computed(() => {
  return `https://www.google.com/maps?q=${encodeURIComponent(address.value)}&output=embed`
})

const directionUrl = computed(() => {
  return `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(address.value)}`
})

const handleSubmit = () => {
  console.log('Contact form:', {...form})
}
</script>

<template>
  <main class="contact-page">
    <div class="contact-container">
      <section class="contact-hero">
        <div>
          <p class="eyebrow">Liên hệ</p>
          <h1>{{ brandName }}</h1>
          <p class="subtitle">
            Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn qua hotline, email hoặc trực tiếp tại cửa hàng.
          </p>
        </div>

        <a class="direction-btn" :href="directionUrl" target="_blank" rel="noreferrer">
          <i class="bi bi-compass"></i>
          Chỉ đường
        </a>
      </section>

      <section class="contact-grid">
        <article class="contact-card contact-form-card">
          <h2>Gửi yêu cầu liên hệ</h2>

          <form class="contact-form" @submit.prevent="handleSubmit">
            <div class="form-grid">
              <div class="form-group">
                <label>Họ và tên</label>
                <input v-model="form.name" type="text" class="form-control" placeholder="Nhập họ tên"/>
              </div>
              <div class="form-group">
                <label>Email</label>
                <input v-model="form.email" type="email" class="form-control" placeholder="Nhập email"/>
              </div>
              <div class="form-group">
                <label>Số điện thoại</label>
                <input v-model="form.phone" type="tel" class="form-control" placeholder="Nhập số điện thoại"/>
              </div>
              <div class="form-group">
                <label>Chủ đề</label>
                <select v-model="form.subject" class="form-select">
                  <option value="">Chọn chủ đề</option>
                  <option v-for="subject in subjects" :key="subject.value" :value="subject.value">
                    {{ subject.label }}
                  </option>
                </select>
              </div>
              <div class="form-group form-group--full">
                <label>Nội dung</label>
                <textarea v-model="form.message" class="form-control" rows="6"
                          placeholder="Nội dung cần hỗ trợ"></textarea>
              </div>
            </div>

            <button type="submit" class="primary-btn">Gửi yêu cầu</button>
          </form>
        </article>

        <aside class="contact-side">
          <article class="contact-card">
            <h2>Thông tin liên hệ</h2>
            <div class="info-list">
              <a v-for="item in contactItems" :key="item.title" class="info-item" :href="item.href || undefined">
                <span class="info-icon">
                  <i :class="`bi ${item.icon}`"></i>
                </span>
                <span class="info-body">
                  <strong>{{ item.title }}</strong>
                  <em>{{ item.value }}</em>
                  <small>{{ item.hint }}</small>
                </span>
              </a>
            </div>
          </article>

          <article class="contact-card">
            <h2>Kênh xã hội</h2>
            <div class="social-list">
              <a v-for="social in socials" :key="social.label" :href="social.url" target="_blank" rel="noreferrer">
                {{ social.label }}
              </a>
              <p v-if="!socials.length">Chưa cấu hình kênh xã hội trong cài đặt.</p>
            </div>
          </article>
        </aside>
      </section>

      <section class="map-section">
        <article class="contact-card">
          <div class="map-head">
            <div>
              <p class="eyebrow">Địa chỉ</p>
              <h2>{{ address }}</h2>
            </div>
            <a :href="directionUrl" target="_blank" rel="noreferrer">Mở Google Maps</a>
          </div>

          <iframe
              :src="mapEmbedUrl"
              title="Bản đồ cửa hàng"
              width="100%"
              height="420"
              style="border:0;"
              allowfullscreen
              loading="lazy"
          ></iframe>
        </article>
      </section>
    </div>
  </main>
</template>

<style scoped>
.contact-page {
  padding: 18px 0 40px;
}

.contact-container {
  width: min(100% - 36px, 1320px);
  margin: 0 auto;
}

.contact-hero,
.contact-card {
  border-radius: 22px;
  border: 1px solid #e5edf8;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.contact-hero {
  padding: 24px;
  background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 28%),
  linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.contact-hero h1 {
  margin: 0;
  color: #0f172a;
  font-size: 34px;
  font-weight: 900;
}

.subtitle {
  margin: 10px 0 0;
  color: #64748b;
  line-height: 1.7;
  max-width: 760px;
}

.direction-btn {
  min-height: 44px;
  padding: 0 16px;
  border-radius: 12px;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
  text-decoration: none;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  white-space: nowrap;
}

.contact-grid {
  margin-top: 16px;
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(320px, 0.9fr);
  gap: 16px;
}

.contact-card {
  padding: 20px;
}

.contact-card h2 {
  margin: 0 0 14px;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

.contact-form .form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group--full {
  grid-column: 1 / -1;
}

.form-group label {
  margin-bottom: 6px;
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
}

.form-control,
.form-select {
  min-height: 42px;
  border-radius: 10px;
  border: 1px solid #dbe3ef;
}

textarea.form-control {
  min-height: 120px;
  resize: vertical;
}

.primary-btn {
  margin-top: 16px;
  min-height: 46px;
  padding: 0 16px;
  border: 0;
  border-radius: 12px;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
  font-weight: 800;
}

.contact-side {
  display: grid;
  gap: 16px;
}

.info-list {
  display: grid;
  gap: 12px;
}

.info-item {
  display: grid;
  grid-template-columns: 44px 1fr;
  gap: 12px;
  align-items: start;
  padding: 12px;
  border-radius: 16px;
  background: #f8fbff;
  color: #0f172a;
  text-decoration: none;
  border: 1px solid #e5edf8;
}

.info-icon {
  width: 44px;
  height: 44px;
  border-radius: 14px;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  color: #1d4ed8;
  display: grid;
  place-items: center;
  font-size: 18px;
}

.info-body strong,
.info-body em,
.info-body small {
  display: block;
}

.info-body strong {
  font-size: 14px;
  font-weight: 900;
  margin-bottom: 4px;
}

.info-body em {
  color: #0f172a;
  font-style: normal;
  font-weight: 700;
  line-height: 1.5;
}

.info-body small {
  color: #64748b;
  margin-top: 4px;
}

.social-list {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.social-list a {
  padding: 8px 12px;
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  text-decoration: none;
  font-weight: 800;
}

.social-list p {
  margin: 0;
  color: #64748b;
}

.map-section {
  margin-top: 16px;
}

.map-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 12px;
}

.map-head h2 {
  margin: 0;
  font-size: 22px;
  font-weight: 900;
  color: #0f172a;
}

.map-head a {
  color: #2563eb;
  font-weight: 800;
  text-decoration: none;
}

@media (max-width: 992px) {
  .contact-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .contact-container {
    width: min(100% - 24px, 1320px);
  }

  .contact-hero {
    flex-direction: column;
  }

  .contact-hero h1 {
    font-size: 28px;
  }

  .contact-form .form-grid {
    grid-template-columns: 1fr;
  }

  .map-head {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
