<script setup>
import {computed, onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import {useOrderStore} from '@/stores/orderStore'
import {formatCurrency} from '@/utils/formatCurrency'
import {formatDate} from '@/utils/formatDate'

const router = useRouter()
const orderStore = useOrderStore()

const searchKeyword = ref('')
const selectedStatus = ref('all')
const pageLoading = ref(true)
const errorMessage = ref('')

const statusMap = {
  pending: {label: 'Chờ xác nhận', className: 'pending'},
  confirmed: {label: 'Đã xác nhận', className: 'confirmed'},
  shipping: {label: 'Đang giao', className: 'shipping'},
  completed: {label: 'Hoàn thành', className: 'completed'},
  cancelled: {label: 'Đã hủy', className: 'cancelled'},
}

const orders = computed(() => {
  const source = Array.isArray(orderStore.items) ? orderStore.items : []

  return source.map((order) => {
    const firstItem = Array.isArray(order.orderItems) ? order.orderItems[0] : null
    const variant = firstItem?.productVariant ?? firstItem?.product_variant ?? null
    const product = variant?.product ?? null
    const image = product?.thumbnail_url || product?.thumbnailUrl || product?.image || '/images/default-product.png'

    return {
      id: order.id,
      code: order.order_code || `#${order.id}`,
      orderDate: formatDate(order.ordered_at || order.created_at),
      status: order.order_status || 'pending',
      total: toNumber(order.total_amount),
      address: order.shipping_address_text || '',
      product: {
        name: firstItem?.product_name || product?.name || 'Sản phẩm',
        color: firstItem?.variant_name || '',
        quantity: firstItem?.quantity || 0,
        image,
      },
    }
  })
})

const toNumber = (value) => {
  const numericValue = Number(value)
  return Number.isFinite(numericValue) ? numericValue : 0
}

const filteredOrders = computed(() => {
  const keyword = searchKeyword.value.trim().toLowerCase()

  return orders.value.filter((order) => {
    const matchesStatus = selectedStatus.value === 'all' || order.status === selectedStatus.value
    const matchesKeyword =
        !keyword ||
        order.code.toLowerCase().includes(keyword) ||
        order.product.name.toLowerCase().includes(keyword) ||
        order.address.toLowerCase().includes(keyword)

    return matchesStatus && matchesKeyword
  })
})

const orderSummary = computed(() => [
  {
    label: 'Tổng đơn hàng',
    value: orders.value.length,
    icon: 'bi bi-bag',
  },
  {
    label: 'Đang giao',
    value: orders.value.filter((order) => order.status === 'shipping').length,
    icon: 'bi bi-truck',
  },
  {
    label: 'Hoàn thành',
    value: orders.value.filter((order) => order.status === 'completed').length,
    icon: 'bi bi-check-circle',
  },
  {
    label: 'Đã hủy',
    value: orders.value.filter((order) => order.status === 'cancelled').length,
    icon: 'bi bi-x-circle',
  },
])

const orderTabs = [
  {key: 'all', label: 'Tất cả'},
  {key: 'pending', label: 'Chờ xác nhận'},
  {key: 'shipping', label: 'Đang giao'},
  {key: 'completed', label: 'Hoàn thành'},
  {key: 'cancelled', label: 'Đã hủy'},
]

const loadOrders = async () => {
  pageLoading.value = true
  errorMessage.value = ''

  try {
    await orderStore.fetchAll()
  } catch (error) {
    if (error.response?.status === 401) {
      await router.replace({name: 'login'})
      return
    }

    errorMessage.value = error.response?.data?.message || 'Không tải được danh sách đơn hàng.'
  } finally {
    pageLoading.value = false
  }
}

const handleViewDetail = (order) => {
  router.push({name: 'orders.show', params: {id: order.id}})
}

onMounted(loadOrders)
</script>

<template>
  <main class="order-history-page">
    <div class="order-history-container">
      <div class="breadcrumb-wrap">
        <span>Trang chủ</span>
        <span>/</span>
        <strong>Đơn hàng của tôi</strong>
      </div>

      <h1 class="page-title">Đơn hàng của tôi</h1>

      <div class="account-layout">
        <aside class="account-sidebar">
          <RouterLink
              v-for="item in [
                { key: 'overview', label: 'Tổng quan', icon: 'bi bi-house-door', to: '/tai-khoan' },
                { key: 'profile', label: 'Thông tin cá nhân', icon: 'bi bi-person', to: '/tai-khoan/thong-tin-ca-nhan' },
                { key: 'address', label: 'Sổ địa chỉ', icon: 'bi bi-geo-alt', to: '/tai-khoan/so-dia-chi' },
                { key: 'password', label: 'Đổi mật khẩu', icon: 'bi bi-lock', to: '/tai-khoan/doi-mat-khau' },
                { key: 'orders', label: 'Đơn hàng của tôi', icon: 'bi bi-bag', to: '/orders' },
              ]"
              :key="item.key"
              :to="item.to"
              class="sidebar-link"
              :class="{ active: item.key === 'orders' }"
          >
            <i :class="item.icon"></i>
            <span>{{ item.label }}</span>
          </RouterLink>
        </aside>

        <section class="orders-content">
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

          <div v-if="pageLoading" class="loading-card">
            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
            <p>Đang tải đơn hàng...</p>
          </div>

          <p v-else-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </p>

          <div v-else class="orders-list">
            <article
                v-for="order in filteredOrders"
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
                  <img :src="order.product.image" :alt="order.product.name"/>

                  <div class="product-text">
                    <h3>{{ order.product.name }}</h3>
                    <p v-if="order.product.color">Phiên bản: {{ order.product.color }}</p>
                    <p>Số lượng: {{ order.product.quantity }}</p>
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

                  <button v-if="order.status === 'pending'" type="button" class="action-btn primary-btn"
                          @click="handleViewDetail(order)">
                    Xem đơn chờ thanh toán
                  </button>

                  <button v-if="order.status !== 'pending'" type="button" class="text-action blue">
                    <i class="bi bi-arrow-clockwise"></i>
                    Mua lại
                  </button>
                </div>
              </div>

              <div class="order-address">
                <span>Địa chỉ nhận hàng</span>
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
        </section>
      </div>
    </div>
  </main>
