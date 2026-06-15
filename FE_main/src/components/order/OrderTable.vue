<script setup>
import {formatCurrency} from '@/utils/formatCurrency'

const props = defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['view', 'status-change'])

const orderStatusMap = {
  pending: {label: 'Chờ xác nhận', className: 'pending'},
  confirmed: {label: 'Đã xác nhận', className: 'confirmed'},
  processing: {label: 'Đang xử lý', className: 'processing'},
  shipping: {label: 'Đang giao', className: 'shipping'},
  completed: {label: 'Hoàn thành', className: 'completed'},
  cancelled: {label: 'Đã hủy', className: 'cancelled'},
}

const orderStatusOptions = ['pending', 'confirmed', 'processing', 'shipping', 'completed', 'cancelled']

const paymentStatusMap = {
  unpaid: {label: 'Chưa thanh toán', className: 'unpaid'},
  pending: {label: 'Chờ thanh toán', className: 'pending'},
  paid: {label: 'Đã thanh toán', className: 'paid'},
  failed: {label: 'Thất bại', className: 'failed'},
  refunded: {label: 'Đã hoàn tiền', className: 'refunded'},
}

const paymentMethodMap = {
  cod: 'COD',
  bank_transfer: 'Chuyển khoản',
  momo: 'MoMo',
  vnpay: 'VNPay',
}

const orderStatusSteps = ['pending', 'confirmed', 'processing', 'shipping', 'completed']

const isSelectableOrderStatus = (currentStatus, targetStatus) => {
  if (currentStatus === targetStatus) {
    return true
  }

  if (targetStatus === 'cancelled') {
    return currentStatus !== 'completed' && currentStatus !== 'cancelled'
  }

  if (currentStatus === 'cancelled') {
    return targetStatus === 'pending'
  }

  const currentIndex = orderStatusSteps.indexOf(currentStatus)
  const targetIndex = orderStatusSteps.indexOf(targetStatus)

  if (currentIndex === -1 || targetIndex === -1) {
    return false
  }

  return Math.abs(targetIndex - currentIndex) === 1
}
</script>

<template>
  <section class="table-card">
    <div class="table-responsive">
      <table class="table align-middle admin-table mb-0">
        <colgroup>
          <col class="col-order-code"/>
          <col class="col-customer"/>
          <col class="col-product"/>
          <col class="col-total"/>
          <col class="col-payment-status"/>
          <col class="col-payment-method"/>
          <col class="col-order-status"/>
          <col class="col-date"/>
          <col class="col-actions"/>
        </colgroup>
        <thead>
        <tr>
          <th>Mã đơn</th>
          <th>Khách hàng</th>
          <th>Sản phẩm</th>
          <th>Tổng tiền</th>
          <th>Trạng thái thanh toán</th>
          <th>Phương thức</th>
          <th>Trạng thái</th>
          <th>Ngày đặt</th>
          <th>Thao tác</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="order in orders" :key="order.id">
          <td>
            <div class="order-code-cell">
              <strong>{{ order.code }}</strong>
              <small>{{ order.itemCount }} sản phẩm</small>
            </div>
          </td>
          <td>
            <div class="customer-cell">
              <strong>{{ order.customer }}</strong>
              <small>{{ order.phone || 'Chưa có số điện thoại' }}</small>
            </div>
          </td>
          <td>
            <div class="product-cell">
              <img :src="order.thumbnail" :alt="order.firstProductName"/>
              <div class="product-meta">
                <strong>{{ order.firstProductName }}</strong>
                <small v-if="order.firstVariantName">Phiên bản: {{ order.firstVariantName }}</small>
                <small v-else>Chưa có chi tiết biến thể</small>
              </div>
            </div>
          </td>
          <td class="fw-semibold">{{ formatCurrency(order.total) }}</td>
          <td>
            <div class="payment-stack">
                <span class="payment-pill" :class="paymentStatusMap[order.paymentStatus]?.className || 'unpaid'">
                  {{ paymentStatusMap[order.paymentStatus]?.label || order.paymentStatus }}
                </span>
            </div>
          </td>
          <td>
            <span class="payment-method">{{ paymentMethodMap[order.paymentMethod] || order.paymentMethod }}</span>
          </td>
          <td>
            <div class="status-control">
              <select
                  :value="order.orderStatus"
                  class="status-select"
                  :class="orderStatusMap[order.orderStatus]?.className || 'pending'"
                  :disabled="order.orderStatus === 'completed'"
                  @change="emit('status-change', order, $event.target.value)"
              >
                <option
                    v-for="status in orderStatusOptions"
                    :key="status"
                    :value="status"
                    :disabled="!isSelectableOrderStatus(order.orderStatus, status)"
                >
                  {{ orderStatusMap[status]?.label || status }}
                </option>
              </select>
              <i class="bi bi-chevron-down status-caret"></i>
            </div>
          </td>
          <td>
            <div class="date-stack">
              <strong>{{ order.orderedAt }}</strong>
              <small>Cập nhật {{ order.updatedAt }}</small>
            </div>
          </td>
          <td>
            <div class="action-group">
              <button type="button" class="action-btn action-view" @click="emit('view', order)" title="Xem chi tiết">
                <i class="bi bi-eye"></i>
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="!orders.length">
          <td colspan="9">
            <div class="empty-state">
              <i class="bi bi-bag-x"></i>
              <p>Không có đơn hàng phù hợp.</p>
            </div>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>

