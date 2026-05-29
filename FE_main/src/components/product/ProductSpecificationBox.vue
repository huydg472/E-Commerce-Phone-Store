<script setup>
const defaultBenefits = [
  {
    icon: 'bi-shield-check',
    title: 'Sản phẩm chính hãng',
    desc: 'Cam kết hàng chính hãng 100%',
  },
  {
    icon: 'bi-truck',
    title: 'Miễn phí giao hàng',
    desc: 'Miễn phí giao hàng toàn quốc',
  },
  {
    icon: 'bi-arrow-repeat',
    title: 'Đổi trả dễ dàng',
    desc: 'Đổi trả trong 30 ngày nếu có lỗi',
  },
  {
    icon: 'bi-credit-card',
    title: 'Hỗ trợ trả góp',
    desc: 'Trả góp 0% qua thẻ tín dụng',
  },
]

</script>

<template>
  <div class="side-box">
    <div class="benefit-card">
      <h3>Ưu đãi & Lợi ích</h3>

      <div
          v-for="benefit in defaultBenefits"
          :key="benefit.title"
          class="benefit-item"
      >
        <div class="benefit-icon">
          <i :class="`bi ${benefit.icon}`"></i>
        </div>

        <div>
          <h4>{{ benefit.title }}</h4>
          <p>{{ benefit.desc }}</p>
        </div>
      </div>
    </div>

    <div class="consult-card">
      <h3>Bạn cần tư vấn?</h3>
      <p>Gọi ngay Zalo hoặc liên hệ với chúng tôi</p>

      <a href="tel:19001234">
        <i class="bi bi-telephone"></i>
        1900 1234
        <span>(8:00 - 22:00)</span>
      </a>
    </div>
  </div>
</template>

<style scoped>
.side-box {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.benefit-card,
.consult-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #ffffff;
}

.benefit-card {
  padding: 16px;
}

.benefit-card h3,
.consult-card h3 {
  margin: 0 0 14px;
  color: #111827;
  font-size: 16px;
  font-weight: 800;
}

.benefit-item {
  display: grid;
  grid-template-columns: 40px 1fr;
  gap: 10px;
  padding: 12px 0;
}

.benefit-item + .benefit-item {
  border-top: 1px solid #e5e7eb;
}

.benefit-icon {
  color: #0d6efd;
  font-size: 30px;
  line-height: 1;
}

.benefit-item h4 {
  margin: 0 0 5px;
  color: #111827;
  font-size: 14px;
  font-weight: 800;
}

.benefit-item p {
  margin: 0;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
}

.consult-card {
  padding: 16px;
  text-align: center;
  background: #f4f7fb;
}

.consult-card h3 {
  margin-bottom: 6px;
}

.consult-card p {
  margin: 0 0 12px;
  color: #64748b;
  font-size: 13px;
  font-weight: 500;
}

.consult-card a {
  color: #0d6efd;
  text-decoration: none;
  font-size: 16px;
  font-weight: 800;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.consult-card span {
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
}
</style>