</template>

<style scoped>
.order-history-page {
  background: #ffffff;
  color: #111827;
  font-size: 14px;
}

.order-history-container {
  width: 100%;
  max-width: 1180px;
  margin: 0 auto;
  padding: 22px 20px 36px;
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

.page-title {
  margin: 0 0 18px;
  color: #111827;
  font-size: 26px;
  font-weight: 750;
}

.account-layout {
  display: grid;
  grid-template-columns: 220px minmax(0, 1fr);
  gap: 18px;
  align-items: flex-start;
}

.account-sidebar {
  min-height: 220px;
  padding: 16px 14px;
  border: 1px solid #e5e7eb;
  border-radius: 7px;
  background: #ffffff;
}

.sidebar-link {
  width: 100%;
  height: 40px;
  padding: 0 12px;
  border: 0;
  border-radius: 6px;
  background: transparent;
  display: flex;
  align-items: center;
  gap: 12px;
  color: #4b5563;
  font-weight: 600;
  text-decoration: none;
  transition: 0.2s ease;
}

.sidebar-link i {
  width: 18px;
  color: #8a94a6;
  font-size: 16px;
}

.sidebar-link:hover,
.sidebar-link.active {
  background: #edf4ff;
  color: #2563eb;
}

.sidebar-link:hover i,
.sidebar-link.active i {
  color: #2563eb;
}

.orders-content {
  min-width: 0;
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
  border-radius: 7px;
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
  border-radius: 5px;
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
  border-radius: 7px;
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
  border-radius: 6px;
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
  grid-template-columns: 82px minmax(0, 1fr);
  gap: 16px;
  align-items: center;
  min-width: 0;
}

.product-info img {
  width: 82px;
  height: 82px;
  border-radius: 6px;
  border: 1px solid #e5e7eb;
  background: #f3f4f6;
  object-fit: cover;
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
  border-radius: 5px;
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

.text-action.red {
  color: #dc2626;
}

.order-address {
  padding-top: 13px;
  border-top: 1px solid #eef2f7;
}

.order-address span {
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

@media (max-width: 1200px) {
  .order-history-container {
    max-width: 1080px;
  }

  .top-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 992px) {
  .account-layout {
    grid-template-columns: 1fr;
  }

  .account-sidebar {
    min-height: auto;
  }

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
}

@media (max-width: 768px) {
  .order-history-container {
    padding: 18px 14px 30px;
  }

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
    grid-template-columns: 72px minmax(0, 1fr);
  }

  .product-info img {
    width: 72px;
    height: 72px;
  }

  .product-text h3 {
    font-size: 15px;
  }
}
</style>
