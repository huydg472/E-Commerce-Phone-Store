<script setup>
import {reactive} from 'vue'

const form = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: ''
})

const subjects = [
  {
    label: 'Tư vấn sản phẩm',
    value: 'product_advice'
  },
  {
    label: 'Hỗ trợ đơn hàng',
    value: 'order_support'
  },
  {
    label: 'Bảo hành sản phẩm',
    value: 'warranty'
  },
  {
    label: 'Góp ý dịch vụ',
    value: 'feedback'
  }
]

const storeInfo = {
  name: 'Zin Mobile Store',
  phone: '085 999 55 5',
  phoneHref: 'tel:085999555',
  email: 'htro@zinmobile.vn',
  address: '295A Trần Nguyên Hãn, An Biên, Hải Phòng',
  workingTime: '8:30 - 22:30'
}

const mapEmbedUrl =
    'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3728.7680544721184!2d106.66485057380578!3d20.84107068076175!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x314a7a7980268201%3A0x760246fdcaf9619f!2zMjk1QSBQLiBUcuG6p24gTmd1ecOqbiBIw6NuLCBBbiBCacOqbiwgSOG6o2kgUGjDsm5nLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1779873102452!5m2!1svi!2s'

const directionUrl =
    'https://www.google.com/maps/dir/?api=1&destination=295A%20Tr%E1%BA%A7n%20Nguy%C3%AAn%20H%C3%A3n%2C%20An%20Bi%C3%AAn%2C%20H%E1%BA%A3i%20Ph%C3%B2ng%2C%20Vi%E1%BB%87t%20Nam'

const phoneIcon = `
<svg width="22" height="22" viewBox="0 0 24 24" fill="none">
  <path d="M22 16.92v3a2 2 0 0 1-2.18 2A19.8 19.8 0 0 1 3.08 5.18 2 2 0 0 1 5.06 3h3a2 2 0 0 1 2 1.72c.12.9.33 1.77.63 2.6a2 2 0 0 1-.45 2.11L9 10.68a16 16 0 0 0 4.32 4.32l1.25-1.25a2 2 0 0 1 2.11-.45c.83.3 1.7.51 2.6.63A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`

const mailIcon = `
<svg width="22" height="22" viewBox="0 0 24 24" fill="none">
  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="m22 6-10 7L2 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`

const locationIcon = `
<svg width="22" height="22" viewBox="0 0 24 24" fill="none">
  <path d="M21 10c0 7-9 12-9 12S3 17 3 10a9 9 0 1 1 18 0z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
</svg>
`

const clockIcon = `
<svg width="22" height="22" viewBox="0 0 24 24" fill="none">
  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/>
  <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
`

const shareIcon = `
<svg width="22" height="22" viewBox="0 0 24 24" fill="none">
  <circle cx="18" cy="5" r="3" stroke="currentColor" stroke-width="2"/>
  <circle cx="6" cy="12" r="3" stroke="currentColor" stroke-width="2"/>
  <circle cx="18" cy="19" r="3" stroke="currentColor" stroke-width="2"/>
  <path d="M8.59 13.51 15.42 17.49M15.41 6.51 8.59 10.49" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
</svg>
`

const contactInfo = [
  {
    id: 1,
    title: 'Hotline hỗ trợ',
    main: storeInfo.phone,
    sub: '(8:30 - 22:30)',
    href: storeInfo.phoneHref,
    icon: phoneIcon
  },
  {
    id: 2,
    title: 'Email',
    main: storeInfo.email,
    sub: 'Phản hồi trong 24h',
    href: `mailto:${storeInfo.email}`,
    icon: mailIcon
  },
  {
    id: 3,
    title: 'Địa chỉ cửa hàng',
    main: storeInfo.address,
    sub: '',
    icon: locationIcon,
    linkText: 'Xem bản đồ'
  },
  {
    id: 4,
    title: 'Giờ làm việc',
    main: storeInfo.workingTime,
    sub: '(Tất cả các ngày trong tuần)',
    icon: clockIcon
  }
]

