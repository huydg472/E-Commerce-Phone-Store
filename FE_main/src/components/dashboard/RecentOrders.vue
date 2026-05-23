<script setup>
defineProps({
  orders: {
    type: Array,
    required: true,
  },
})

const statusMap = {
  pending: 'Chờ xác nhận',
  shipping: 'Đang giao',
  completed: 'Hoàn thành',
  cancelled: 'Đã hủy',
}
</script>

<template>
  <section class="dashboard-panel recent-orders-panel">
    <div class="panel-header">
      <h2>Đơn hàng gần đây</h2>

      <a href="#" class="view-all-link">
        Xem tất cả
        <i class="bi bi-chevron-right"></i>
      </a>
    </div>

    <div class="table-responsive">
      <table class="table order-table align-middle mb-0">
        <thead>
        <tr>
          <th>Mã đơn</th>
          <th>Khách hàng</th>
          <th>Sản phẩm</th>
          <th class="text-end">Tổng tiền</th>
          <th class="text-center">Trạng thái</th>
          <th>Ngày đặt</th>
        </tr>
        </thead>

        <tbody>
        <tr v-for="order in orders" :key="order.code">
          <td>
            <a class="order-code" href="#">{{ order.code }}</a>
          </td>
          <td>{{ order.customer }}</td>
          <td>
            <div class="product-cell">
              <span class="phone-mini" :class="order.thumbClass"></span>
              <span>{{ order.product }}</span>
            </div>
          </td>
          <td class="text-end fw-semibold">{{ order.total }}</td>
          <td class="text-center">
                            <span class="status-badge" :class="order.status">
                                {{ statusMap[order.status] }}
                            </span>
          </td>
          <td>{{ order.date }}</td>
        </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<style scoped>
.dashboard-panel {
  overflow: hidden;
  background: #ffffff;
  border: 1px solid #e5e9f1;
  border-radius: 10px;
  box-shadow: 0 9px 25px rgba(15, 23, 42, 0.05);
}

.panel-header {
  min-height: 62px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  padding: 0 22px;
  border-bottom: 1px solid #edf0f5;
}

.panel-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 19px;
  font-weight: 800;
}

.view-all-link {
  min-width: 125px;
  height: 40px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  color: #0d6efd;
  text-decoration: none;
  background: #ffffff;
  border: 1px solid #e0e6ef;
  border-radius: 8px;
  font-size: 15px;
  font-weight: 700;
}

.order-table {
  min-width: 980px;
}

.order-table thead th {
  height: 51px;
  color: #111827;
  background: #ffffff;
  border-bottom: 1px solid #edf0f5;
  font-size: 16px;
  font-weight: 700;
  white-space: nowrap;
}

.order-table tbody td {
  height: 58px;
  color: #0f172a;
  border-bottom: 1px solid #edf0f5;
  font-size: 16px;
  white-space: nowrap;
}

.order-table tbody tr:last-child td {
  border-bottom: 0;
}

.order-table th:first-child,
.order-table td:first-child {
  padding-left: 26px;
}

.order-table th:last-child,
.order-table td:last-child {
  padding-right: 26px;
}

.order-code {
  color: #0d6efd;
  text-decoration: none;
  font-weight: 600;
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 18px;
}

.phone-mini {
  width: 29px;
  height: 39px;
  display: inline-block;
  border-radius: 6px;
  background: linear-gradient(135deg, #111827, #27272a);
  box-shadow: inset -5px 0 8px rgba(255, 255, 255, 0.08), 0 4px 8px rgba(15, 23, 42, 0.12);
}

.phone-graphite {
  background: linear-gradient(135deg, #08090c, #3f3f46);
}

.phone-titanium {
  background: linear-gradient(135deg, #22252b, #b7aa9a);
}

.phone-purple {
  background: linear-gradient(135deg, #1f1b24, #a855f7);
}

.phone-green {
  background: linear-gradient(135deg, #142d27, #8fd6b5);
}

.status-badge {
  min-width: 113px;
  height: 34px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0 14px;
  border-radius: 7px;
  font-size: 14px;
  font-weight: 600;
}

.status-badge.pending {
  color: #d97706;
  background: #fff2d8;
}

.status-badge.shipping {
  color: #0d6efd;
  background: #e4f0ff;
}

.status-badge.completed {
  color: #15803d;
  background: #ddf7e8;
}

.status-badge.cancelled {
  color: #dc2626;
  background: #fee2e2;
}

@media (max-width: 767.98px) {
  .panel-header {
    align-items: flex-start;
    flex-direction: column;
    padding: 18px;
  }

  .view-all-link {
    width: 100%;
  }
}
</style>
