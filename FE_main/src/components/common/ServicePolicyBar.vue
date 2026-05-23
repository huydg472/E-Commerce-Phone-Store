<template>
  <section class="container-fluid px-4">
    <div class="policy-bar">
      <div class="policy-item">
        <div class="policy-icon">
          <i class="bi bi-shield-check"></i>
        </div>

        <div class="policy-content">
          <h6>Hàng chính hãng</h6>
          <p>100% chính hãng, bảo hành toàn quốc</p>
        </div>
      </div>

      <div class="policy-item">
        <div class="policy-icon">
          <i class="bi bi-truck"></i>
        </div>

        <div class="policy-content">
          <h6>Giao hàng miễn phí</h6>
          <p>Miễn phí giao hàng cho đơn từ 500.000đ</p>
        </div>
      </div>

      <div class="policy-item">
        <div class="policy-icon">
          <i class="bi bi-arrow-repeat"></i>
        </div>

        <div class="policy-content">
          <h6>Đổi trả dễ dàng</h6>
          <p>Đổi trả trong 30 ngày nếu có lỗi</p>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.policy-bar {
  margin-top: 14px;
  min-height: 84px;
  border: 1px solid var(--border-color);
  border-radius: 10px;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  align-items: center;
  background: var(--card-bg);
  overflow: hidden;
}

.policy-item {
  min-height: 84px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 18px;
  padding: 14px 20px;
  border-right: 1px solid var(--border-color);
}

.policy-item:last-child {
  border-right: none;
}

.policy-icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  color: var(--primary-color);
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 34px;
  flex-shrink: 0;
}

.policy-content h6 {
  margin: 0 0 4px;
  font-size: 17px;
  line-height: 1.35;
  font-weight: 700;
  letter-spacing: 0;
  color: var(--text-color);
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  text-rendering: optimizeLegibility;
}

.policy-content p {
  margin: 0;
  color: var(--muted-color);
  font-size: 14px;
  line-height: 1.45;
  font-weight: 400;
  letter-spacing: 0;
  font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  text-rendering: optimizeLegibility;
}

@media (max-width: 900px) {
  .policy-bar {
    grid-template-columns: 1fr;
  }

  .policy-item {
    justify-content: flex-start;
    border-right: none;
    border-bottom: 1px solid var(--border-color);
  }

  .policy-item:last-child {
    border-bottom: none;
  }
}
</style>