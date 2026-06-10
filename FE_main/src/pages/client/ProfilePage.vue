<script setup>
import {computed, onMounted, ref} from 'vue'
import {useAuthStore} from '@/stores/authStore'
import {useOrderStore} from '@/stores/orderStore'
import {shippingAddressService} from '@/services/shippingAddressService'

const authStore = useAuthStore()
const orderStore = useOrderStore()

const orderCount = ref(0)
const addressCount = ref(0)

const profileFallback = {
  name: 'Nguyễn Văn A',
  phone: '0901 234 567',
  email: 'nguyenvana@gmail.com',
  joinedAt: '12/03/2024',
}

const formatJoinedDate = (value) => {
  if (!value) {
    return ''
  }

  const date = new Date(value)

  if (Number.isNaN(date.getTime())) {
    return String(value).replaceAll('/', '-')
  }

  const day = String(date.getDate()).padStart(2, '0')
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const year = date.getFullYear()

  return `${day}-${month}-${year}`
}

const resolveListPayload = (response, fallback = []) => {
  const candidates = [
    response?.data,
    response?.data?.data,
    response?.data?.data?.data,
  ]

  for (const candidate of candidates) {
    if (Array.isArray(candidate)) {
      return candidate
    }

    if (Array.isArray(candidate?.data)) {
      return candidate.data
    }

    if (Array.isArray(candidate?.items)) {
      return candidate.items
    }
  }

  return Array.isArray(fallback) ? fallback : []
}

const resolveTotalCount = (response, fallback = []) => {
  const totalCandidates = [
    response?.data?.meta?.total,
    response?.data?.data?.meta?.total,
    response?.data?.total,
    response?.data?.data?.total,
  ]

  for (const total of totalCandidates) {
    const numericTotal = Number(total)
    if (Number.isFinite(numericTotal)) {
      return numericTotal
    }
  }

  const list = resolveListPayload(response, fallback)
  return list.length
}

const userSummary = computed(() => ({
  name: authStore.user?.name || authStore.user?.full_name || authStore.user?.username || profileFallback.name,
  phone: authStore.user?.phone || authStore.user?.phone_number || profileFallback.phone,
  email: authStore.user?.email || profileFallback.email,
  joinedAt: formatJoinedDate(authStore.user?.created_at || authStore.user?.joined_at || profileFallback.joinedAt),
}))

const stats = computed(() => [
  {
    value: orderCount.value,
    label: 'Đơn hàng',
    icon: 'bi bi-bag',
    to: {name: 'orders.history'},
  },
  {
    value: addressCount.value,
    label: 'Địa chỉ',
    icon: 'bi bi-geo-alt',
    to: {name: 'shipping-addresses'},
  },
])

const loadSummaryCounts = async () => {
  const [ordersResult, addressesResult] = await Promise.allSettled([
    orderStore.items.length ? Promise.resolve(null) : orderStore.fetchAll(),
    shippingAddressService.getAll(),
  ])

  if (ordersResult.status === 'fulfilled') {
    const response = ordersResult.value
    orderCount.value = resolveTotalCount(response, orderStore.items)
  } else {
    orderCount.value = Array.isArray(orderStore.items) ? orderStore.items.length : 0
  }

  if (addressesResult.status === 'fulfilled') {
    const response = addressesResult.value
    addressCount.value = resolveTotalCount(response)
  } else {
    addressCount.value = 0
  }
}

onMounted(() => {
  void loadSummaryCounts()
})
</script>