const socials = [
  {
    name: 'facebook',
    label: 'f',
    href: '#',
    className: 'facebook'
  },
  {
    name: 'zalo',
    label: 'Zalo',
    href: '#',
    className: 'zalo'
  },
  {
    name: 'youtube',
    label: '▶',
    href: '#',
    className: 'youtube'
  },
  {
    name: 'tiktok',
    label: '♪',
    href: '#',
    className: 'tiktok'
  }
]

const handleSubmit = () => {
  console.log('Contact form:', {...form})
}
</script>

<template>
  <main class="contact-page">
    <div class="container">
      <!-- Breadcrumb -->
      <nav class="contact-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
          <li class="breadcrumb-item">
            <RouterLink to="/">Trang chủ</RouterLink>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            Liên hệ
          </li>
        </ol>
      </nav>

      <!-- Heading -->
      <section class="page-heading">
        <h1>Liên hệ</h1>
        <p>
          Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn. Hãy liên hệ với Zin Mobile qua các kênh dưới đây.
        </p>
      </section>

      <!-- Contact content -->
      <section class="row g-4 align-items-stretch">
        <!-- Contact form -->
        <div class="col-lg-7">
          <div class="contact-card h-100">
            <h2>Gửi yêu cầu liên hệ</h2>

            <form @submit.prevent="handleSubmit">
              <div class="row g-3">
                <div class="col-md-6">
                  <label for="name" class="form-label">
                    Họ và tên <span>*</span>
                  </label>
                  <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      class="form-control"
                      placeholder="Nhập họ và tên của bạn"
                  />
                </div>

                <div class="col-md-6">
                  <label for="email" class="form-label">
                    Email <span>*</span>
                  </label>
                  <input
                      id="email"
                      v-model="form.email"
                      type="email"
                      class="form-control"
                      placeholder="Nhập email của bạn"
                  />
                </div>

                <div class="col-md-6">
                  <label for="phone" class="form-label">
                    Số điện thoại <span>*</span>
                  </label>
                  <input
                      id="phone"
                      v-model="form.phone"
                      type="tel"
                      class="form-control"
                      placeholder="Nhập số điện thoại"
                  />
                </div>

                <div class="col-md-6">
                  <label for="subject" class="form-label">
                    Chủ đề <span>*</span>
                  </label>
                  <select
                      id="subject"
                      v-model="form.subject"
                      class="form-select"
                  >
                    <option value="" disabled>Chọn chủ đề</option>
                    <option
                        v-for="subject in subjects"
                        :key="subject.value"
                        :value="subject.value"
                    >
                      {{ subject.label }}
                    </option>
                  </select>
                </div>

                <div class="col-12">
                  <label for="message" class="form-label">
                    Nội dung <span>*</span>
                  </label>
                  <textarea
                      id="message"
                      v-model="form.message"
                      class="form-control contact-textarea"
                      placeholder="Nhập nội dung bạn cần hỗ trợ hoặc góp ý..."
                  ></textarea>
                </div>

                <div class="col-12">
                  <button type="submit" class="btn btn-primary btn-submit">
                    <span class="send-icon">➤</span>
                    Gửi yêu cầu
                  </button>

                  <p class="form-note">
                    Thông tin của bạn được bảo mật và chỉ sử dụng để hỗ trợ.
                  </p>
                </div>
              </div>
            </form>
          </div>
        </div>

        <!-- Contact information -->
        <div class="col-lg-5">
          <div class="contact-card h-100">
            <h2>Thông tin liên hệ</h2>

            <div class="info-list">
              <div
                  v-for="item in contactInfo"
                  :key="item.id"
                  class="info-item"
              >
                <div class="info-icon">
                  <span v-html="item.icon"></span>
                </div>

                <div class="info-content">
                  <h3>{{ item.title }}</h3>

                  <a
                      v-if="item.href"
                      :href="item.href"
                      class="info-main"
                  >
                    {{ item.main }}
                  </a>

                  <p v-else class="info-main mb-0">
                    {{ item.main }}
                  </p>

                  <p v-if="item.sub" class="info-sub mb-0">
                    {{ item.sub }}
                  </p>

                  <a
                      v-if="item.linkText"
                      :href="directionUrl"
                      target="_blank"
                      rel="noopener noreferrer"
                      class="info-link"
                  >
                    {{ item.linkText }}
                    <span>→</span>
                  </a>
                </div>
              </div>

              <div class="info-item social-item">
                <div class="info-icon">
                  <span v-html="shareIcon"></span>
                </div>

                <div class="info-content">
                  <h3>Kết nối với chúng tôi</h3>

                  <div class="social-list">
                    <a
                        v-for="social in socials"
                        :key="social.name"
                        :href="social.href"
                        :class="['social-link', social.className]"
                    >
                      {{ social.label }}
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Google Maps -->
      <section class="map-section">
        <iframe
            class="map-frame"
            :src="mapEmbedUrl"
            allowfullscreen
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            title="Bản đồ Zin Mobile Store"
        ></iframe>

        <div class="store-card">
          <h3>{{ storeInfo.name }}</h3>
          <p>{{ storeInfo.address }}</p>

          <a
              :href="directionUrl"
              target="_blank"
              rel="noopener noreferrer"
          >
            Chỉ đường
            <span>→</span>
          </a>
        </div>
      </section>

      <!-- Support CTA -->
      <section class="support-box">
        <div class="support-left">
          <div class="support-icon">?</div>

          <div>
            <h2>Bạn cần hỗ trợ nhanh?</h2>
            <p>Xem các câu hỏi thường gặp hoặc gửi thông tin cần thiết nhanh chóng.</p>
          </div>
        </div>

        <RouterLink to="/ho-tro/faq" class="btn btn-outline-primary btn-faq">
          Xem câu hỏi thường gặp
          <span>→</span>
        </RouterLink>
      </section>
    </div>
  </main>
