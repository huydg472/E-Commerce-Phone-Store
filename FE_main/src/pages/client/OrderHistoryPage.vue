<script setup>
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import {useOrderHistoryPage} from '@/composables/useOrderHistoryPage'

const {
  orderLoading,
  searchKeyword,
  selectedStatus,
  pageLoading,
  errorMessage,
  detailModalOpen,
  detailLoading,
  detailError,
  retryLoading,
  retryError,
  statusMap,
  selectedOrder,
  selectedOrderItems,
  selectedOrderPayment,
  selectedPaymentMethod,
  selectedPaymentStatus,
  canRetryVnpayPayment,
  pendingPaymentMethods,
  displayOrders,
  filteredOrders,
  currentPage,
  pageSize,
  totalPages,
  paginatedOrders,
  pageStart,
  pageEnd,
  orderSummary,
  orderTabs,
  loadOrders,
  handleViewDetail,
  closeDetailModal,
  handleRetryVnpayPayment,
  handleOrderPrimaryAction,
  handleReorder,
  getItemImage,
  formatCurrency,
  formatDate,
} = useOrderHistoryPage()
</script>

<template>
  <section class="order-history-page">
    <div class="page-head">
      <div>
        <h1 class="page-title">Đơn hàng của tôi</h1>
        <p class="page-subtitle mb-0">Tra cứu trạng thái, xem chi tiết và mua lại các đơn hàng trước đây.</p>
      </div>
    </div>

    <div class="top-row">
      <div class="filter-card">
        <div class="filter-row">
          <div class="input-box">
            <input
                v-model.trim="searchKeyword"
                type="text"
                placeholder="Tìm theo mã đơn hàng, sản phẩm hoặc địa chỉ"
            />
            <i class="bi bi-search"></i>
          </div>

          <select v-model="selectedStatus" class="status-select">
            <option value="all">Tất cả trạng thái</option>
            <option v-for="tab in orderTabs.slice(1)" :key="tab.key" :value="tab.key">
              {{ tab.label }}
            </option>
          </select>
        </div>

        <div class="tabs">
          <button
              v-for="tab in orderTabs"
              :key="tab.key"
              type="button"
              class="tab-btn"
              :class="{ active: selectedStatus === tab.key }"
              @click="selectedStatus = tab.key"
          >
            {{ tab.label }}
          </button>
        </div>
      </div>

      <div class="summary-card">
        <div
            v-for="item in orderSummary"
            :key="item.label"
            class="summary-item"
        >
          <div class="summary-icon">
            <i :class="item.icon"></i>
          </div>
          <span>{{ item.label }}</span>
          <strong>{{ item.value }}</strong>
        </div>
      </div>
    </div>

    <div v-if="pageLoading || orderLoading" class="loading-card">
      <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
      <p>Đang tải đơn hàng...</p>
    </div>

    <p v-else-if="errorMessage" class="error-message">
      {{ errorMessage }}
    </p>

    <div v-else class="orders-list">
      <article
          v-for="order in paginatedOrders"
          :key="order.id"
          class="order-card"
      >
        <div class="order-card-header">
          <div class="order-code">
            <span>Mã đơn hàng:</span>
            <strong>{{ order.code }}</strong>
          </div>

          <div class="order-date">
            <span>Ngày đặt:</span>
            <strong>{{ order.orderDate }}</strong>
          </div>

          <span class="status-badge" :class="statusMap[order.status]?.className || 'pending'">
            {{ statusMap[order.status]?.label || order.status }}
          </span>
        </div>

        <div class="order-card-body">
          <div class="product-info">
            <div class="product-preview">
              <img
                  v-for="(preview, index) in order.product.previewProducts"
                  :key="`${order.id}-${index}`"
                  :src="preview.image"
                  :alt="preview.name"
                  :class="{ stacked: order.product.previewProducts.length > 1 && index > 0 }"
              />
            </div>

            <div class="product-text">
              <h3>{{ order.product.name }}</h3>
              <p v-if="order.product.color">Phiên bản: {{ order.product.color }}</p>
              <p>
                Số lượng: {{ order.product.quantity }}
                <span v-if="order.product.extraCount">+ {{ order.product.extraCount }} sản phẩm khác</span>
              </p>
            </div>
          </div>

          <div class="order-total">
            <span>Tổng tiền</span>
            <strong>{{ formatCurrency(order.total) }}</strong>
          </div>

          <div class="order-actions">
            <button type="button" class="action-btn outline-btn" @click="handleViewDetail(order)">
              Xem chi tiết
            </button>

            <button
                v-if="order.status === 'pending' && order.paymentStatus !== 'paid' && pendingPaymentMethods.has(order.paymentMethod)"
                type="button"
                class="action-btn primary-btn"
                @click="handleOrderPrimaryAction(order)"
            >
              Thanh toán
            </button>

            <button
                v-else-if="order.status === 'pending' && order.paymentStatus !== 'paid' && order.paymentMethod === 'cod'"
                type="button"
                class="action-btn danger-btn"
                @click="handleOrderPrimaryAction(order)"
            >
              Hủy đơn
            </button>

            <button v-if="order.status !== 'pending'" type="button" class="text-action blue"
                    @click="handleReorder(order)">
              <i class="bi bi-arrow-clockwise"></i>
              Mua lại
            </button>
          </div>
        </div>

        <div class="order-address">
          <div class="order-address__head">
            <span>Địa chỉ nhận hàng</span>
          </div>
          <p>
            <i class="bi bi-geo-alt"></i>
            {{ order.address || 'Chưa có địa chỉ' }}
          </p>
        </div>
      </article>

      <div v-if="filteredOrders.length === 0" class="empty-card">
        <i class="bi bi-bag-x"></i>
        <h3>Không tìm thấy đơn hàng</h3>
        <p>Hãy thử thay đổi từ khóa hoặc bộ lọc trạng thái.</p>
      </div>
    </div>

    <ListPaginationControls
        v-if="!pageLoading && !orderLoading && filteredOrders.length > 0"
        :current-page="currentPage"
        :total-pages="totalPages"
        :page-size="pageSize"
        :total-items="filteredOrders.length"
        :page-start="pageStart"
        :page-end="pageEnd"
        item-label="đơn hàng"
        @update:currentPage="currentPage = $event"
        @update:pageSize="pageSize = $event"
    />

    <Teleport to="body">
      <div v-if="detailModalOpen" class="order-detail-overlay" @click.self="closeDetailModal">
        <section class="order-detail-popup" role="dialog" aria-modal="true" aria-label="Chi tiết đơn hàng">
          <button type="button" class="popup-close" @click="closeDetailModal">
            <i class="bi bi-x-lg"></i>
          </button>

          <div v-if="detailLoading" class="popup-loading">
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
            <p>Đang tải chi tiết đơn hàng...</p>
          </div>

          <p v-else-if="detailError" class="popup-error">
            {{ detailError }}
          </p>

          <template v-else-if="selectedOrder">
            <div class="popup-header">
              <div>
                <nav class="popup-breadcrumb">
                  <span>Đơn hàng của tôi</span>
                  <span>/</span>
                  <strong>Chi tiết đơn hàng</strong>
                </nav>

                <h2>Chi tiết đơn hàng</h2>
                <p>Mã đơn hàng: <strong>{{ selectedOrder.order_code || `#${selectedOrder.id}` }}</strong></p>
              </div>

              <div class="popup-header-actions">
                <span class="status-badge" :class="statusMap[selectedOrder.order_status]?.className || 'pending'">
                  {{ statusMap[selectedOrder.order_status]?.label || selectedOrder.order_status }}
                </span>

                <button
                    v-if="canRetryVnpayPayment"
                    type="button"
                    class="action-btn primary-btn retry-vnpay-btn"
                    :disabled="retryLoading"
                    @click="handleRetryVnpayPayment"
                >
                  <span v-if="retryLoading" class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                  <i v-else class="bi bi-credit-card"></i>
                  Thanh toán VNPay
                </button>
              </div>
            </div>

            <div class="popup-layout">
              <section class="detail-card">
                <h3>Thông tin đơn hàng</h3>
                <div class="popup-info-grid">
                  <div>
                    <span>Ngày đặt</span>
                    <strong>{{ formatDate(selectedOrder.ordered_at || selectedOrder.created_at) }}</strong>
                  </div>
                  <div>
                    <span>Thanh toán</span>
                    <strong class="payment-status" :class="selectedOrder.payment_status === 'paid' ? 'paid' : 'unpaid'">
                      {{ selectedOrder.payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                    </strong>
                  </div>
                  <div>
                    <span>Người nhận</span>
                    <strong>{{ selectedOrder.receiver_name }}</strong>
                  </div>
                  <div>
                    <span>Số điện thoại</span>
                    <strong>{{ selectedOrder.receiver_phone }}</strong>
                  </div>
                </div>

                <div class="address-box">
                  <div class="address-box__head">
                    <span>Địa chỉ giao hàng</span>
                  </div>
                  <p>{{ selectedOrder.shipping_address_text || 'Chưa có địa chỉ' }}</p>
                </div>

                <div v-if="selectedOrder.note" class="note-box">
                  <span>Ghi chú</span>
                  <p>{{ selectedOrder.note }}</p>
                </div>

                <div v-if="selectedOrderPayment" class="payment-box">
                  <span>Thanh toán</span>
                  <p>
                    Phương thức:
                    <strong>{{ selectedPaymentMethod.toUpperCase() || 'N/A' }}</strong>
                  </p>
                  <p>
                    Trạng thái:
                    <strong :class="selectedPaymentStatus === 'paid' ? 'paid-text' : 'unpaid-text'">
                      {{ selectedPaymentStatus === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                    </strong>
                  </p>
                </div>
              </section>

              <section class="detail-card">
                <h3>Sản phẩm</h3>
                <div v-if="selectedOrderItems.length" class="popup-item-list">
                  <article v-for="item in selectedOrderItems" :key="item.id" class="popup-item-row">
                    <img
                        :src="getItemImage(item)"
                        :alt="item.product_name"
                    />
                    <div class="popup-item-info">
                      <h4>{{ item.product_name }}</h4>
                      <p>{{ item.variant_name }}</p>
                    </div>
                    <div class="popup-item-meta">
                      <span>{{ item.quantity }} x {{ formatCurrency(item.unit_price) }}</span>
                      <strong>{{ formatCurrency(item.total_price) }}</strong>
                    </div>
                  </article>
                </div>
              </section>

              <aside class="detail-card summary-card popup-summary">
                <h3>Tóm tắt</h3>
                <div class="summary-line">
                  <span>Tạm tính</span>
                  <strong>{{ formatCurrency(selectedOrder.subtotal || 0) }}</strong>
                </div>
                <div class="summary-line">
                  <span>Phí vận chuyển</span>
                  <strong>{{ formatCurrency(selectedOrder.shipping_fee || 0) }}</strong>
                </div>
                <div class="summary-line">
                  <span>Giảm giá</span>
                  <strong class="discount">{{ formatCurrency(selectedOrder.discount_amount || 0) }}</strong>
                </div>
                <div class="summary-total">
                  <span>Tổng cộng</span>
                  <strong>{{ formatCurrency(selectedOrder.total_amount || 0) }}</strong>
                </div>

                <p v-if="retryError" class="retry-error">
                  {{ retryError }}
                </p>
              </aside>
            </div>
          </template>
        </section>
      </div>
    </Teleport>
  </section>
</template>

<style scoped>
.order-history-page {
  color: #111827;
  font-size: 14px;
}

.breadcrumb-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6b7280;
  margin-bottom: 8px;
}

.breadcrumb-wrap strong {
  color: #2563eb;
  font-weight: 600;
}

.page-head {
  margin-bottom: 18px;
}

.page-title {
  margin: 0 0 6px;
  color: #111827;
  font-size: 26px;
  font-weight: 750;
}

.page-subtitle {
  color: #64748b;
}

.top-row {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 260px;
  gap: 12px;
  margin-bottom: 12px;
}

.filter-card,
.summary-card,
.order-card,
.empty-card,
.loading-card {
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #ffffff;
}

.filter-card {
  padding: 16px 18px 0;
}

.filter-row {
  display: grid;
  grid-template-columns: minmax(220px, 1fr) 170px;
  gap: 12px;
}

.input-box {
  position: relative;
}

.input-box input,
.status-select {
  width: 100%;
  height: 36px;
  border: 1px solid #d8dee9;
  border-radius: 8px;
  outline: none;
  background: #ffffff;
  color: #374151;
  font-size: 14px;
}

.input-box input {
  padding: 0 34px 0 12px;
}

.status-select {
  padding: 0 12px;
}

.input-box input:focus,
.status-select:focus {
  border-color: #2563eb;
}

.input-box i {
  position: absolute;
  top: 50%;
  right: 11px;
  transform: translateY(-50%);
  color: #8a94a6;
  font-size: 14px;
}

.tabs {
  display: flex;
  align-items: center;
  gap: 24px;
  margin-top: 14px;
  overflow-x: auto;
}

.tab-btn {
  position: relative;
  min-width: max-content;
  border: 0;
  background: transparent;
  padding: 0 0 13px;
  color: #111827;
  font-weight: 650;
}

.tab-btn.active {
  color: #2563eb;
}

.tab-btn.active::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  width: 100%;
  height: 2px;
  border-radius: 999px;
  background: #2563eb;
}

.summary-card {
  min-height: 110px;
  padding: 12px 8px;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
}

.summary-item {
  min-width: 0;
  padding: 0 6px;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.summary-item:last-child {
  border-right: 0;
}

.summary-icon {
  width: 26px;
  height: 26px;
  margin-bottom: 6px;
  border-radius: 50%;
  background: #eff6ff;
  color: #2563eb;
  display: grid;
  place-items: center;
}

.summary-item span {
  color: #4b5563;
  font-size: 12px;
  line-height: 1.25;
  text-align: center;
}

.summary-item strong {
  margin-top: 2px;
  color: #111827;
  font-size: 18px;
  line-height: 1;
}

.loading-card {
  min-height: 220px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
}

.loading-card p,
.error-message {
  margin: 0;
  color: #6b7280;
}

.error-message {
  padding: 14px 16px;
  border: 1px solid #fecaca;
  border-radius: 12px;
  background: #fef2f2;
  color: #b91c1c;
}

.orders-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.order-card {
  padding: 18px 20px;
}

.order-card-header {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 14px;
  padding-bottom: 14px;
  border-bottom: 1px solid #eef2f7;
}

.order-code,
.order-date {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #64748b;
  font-size: 14px;
}

.order-code strong,
.order-date strong {
  color: #111827;
  font-weight: 750;
}

.status-badge {
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.status-badge.shipping {
  background: #dbeafe;
  color: #2563eb;
}

.status-badge.completed {
  background: #dcfce7;
  color: #16a34a;
}

.status-badge.pending {
  background: #ffedd5;
  color: #f97316;
}

.status-badge.cancelled {
  background: #f1f5f9;
  color: #64748b;
}

.status-badge.confirmed {
  background: #ede9fe;
  color: #7c3aed;
}

.order-card-body {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 160px 168px;
  gap: 22px;
  align-items: center;
  padding: 16px 0;
}

.product-info {
  display: grid;
  grid-template-columns: 86px minmax(0, 1fr);
  gap: 16px;
  align-items: center;
  min-width: 0;
}

.product-preview {
  width: 86px;
  height: 86px;
  position: relative;
}

.product-preview img {
  position: absolute;
  width: 82px;
  height: 82px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  background: #f3f4f6;
  object-fit: cover;
  top: 0;
  left: 0;
  box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
}

.product-preview img.stacked {
  top: 4px;
  left: 12px;
  opacity: 0.92;
}

.product-text {
  min-width: 0;
}

.product-text h3 {
  margin: 0 0 7px;
  color: #111827;
  font-size: 16px;
  line-height: 1.35;
  font-weight: 750;
}

.product-text p {
  margin: 0 0 3px;
  color: #64748b;
  line-height: 1.4;
}

.product-text p span {
  color: #94a3b8;
}

.order-total {
  min-height: 74px;
  padding-left: 22px;
  border-left: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.order-total span {
  margin-bottom: 7px;
  color: #374151;
  font-weight: 700;
}

.order-total strong {
  color: #2563eb;
  font-size: 19px;
  font-weight: 800;
  white-space: nowrap;
}

.order-actions {
  display: flex;
  flex-direction: column;
  gap: 9px;
}

.action-btn {
  width: 100%;
  height: 36px;
  border: 1px solid #2563eb;
  border-radius: 8px;
  background: #ffffff;
  color: #2563eb;
  font-size: 14px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  white-space: nowrap;
}

.primary-btn {
  background: #2563eb;
  color: #ffffff;
}

.outline-btn {
  background: #ffffff;
  color: #2563eb;
}

.danger-btn {
  background: #dc2626;
  border-color: #dc2626;
  color: #ffffff;
}

.text-action {
  height: 26px;
  border: 0;
  background: transparent;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
}

.text-action.blue {
  color: #2563eb;
}

.order-address {
  padding-top: 13px;
  border-top: 1px solid #eef2f7;
}

.order-address__head,
.address-box__head {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}

.order-address__head > span:first-child,
.address-box__head > span:first-child {
  display: block;
  margin-bottom: 5px;
  color: #374151;
  font-weight: 700;
}

.order-address p {
  margin: 0;
  color: #4b5563;
  font-size: 14px;
  line-height: 1.45;
}

.order-address i {
  margin-right: 5px;
  color: #94a3b8;
}

.empty-card {
  padding: 42px 20px;
  text-align: center;
  color: #6b7280;
}

.empty-card i {
  color: #9ca3af;
  font-size: 42px;
}

.empty-card h3 {
  margin: 8px 0 6px;
  color: #111827;
  font-size: 18px;
  font-weight: 750;
}

.empty-card p {
  margin: 0;
}

.order-detail-overlay {
  position: fixed;
  inset: 0;
  z-index: 1200;
  padding: 20px;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(10px);
  overflow: auto;
}

.order-detail-popup {
  position: relative;
  width: min(100%, 1360px);
  min-height: calc(100vh - 40px);
  margin: 0 auto;
  padding: 24px 24px 28px;
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.98);
  box-shadow: 0 32px 90px rgba(15, 23, 42, 0.28);
}

.popup-close {
  position: absolute;
  top: 18px;
  right: 18px;
  width: 40px;
  height: 40px;
  border: 0;
  border-radius: 50%;
  background: #eff6ff;
  color: #1d4ed8;
  display: grid;
  place-items: center;
}

.popup-loading,
.popup-error {
  min-height: 260px;
  display: grid;
  place-items: center;
  color: #64748b;
}

.popup-loading p {
  margin: 12px 0 0;
}

.popup-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-start;
  margin-bottom: 18px;
  padding-right: 52px;
}

.popup-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
  color: #64748b;
  font-size: 14px;
}

.popup-breadcrumb strong {
  color: #2563eb;
}

.popup-header h2 {
  margin: 0 0 6px;
  font-size: 30px;
  font-weight: 900;
}

.popup-header p {
  margin: 0;
  color: #64748b;
}

.popup-header-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.retry-vnpay-btn {
  width: fit-content;
  min-width: 170px;
  height: 38px;
  padding: 0 14px;
}

.popup-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1.08fr) minmax(0, 0.92fr);
  gap: 18px;
  align-items: stretch;
}

