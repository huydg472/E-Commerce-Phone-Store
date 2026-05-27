<script setup>
const stats = [
  {
    title: 'Tổng doanh thu',
    value: '128.5M',
    unit: 'VNĐ',
    icon: 'bi bi-cash-stack',
    change: '+12.5%',
    desc: 'So với tháng trước',
  },
  {
    title: 'Đơn hàng',
    value: '356',
    unit: 'đơn',
    icon: 'bi bi-receipt',
    change: '+8.2%',
    desc: 'Đơn hàng trong tháng',
  },
  {
    title: 'Sản phẩm',
    value: '1,248',
    unit: 'sp',
    icon: 'bi bi-phone',
    change: '+24',
    desc: 'Sản phẩm đang bán',
  },
  {
    title: 'Khách hàng',
    value: '2,430',
    unit: 'người',
    icon: 'bi bi-people',
    change: '+16.8%',
    desc: 'Khách hàng hoạt động',
  },
]

const topProducts = [
  {
    name: 'iPhone 15 Pro Max',
    category: 'Apple',
    sold: 128,
    revenue: '38.4M',
    percent: 86,
  },
  {
    name: 'Samsung Galaxy S24 Ultra',
    category: 'Samsung',
    sold: 96,
    revenue: '28.8M',
    percent: 72,
  },
  {
    name: 'iPhone 14 Pro',
    category: 'Apple',
    sold: 84,
    revenue: '21.6M',
    percent: 64,
  },
  {
    name: 'Xiaomi 14T Pro',
    category: 'Xiaomi',
    sold: 62,
    revenue: '12.4M',
    percent: 48,
  },
]

const recentOrders = [
  {
    id: '#ZM10245',
    customer: 'Đào Gia Huy',
    total: '24.990.000đ',
    status: 'Đã thanh toán',
    type: 'success',
  },
  {
    id: '#ZM10244',
    customer: 'Nguyễn Văn An',
    total: '18.490.000đ',
    status: 'Đang xử lý',
    type: 'warning',
  },
  {
    id: '#ZM10243',
    customer: 'Trần Minh Đức',
    total: '7.990.000đ',
    status: 'Đang giao',
    type: 'info',
  },
  {
    id: '#ZM10242',
    customer: 'Lê Hoàng Nam',
    total: '4.990.000đ',
    status: 'Đã huỷ',
    type: 'danger',
  },
]

const activities = [
  {
    icon: 'bi bi-bag-check',
    title: 'Có đơn hàng mới',
    time: '5 phút trước',
  },
  {
    icon: 'bi bi-person-plus',
    title: 'Khách hàng mới đăng ký',
    time: '18 phút trước',
  },
  {
    icon: 'bi bi-phone',
    title: 'Sản phẩm iPhone 15 được cập nhật',
    time: '35 phút trước',
  },
  {
    icon: 'bi bi-credit-card',
    title: 'Thanh toán mới thành công',
    time: '1 giờ trước',
  },
]
</script>

<template>
  <div class="dashboard-page">
    <section class="stats-grid">
      <div v-for="item in stats" :key="item.title" class="stat-card">
        <div class="stat-icon">
          <i :class="item.icon"></i>
        </div>

        <div class="stat-content">
          <p>{{ item.title }}</p>

          <h3>
            {{ item.value }}
            <span>{{ item.unit }}</span>
          </h3>

          <div class="stat-meta">
            <strong>{{ item.change }}</strong>
            <span>{{ item.desc }}</span>
          </div>
        </div>
      </div>
    </section>

    <section class="dashboard-grid">
      <div class="dashboard-card sales-card">
        <div class="card-head">
          <div>
            <h4>Doanh thu bán hàng</h4>
            <p>Thống kê doanh thu 6 tháng gần nhất</p>
          </div>

          <button type="button" class="card-action">
            Xem chi tiết
          </button>
        </div>

        <div class="chart-box">
          <div class="bar-item">
            <span style="height: 45%"></span>
            <small>T1</small>
          </div>
          <div class="bar-item">
            <span style="height: 62%"></span>
            <small>T2</small>
          </div>
          <div class="bar-item">
            <span style="height: 56%"></span>
            <small>T3</small>
          </div>
          <div class="bar-item">
            <span style="height: 78%"></span>
            <small>T4</small>
          </div>
          <div class="bar-item">
            <span style="height: 68%"></span>
            <small>T5</small>
          </div>
          <div class="bar-item">
            <span style="height: 88%"></span>
            <small>T6</small>
          </div>
        </div>
      </div>

      <div class="dashboard-card activity-card">
        <div class="card-head">
          <div>
            <h4>Hoạt động gần đây</h4>
            <p>Cập nhật mới nhất trong hệ thống</p>
          </div>
        </div>

        <div class="activity-list">
          <div v-for="item in activities" :key="item.title" class="activity-item">
            <div class="activity-icon">
              <i :class="item.icon"></i>
            </div>

            <div>
              <strong>{{ item.title }}</strong>
              <span>{{ item.time }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="dashboard-grid bottom-grid">
      <div class="dashboard-card">
        <div class="card-head">
          <div>
            <h4>Sản phẩm bán chạy</h4>
            <p>Các sản phẩm có doanh số cao</p>
          </div>
        </div>

        <div class="product-list">
          <div v-for="product in topProducts" :key="product.name" class="product-item">
            <div class="product-main">
              <div>
                <strong>{{ product.name }}</strong>
                <span>{{ product.category }}</span>
              </div>

              <p>{{ product.revenue }}</p>
            </div>

            <div class="product-meta">
              <span>Đã bán {{ product.sold }}</span>
              <span>{{ product.percent }}%</span>
            </div>

            <div class="progress product-progress">
              <div class="progress-bar" :style="{ width: product.percent + '%' }"></div>
            </div>
          </div>
        </div>
      </div>

      <div class="dashboard-card">
        <div class="card-head">
          <div>
            <h4>Đơn hàng mới</h4>
            <p>Danh sách đơn hàng gần đây</p>
          </div>

          <button type="button" class="card-action">
            Tất cả
          </button>
        </div>

        <div class="order-list">
          <div v-for="order in recentOrders" :key="order.id" class="order-item">
            <div>
              <strong>{{ order.id }}</strong>
              <span>{{ order.customer }}</span>
            </div>

            <p>{{ order.total }}</p>

            <span class="status-badge" :class="`status-${order.type}`">
              {{ order.status }}
            </span>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<style scoped>
.dashboard-page {
  display: flex;
  flex-direction: column;
  gap: 24px;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 20px;
}

.stat-card {
  min-height: 150px;
  padding: 22px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
  display: flex;
  gap: 16px;
}

.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  background: #eef5ff;
  color: #2563eb;
  display: grid;
  place-items: center;
  font-size: 24px;
  flex-shrink: 0;
}