</template>
<style scoped>
.contact-page {
  padding: 26px 0 34px;
  background: #fff;
  color: #111827;
}

.contact-breadcrumb {
  margin-bottom: 6px;
}

.breadcrumb {
  font-size: 14px;
  font-weight: 500;
}

.breadcrumb a {
  color: #64748b;
  text-decoration: none;
}

.breadcrumb a:hover {
  color: #0d6efd;
}

.breadcrumb-item.active {
  color: #0d6efd;
}

.page-heading {
  margin-bottom: 18px;
}

.page-heading h1 {
  margin-bottom: 6px;
  font-size: 28px;
  font-weight: 800;
}

.page-heading p {
  margin-bottom: 0;
  color: #475569;
  font-size: 15px;
  font-weight: 500;
}

.contact-card {
  padding: 22px 24px 24px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  background: #fff;
}

.contact-card h2 {
  margin-bottom: 20px;
  color: #111827;
  font-size: 19px;
  font-weight: 800;
}

.form-label {
  margin-bottom: 7px;
  color: #111827;
  font-size: 14px;
  font-weight: 700;
}

.form-label span {
  color: #dc2626;
}

.form-control,
.form-select {
  height: 38px;
  border-color: #dbe1ea;
  border-radius: 5px;
  color: #111827;
  font-size: 14px;
  font-weight: 500;
}

.form-control::placeholder {
  color: #94a3b8;
}

.form-control:focus,
.form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.15rem rgba(13, 110, 253, 0.15);
}

.contact-textarea {
  min-height: 105px;
  resize: none;
}

.btn-submit {
  width: 100%;
  height: 43px;
  margin-top: 2px;
  border-radius: 5px;
  font-size: 15px;
  font-weight: 800;
}

.send-icon {
  display: inline-block;
  margin-right: 8px;
  transform: rotate(-25deg);
}

.form-note {
  margin: 12px 0 0;
  color: #64748b;
  text-align: center;
  font-size: 13px;
  font-weight: 500;
}

.info-list {
  display: flex;
  flex-direction: column;
}