<template>
  <section class="account-page">
    <nav class="account-breadcrumb mb-2">
      <span>Trang chủ</span>
      <span>/</span>
      <strong>Tài khoản của tôi</strong>
    </nav>

    <div class="page-head">
      <div>
        <h1 class="page-title mb-1">Tài khoản của tôi</h1>
        <p class="page-subtitle mb-0">Xem nhanh trạng thái tài khoản, đơn hàng và các lối tắt quan trọng.</p>
      </div>
    </div>

    <section class="account-card mb-3">
      <h5 class="section-title mb-3">Tổng quan</h5>

      <div class="account-overview">
        <div class="user-info">
          <h4 class="user-name mb-2">{{ userSummary.name }}</h4>
          <p>
            <i class="bi bi-telephone"></i>
            {{ userSummary.phone }}
          </p>
          <p>
            <i class="bi bi-envelope"></i>
            {{ userSummary.email }}
          </p>
          <p>
            <i class="bi bi-clock-history"></i>
            Tham gia ngày: {{ userSummary.joinedAt }}
          </p>
        </div>

        <div class="overview-stats">
          <RouterLink
              v-for="stat in stats"
              :key="stat.label"
              :to="stat.to"
              class="stat-item"
          >
            <div class="stat-icon">
              <i :class="stat.icon"></i>
            </div>

            <strong>{{ stat.value }}</strong>
            <span>{{ stat.label }}</span>
            <em>Xem chi tiết</em>
          </RouterLink>
        </div>
      </div>
    </section>

    <div class="row g-3">
      <div class="col-md-4">
        <section class="mini-card">
          <div class="mini-content">
            <div>
              <h6>
                <i class="bi bi-bag"></i>
                Đơn hàng gần đây
              </h6>

              <p>Bạn đã đặt <strong>{{ stats[0].value }}</strong> đơn hàng</p>

              <small>Theo dõi trạng thái và xem lại lịch sử mua hàng</small>
            </div>
          </div>

          <RouterLink :to="{ name: 'orders.history' }" class="btn btn-outline-primary btn-sm">
            Xem đơn hàng
            <i class="bi bi-arrow-right"></i>
          </RouterLink>
        </section>
      </div>

      <div class="col-md-4">
        <section class="mini-card">
          <div class="mini-content">
            <div>
              <h6>
                <i class="bi bi-person"></i>
                Thông tin cá nhân
              </h6>

              <p>Họ tên, email và số điện thoại</p>

              <small>Chỉnh sửa hồ sơ để nhận thông báo chính xác hơn</small>
            </div>
          </div>

          <RouterLink :to="{ name: 'profile.edit' }" class="btn btn-outline-primary btn-sm">
            Cập nhật hồ sơ
            <i class="bi bi-arrow-right"></i>
          </RouterLink>
        </section>
      </div>

      <div class="col-md-4">
        <section class="mini-card">
          <div class="mini-content">
            <div>
              <h6>
                <i class="bi bi-shield-lock"></i>
                Bảo mật tài khoản
              </h6>

              <p>Đổi mật khẩu để tăng an toàn</p>

              <small>Khuyến nghị cập nhật mật khẩu định kỳ</small>
            </div>
          </div>

          <RouterLink :to="{ name: 'change-password' }" class="btn btn-outline-primary btn-sm">
            Đổi mật khẩu
            <i class="bi bi-arrow-right"></i>
          </RouterLink>
        </section>
      </div>
    </div>
  </section>
</template>

<style scoped>
.account-page {
  color: #0f172a;
  font-size: 14px;
}

.account-breadcrumb {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #6b7280;
  font-size: 14px;
}

.account-breadcrumb strong {
  color: #2563eb;
  font-weight: 600;
}

.page-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.page-title {
  font-size: 28px;
  font-weight: 750;
  color: #111827;
}

.page-subtitle {
  color: #64748b;
}

.account-card,
.mini-card {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
}

.account-card {
  padding: 18px 22px;
}

.section-title {
  font-size: 17px;
  font-weight: 750;
  color: #111827;
}

.account-overview {
  display: grid;
  grid-template-columns: 1fr 1.4fr;
  align-items: center;
  gap: 24px;
}

.user-name {
  font-size: 20px;
  font-weight: 750;
}

.user-info p {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 10px;
  color: #334155;
}

.user-info i {
  color: #475569;
  font-size: 17px;
}

.overview-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  border-left: 1px solid #e5e7eb;
}

.stat-item {
  min-height: 118px;
  text-align: center;
  border-right: 1px solid #e5e7eb;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-decoration: none;
  color: inherit;
}

.stat-item:last-child {
  border-right: none;
}

.stat-icon {
  width: 38px;
  height: 38px;
  background: #eef4ff;
  color: #0d6efd;
  border-radius: 50%;
  display: grid;
  place-items: center;
  font-size: 18px;
  margin-bottom: 6px;
}

.stat-item strong {
  font-size: 22px;
  color: #111827;
  line-height: 1;
}

.stat-item span {
  color: #374151;
  margin-top: 4px;
}

.stat-item em {
  color: #0d6efd;
  font-style: normal;
  font-size: 13px;
  margin-top: 6px;
}

.mini-card {
  padding: 16px;
  height: 100%;
}

.mini-content {
  display: flex;
  align-items: flex-start;
  min-height: 90px;
}

.mini-content h6 {
  font-weight: 750;
  color: #111827;
  margin-bottom: 10px;
}

.mini-content h6 i {
  color: #0d6efd;
  margin-right: 8px;
}

.mini-content p {
  margin-bottom: 2px;
  color: #334155;
}

.mini-content small {
  color: #64748b;
  line-height: 1.5;
}

.mini-card .btn {
  min-width: 126px;
  height: 34px;
  margin-top: 12px;
  font-size: 13px;
}

@media (max-width: 1200px) {
  .account-overview {
    grid-template-columns: 1fr;
  }

  .overview-stats {
    grid-column: 1 / -1;
    border-left: none;
    border-top: 1px solid #e5e7eb;
    padding-top: 16px;
  }
}

@media (max-width: 768px) {
  .page-head {
    flex-direction: column;
  }

  .page-title {
    font-size: 24px;
  }

  .account-overview {
    grid-template-columns: 1fr;
  }

  .overview-stats {
    grid-template-columns: 1fr;
  }

  .stat-item {
    border-right: none;
    border-bottom: 1px solid #e5e7eb;
  }

  .stat-item:last-child {
    border-bottom: none;
  }
}
</style>
1