<style scoped>
.table-card {
  border: 1px solid #e5eaf3;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  width: 100%;
  min-width: 1440px;
  table-layout: fixed;
}

.col-order-code {
  width: 200px;
}

.col-customer {
  width: 160px;
}

.col-product {
  width: 300px;
}

.col-total {
  width: 120px;
}

.col-payment-status {
  width: 170px;
}

.col-payment-method {
  width: 100px;
}

.col-order-status {
  width: 170px;
}

.col-date {
  width: 180px;
}

.col-actions {
  width: 92px;
}

.admin-table thead th {
  padding: 16px;
  border-bottom: 1px solid #e5eaf3;
  color: #64748b;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
  text-align: left;
}

.admin-table tbody td {
  padding: 16px;
  border-bottom: 1px solid #eef2f7;
  color: #0f172a;
  font-size: 14px;
  vertical-align: middle;
  text-align: left;
}

.admin-table tbody td:nth-child(1),
.admin-table tbody td:nth-child(2),
.admin-table tbody td:nth-child(3),
.admin-table tbody td:nth-child(7),
.admin-table tbody td:nth-child(8) {
  white-space: normal;
}

.order-code-cell,
.customer-cell,
.date-stack {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.customer-cell small,
.order-code-cell small,
.product-meta small,
.date-stack small {
  color: #64748b;
}

.product-cell {
  display: flex;
  align-items: center;
  gap: 12px;
}

.product-cell img {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  object-fit: cover;
  background: #f1f5f9;
}

.product-meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.payment-stack {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.payment-pill {
  display: inline-flex;
  padding: 7px 14px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
}

.payment-pill.unpaid {
  color: #b45309;
  background: #fff7ed;
}

.payment-pill.pending {
  color: #b45309;
  background: #fff7ed;
}

.payment-pill.paid {
  color: #15803d;
  background: #ecfdf5;
}

.payment-pill.failed {
  color: #dc2626;
  background: #fef2f2;
}

.payment-pill.refunded {
  color: #1d4ed8;
  background: #eff6ff;
}

.payment-method {
  color: #334155;
  font-weight: 700;
  white-space: nowrap;
}

.status-control {
  position: relative;
  width: 100%;
  max-width: 150px;
  margin-right: auto;
}

.status-select {
  width: 100%;
  box-sizing: border-box;
  min-height: 40px;
  padding: 0 34px 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 999px;
  font-weight: 800;
  font-size: 13px;
  line-height: 1.1;
  outline: none;
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}

.status-caret {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: currentColor;
  font-size: 12px;
}

.status-select.pending {
  background: #fff7ed;
  color: #c2410c;
}

.status-select.confirmed,
.status-select.processing,
.status-select.shipping {
  background: #eff6ff;
  color: #1d4ed8;
}

.status-select.completed {
  background: #ecfdf5;
  color: #15803d;
}

.status-select.cancelled {
  background: #fef2f2;
  color: #dc2626;
}

.action-group {
  display: flex;
  justify-content: flex-start;
}

.action-btn {
  width: 36px;
  height: 36px;
  display: inline-grid;
  place-items: center;
  border-radius: 11px;
  border: 0;
  flex: 0 0 auto;
}

.action-view {
  color: #2563eb;
  background: #eff6ff;
}

.empty-state {
  min-height: 160px;
  display: grid;
  place-items: center;
  gap: 10px;
  color: #64748b;
  text-align: center;
}

.date-stack strong,
.date-stack small {
  white-space: nowrap;
}

.date-stack strong {
  font-size: 15px;
}

.date-stack small {
  font-size: 12px;
}

.status-select {
  min-width: 0;
}
</style>