.stat-content {
  min-width: 0;
}

.stat-content p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
}

.stat-content h3 {
  margin: 10px 0 8px;
  color: #0f172a;
  font-size: 28px;
  font-weight: 800;
  line-height: 1.1;
}

.stat-content h3 span {
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
}

.stat-meta {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.stat-meta strong {
  color: #16a34a;
  font-size: 13px;
  font-weight: 800;
}

.stat-meta span {
  color: #94a3b8;
  font-size: 13px;
}

.dashboard-grid {
  display: grid;
  grid-template-columns: 1.55fr 1fr;
  gap: 20px;
}

.bottom-grid {
  grid-template-columns: 1fr 1.15fr;
}

.dashboard-card {
  padding: 22px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.card-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
}

.card-head h4 {
  margin: 0;
  color: #0f172a;
  font-size: 19px;
  font-weight: 800;
}

.card-head p {
  margin: 5px 0 0;
  color: #64748b;
  font-size: 14px;
}

.card-action {
  height: 36px;
  padding: 0 14px;
  border: none;
  border-radius: 10px;
  background: #eef5ff;
  color: #2563eb;
  font-size: 14px;
  font-weight: 700;
}

.chart-box {
  height: 260px;
  padding: 16px 10px 0;
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  align-items: end;
  gap: 18px;
}

.bar-item {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  align-items: center;
  gap: 10px;
}

.bar-item span {
  width: 100%;
  max-width: 42px;
  border-radius: 999px 999px 8px 8px;
  background: linear-gradient(180deg, #60a5fa, #2563eb);
}

.bar-item small {
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 12px;
}

.activity-icon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  background: #f4f8ff;
  color: #2563eb;
  display: grid;
  place-items: center;
  font-size: 18px;
  flex-shrink: 0;
}

.activity-item strong {
  display: block;
  color: #0f172a;
  font-size: 15px;
  font-weight: 700;
}

.activity-item span {
  display: block;
  margin-top: 3px;
  color: #94a3b8;
  font-size: 13px;
}

.product-list,
.order-list {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.product-main {
  display: flex;
  justify-content: space-between;
  gap: 14px;
}

.product-main strong,
.order-item strong {
  display: block;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
}

.product-main span,
.order-item span {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
}

.product-main p,
.order-item p {
  margin: 0;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
}

.product-meta {
  margin-top: 10px;
  display: flex;
  justify-content: space-between;
  color: #64748b;
  font-size: 13px;
}

.product-progress {
  height: 8px;
  margin-top: 8px;
  border-radius: 999px;
  background: #eaf1fb;
  overflow: hidden;
}

.product-progress .progress-bar {
  background: #2563eb;
}

.order-item {
  display: grid;
  grid-template-columns: 1fr auto auto;
  align-items: center;
  gap: 16px;
  padding-bottom: 14px;
  border-bottom: 1px solid #eef2f7;
}

.order-item:last-child {
  padding-bottom: 0;
  border-bottom: none;
}

.status-badge {
  min-width: 92px;
  padding: 7px 10px;
  border-radius: 999px;
  text-align: center;
  font-size: 12px;
  font-weight: 800;
}

@media (max-width: 1400px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 1200px) {
  .dashboard-grid,
  .bottom-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 770px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }

  .dashboard-card,
  .stat-card {
    padding: 18px;
    border-radius: 16px;
  }

  .chart-box {
    height: 220px;
    gap: 12px;
  }

  .order-item {
    grid-template-columns: 1fr;
    gap: 8px;
  }

  .status-badge {
    width: fit-content;
  }
}
</style>