.detail-card {
  min-width: 0;
  display: flex;
  flex-direction: column;
  gap: 16px;
  padding: 20px;
  border: 1px solid #e5edf8;
  border-radius: 20px;
  background: #fff;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
}

.detail-card h3 {
  margin: 0;
  color: #111827;
  font-size: 21px;
  font-weight: 850;
}

.popup-info-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.popup-info-grid div {
  padding: 14px;
  border-radius: 14px;
  border: 1px solid #e5edf8;
  background: #fafcff;
}

.popup-info-grid span {
  display: block;
  margin-bottom: 6px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.popup-info-grid strong {
  color: #111827;
}

.payment-box {
  padding: 14px 16px;
  border: 1px solid #e5edf8;
  border-radius: 14px;
  background: #fff;
}

.payment-box > span {
  display: block;
  margin-bottom: 10px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.payment-box p {
  margin: 0 0 6px;
  color: #374151;
  line-height: 1.5;
}

.payment-box p:last-child {
  margin-bottom: 0;
}

.paid-text {
  color: #16a34a;
}

.unpaid-text {
  color: #f97316;
}

.popup-item-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  min-width: 0;
}

.popup-item-row {
  display: grid;
  grid-template-columns: 88px minmax(0, 1fr) auto;
  gap: 14px;
  align-items: center;
  padding: 14px;
  border-radius: 16px;
  border: 1px solid #e5edf8;
  background: #fff;
  min-width: 0;
}

.popup-item-row img {
  width: 92px;
  height: 92px;
  border-radius: 14px;
  object-fit: cover;
  background: #f3f4f6;
}

.popup-item-info h4 {
  margin: 0 0 6px;
  color: #111827;
  font-size: 16px;
  font-weight: 800;
  word-break: break-word;
}

.popup-item-info p {
  margin: 0 0 3px;
  color: #64748b;
  font-size: 13px;
  line-height: 1.5;
}

.popup-item-meta {
  text-align: right;
  min-width: 0;
}

.popup-item-meta span {
  display: block;
  margin-bottom: 6px;
  color: #64748b;
  font-size: 13px;
}

.popup-item-meta strong {
  color: #2563eb;
  font-size: 16px;
  font-weight: 900;
}

.popup-summary {
  background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
}

.popup-summary .summary-line,
.popup-summary .summary-total {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 14px 16px;
  border-radius: 14px;
  border: 1px solid #e5edf8;
  background: #fff;
}

.popup-summary .summary-line span,
.popup-summary .summary-total span {
  color: #64748b;
  font-weight: 700;
  min-width: 0;
}

.popup-summary .summary-line strong,
.popup-summary .summary-total strong {
  text-align: right;
  white-space: nowrap;
}

.popup-summary .summary-total {
  margin-top: auto;
  border-color: #cfe0ff;
  background: linear-gradient(180deg, #eef4ff 0%, #dfeaff 100%);
}

.retry-error {
  margin: 0;
  color: #b91c1c;
  font-size: 13px;
  font-weight: 600;
  line-height: 1.5;
}

@media (max-width: 1200px) {
  .top-row {
    grid-template-columns: 1fr;
  }

  .popup-layout {
    grid-template-columns: 1fr;
  }

  .detail-card {
    padding: 18px;
  }
}

@media (max-width: 992px) {
  .filter-row {
    grid-template-columns: 1fr;
  }

  .summary-card {
    grid-template-columns: repeat(2, 1fr);
  }

  .order-card-body {
    grid-template-columns: 1fr;
  }

  .order-total {
    min-height: auto;
    padding-left: 0;
    border-left: 0;
  }

  .order-actions {
    width: 100%;
  }

  .popup-item-row {
    grid-template-columns: 72px minmax(0, 1fr);
  }

  .popup-item-row img {
    width: 72px;
    height: 72px;
  }

  .popup-item-meta {
    grid-column: 1 / -1;
    text-align: left;
  }

  .popup-info-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .page-title {
    font-size: 23px;
  }

  .summary-card {
    grid-template-columns: repeat(2, 1fr);
  }

  .summary-item:nth-child(2) {
    border-right: 0;
  }

  .summary-item:nth-child(1),
  .summary-item:nth-child(2) {
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
  }

  .summary-item:nth-child(3),
  .summary-item:nth-child(4) {
    padding-top: 12px;
  }

  .order-card {
    padding: 16px;
  }

  .product-info {
    grid-template-columns: 76px minmax(0, 1fr);
  }

  .product-preview {
    width: 76px;
    height: 76px;
  }

  .product-preview img {
    width: 72px;
    height: 72px;
  }

  .product-text h3 {
    font-size: 15px;
  }

  .order-detail-overlay {
    padding: 8px;
  }

  .order-detail-popup {
    min-height: calc(100vh - 16px);
    padding: 16px;
    border-radius: 20px;
  }

  .popup-header {
    flex-direction: column;
    padding-right: 0;
  }

  .popup-layout {
    gap: 14px;
  }

  .retry-vnpay-btn {
    width: 100%;
  }
}
</style>