.info-item {
  display: flex;
  gap: 14px;
  padding: 14px 0;
  border-bottom: 1px solid #edf0f5;
}

.info-item:first-child {
  padding-top: 0;
}

.info-item:last-child {
  padding-bottom: 0;
  border-bottom: 0;
}

.info-icon {
  display: flex;
  flex: 0 0 50px;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 44px;
  border: 1px solid #0d6efd;
  border-radius: 5px;
  color: #0d6efd;
  background: #f8fbff;
}

.info-content {
  min-width: 0;
  flex: 1;
}

.info-content h3 {
  margin-bottom: 3px;
  color: #111827;
  font-size: 14px;
  font-weight: 800;
}

.info-main {
  color: #0d6efd;
  text-decoration: none;
  font-size: 15px;
  font-weight: 800;
}

.info-main:hover {
  color: #0a58ca;
}

.info-sub {
  color: #475569;
  font-size: 13px;
  font-weight: 600;
}

.info-link {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  margin-top: 4px;
  color: #0d6efd;
  text-decoration: none;
  font-size: 13px;
  font-weight: 700;
}

.info-link:hover {
  text-decoration: underline;
}

.social-list {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-top: 7px;
}

.social-link {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 30px;
  height: 30px;
  padding: 0 7px;
  border-radius: 999px;
  color: #fff;
  text-decoration: none;
  font-size: 11px;
  font-weight: 800;
}

.facebook {
  background: #0d6efd;
}

.zalo {
  background: #0ea5e9;
}

.youtube {
  background: #dc2626;
}

.tiktok {
  background: #111827;
}

/* Google Map */
.map-section {
  position: relative;
  height: 260px;
  margin-top: 22px;
  overflow: hidden;
  border-radius: 10px;
  border: 1px solid #e5e7eb;
  background: #eef2f7;
}

.map-frame {
  display: block;
  width: 100%;
  height: 100%;
  border: 0;
}

.store-card {
  position: absolute;
  top: 18px;
  left: 18px;
  z-index: 2;
  width: 290px;
  padding: 14px 16px;
  border-radius: 6px;
  background: #fff;
  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.12);
}

.store-card h3 {
  margin-bottom: 6px;
  color: #111827;
  font-size: 15px;
  font-weight: 800;
}

.store-card p {
  margin-bottom: 8px;
  color: #334155;
  font-size: 13px;
  line-height: 1.45;
  font-weight: 600;
}

.store-card a {
  color: #0d6efd;
  text-decoration: none;
  font-size: 13px;
  font-weight: 800;
}

.store-card a:hover {
  text-decoration: underline;
}

/* Support box */
.support-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  margin-top: 22px;
  padding: 18px 28px;
  border-radius: 8px;
  background: #f3f7ff;
}

.support-left {
  display: flex;
  align-items: center;
  gap: 18px;
}

.support-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  flex: 0 0 42px;
  width: 42px;
  height: 42px;
  border: 2px solid #0d6efd;
  border-radius: 50%;
  color: #0d6efd;
  font-size: 22px;
  font-weight: 800;
}

.support-box h2 {
  margin-bottom: 4px;
  color: #111827;
  font-size: 18px;
  font-weight: 800;
}

.support-box p {
  margin-bottom: 0;
  color: #475569;
  font-size: 14px;
  font-weight: 500;
}

.btn-faq {
  min-width: 230px;
  height: 40px;
  border-radius: 5px;
  font-size: 14px;
  font-weight: 800;
}

/* Responsive */
@media (max-width: 990px) {
  .contact-card {
    padding: 20px;
  }

  .support-box {
    align-items: flex-start;
    flex-direction: column;
  }

  .btn-faq {
    width: 100%;
  }
}

@media (max-width: 768px) {
  .contact-page {
    padding-top: 20px;
  }

  .page-heading h1 {
    font-size: 24px;
  }

  .map-section {
    height: 300px;
  }

  .store-card {
    top: 14px;
    left: 14px;
    width: calc(100% - 28px);
  }

  .support-left {
    align-items: flex-start;
  }
}
</style>