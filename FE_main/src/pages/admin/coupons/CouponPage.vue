<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import {couponService} from '@/services/couponService'
import {formatDate} from '@/utils/formatDate'
import {formatMoney} from '@/utils/reportHelpers'
import {useClientPagination} from '@/composables/useClientPagination.js'
import {useNotificationStore} from '@/stores/notificationStore.js'

const loading = ref(true)
const errorMessage = ref('')
const search = ref('')
const statusFilter = ref('all')
const typeFilter = ref('all')
const saving = ref(false)
const deletingId = ref(null)
const editingId = ref(null)
const showModal = ref(false)

const form = reactive({
  code: '',
  name: '',
  description: '',
  type: 'percentage',
  value: '',
  max_discount: '',
  min_order_amount: '',
  usage_limit: '',
  starts_at: '',
  ends_at: '',
  is_active: true,
})

const coupons = ref([])

const normalize = (value) => String(value ?? '').trim().toLowerCase()

const isExpired = (coupon) => {
  if (!coupon?.ends_at) {
    return false
  }

  const endsAt = new Date(coupon.ends_at)
  return !Number.isNaN(endsAt.getTime()) && endsAt.getTime() < Date.now()
}

const displayCoupons = computed(() => (Array.isArray(coupons.value) ? coupons.value : []))

const filteredCoupons = computed(() => {
  const query = normalize(search.value)

  return displayCoupons.value.filter((coupon) => {
    const matchesQuery =
        !query ||
        [coupon.code, coupon.name, coupon.description]
            .filter(Boolean)
            .some((field) => normalize(field).includes(query))

    const matchesStatus =
        statusFilter.value === 'all' ||
        (statusFilter.value === 'active' && coupon.is_active && !isExpired(coupon)) ||
        (statusFilter.value === 'inactive' && !coupon.is_active && !isExpired(coupon)) ||
        (statusFilter.value === 'expired' && isExpired(coupon))

    const matchesType = typeFilter.value === 'all' || coupon.type === typeFilter.value

    return matchesQuery && matchesStatus && matchesType
  })
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems,
  pageStart,
  pageEnd,
} = useClientPagination(filteredCoupons, {
  defaultPageSize: 5,
  pageSizeOptions: [5, 10],
})

const summary = computed(() => {
  const total = displayCoupons.value.length
  const active = displayCoupons.value.filter((coupon) => coupon.is_active && !isExpired(coupon)).length
  const expired = displayCoupons.value.filter((coupon) => isExpired(coupon)).length
  const fixed = displayCoupons.value.filter((coupon) => coupon.type === 'fixed').length

  return [
    {label: 'Tổng coupon', value: total, icon: 'bi bi-ticket-perforated', tone: 'blue'},
    {label: 'Đang hoạt động', value: active, icon: 'bi bi-toggle-on', tone: 'green'},
    {label: 'Hết hạn', value: expired, icon: 'bi bi-calendar-x', tone: 'orange'},
    {label: 'Giảm cố định', value: fixed, icon: 'bi bi-cash-coin', tone: 'slate'},
  ]
})

const resetForm = () => {
  form.code = ''
  form.name = ''
  form.description = ''
  form.type = 'percentage'
  form.value = ''
  form.max_discount = ''
  form.min_order_amount = ''
  form.usage_limit = ''
  form.starts_at = ''
  form.ends_at = ''
  form.is_active = true
  editingId.value = null
}

const openCreateModal = () => {
  resetForm()
  errorMessage.value = ''
  showModal.value = true
}

const openEditModal = (coupon) => {
  editingId.value = coupon.id
  form.code = coupon.code || ''
  form.name = coupon.name || ''
  form.description = coupon.description || ''
  form.type = coupon.type || 'percentage'
  form.value = String(coupon.value ?? '')
  form.max_discount = String(coupon.max_discount ?? '')
  form.min_order_amount = String(coupon.min_order_amount ?? '')
  form.usage_limit = String(coupon.usage_limit ?? '')
  form.starts_at = coupon.starts_at ? String(coupon.starts_at).slice(0, 16) : ''
  form.ends_at = coupon.ends_at ? String(coupon.ends_at).slice(0, 16) : ''
  form.is_active = Boolean(coupon.is_active)
  errorMessage.value = ''
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  errorMessage.value = ''
  resetForm()
}

const loadCoupons = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await couponService.getAll()
    const payload = response.data?.data ?? response.data ?? []
    coupons.value = Array.isArray(payload) ? payload : (payload?.data ?? [])
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không tải được danh sách coupon.'
  } finally {
    loading.value = false
  }
}

const submitForm = async () => {
  saving.value = true
  errorMessage.value = ''

  try {
    const payload = {
      code: form.code.trim().toUpperCase(),
      name: form.name.trim(),
      description: form.description.trim() || null,
      type: form.type,
      value: Number(form.value),
      max_discount: form.max_discount === '' ? null : Number(form.max_discount),
      min_order_amount: form.min_order_amount === '' ? 0 : Number(form.min_order_amount),
      usage_limit: form.usage_limit === '' ? null : Number(form.usage_limit),
      starts_at: form.starts_at || null,
      ends_at: form.ends_at || null,
      is_active: Boolean(form.is_active),
    }

    if (editingId.value) {
      await couponService.update(editingId.value, payload)
      useNotificationStore().success('Đã sửa coupon.')
    } else {
      await couponService.create(payload)
      useNotificationStore().success('Đã thêm coupon.')
    }

    await loadCoupons()
    closeModal()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không lưu được coupon.'
  } finally {
    saving.value = false
  }
}

const handleToggleStatus = async (coupon) => {
  try {
    await couponService.toggleStatus(coupon.id)
    useNotificationStore().success('Đã đổi trạng thái coupon.')
    await loadCoupons()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không đổi được trạng thái coupon.'
  }
}

const handleDelete = async (coupon) => {
  if (!coupon || deletingId.value) {
    return
  }

  if (!window.confirm(`Xóa coupon "${coupon.code}"?`)) {
    return
  }

  deletingId.value = coupon.id

  try {
    await couponService.delete(coupon.id)
    useNotificationStore().success('Đã xóa coupon.')
    await loadCoupons()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không xóa được coupon.'
  } finally {
    deletingId.value = null
  }
}

const formatCouponValue = (coupon) => {
  if (!coupon) {
    return '--'
  }

  if (coupon.type === 'percentage') {
    return `${Number(coupon.value || 0)}%`
  }

  return formatMoney(coupon.value)
}

const formatCouponStatus = (coupon) => {
  if (isExpired(coupon)) {
    return 'Hết hạn'
  }

  return coupon.is_active ? 'Đang bật' : 'Đang tắt'
}

const formatCouponBadgeClass = (coupon) => {
  if (isExpired(coupon)) {
    return 'expired'
  }

  return coupon.is_active ? 'active' : 'inactive'
}

onMounted(loadCoupons)
</script>

<template>
  <div class="admin-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Khuyến mãi</p>
        <h1>Quản lý coupon</h1>
        <p class="subtitle">Tạo và kiểm soát mã giảm giá áp dụng cho khách hàng khi thanh toán.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="openCreateModal">
            <i class="bi bi-plus-lg"></i>
            Thêm coupon
          </button>

          <button type="button" class="secondary-action" @click="loadCoupons">
            <i class="bi bi-arrow-clockwise"></i>
            Làm mới
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <article v-for="item in summary" :key="item.label" class="stat-card">
          <span class="stat-icon" :class="`tone-${item.tone}`">
            <i :class="item.icon"></i>
          </span>
          <div>
            <strong>{{ item.value }}</strong>
            <span>{{ item.label }}</span>
          </div>
        </article>
      </div>
    </section>

    <section class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model.trim="search" type="search" placeholder="Tìm theo mã, tên, mô tả..." />
      </div>

      <div class="filter-row">
        <select v-model="statusFilter" class="filter-select">
          <option value="all">Tất cả trạng thái</option>
          <option value="active">Đang hoạt động</option>
          <option value="inactive">Đang tắt</option>
          <option value="expired">Hết hạn</option>
        </select>

        <select v-model="typeFilter" class="filter-select">
          <option value="all">Tất cả kiểu</option>
          <option value="percentage">Giảm %</option>
          <option value="fixed">Giảm tiền</option>
        </select>
      </div>
    </section>

    <section v-if="errorMessage && !displayCoupons.length" class="state-card error-state">
      <i class="bi bi-exclamation-triangle"></i>
      <p>{{ errorMessage }}</p>
      <button type="button" class="secondary-action" @click="loadCoupons">Thử lại</button>
    </section>

    <section v-else-if="loading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải coupon...</p>
    </section>

    <section v-else class="table-card">
      <div class="table-header">
        <div>
          <h2>Danh sách coupon</h2>
          <p>Hiện thị {{ filteredCoupons.length }} coupon phù hợp.</p>
        </div>
        <div class="table-chip">
          <i class="bi bi-funnel"></i>
          <span>{{ filteredCoupons.length }} kết quả</span>
        </div>
      </div>

      <div v-if="errorMessage" class="inline-alert">
        <i class="bi bi-exclamation-circle"></i>
        <span>{{ errorMessage }}</span>
      </div>

      <div class="table-responsive">
        <table class="table align-middle admin-table mb-0">
          <thead>
          <tr>
            <th>Mã</th>
            <th>Tên</th>
            <th>Giảm giá</th>
            <th>Đơn tối thiểu</th>
            <th>Lượt dùng</th>
            <th>Trạng thái</th>
            <th>Thời gian</th>
            <th>Thao tác</th>
          </tr>
          </thead>

          <tbody>
          <tr v-for="coupon in paginatedItems" :key="coupon.id">
            <td>
              <div class="code-cell">
                <strong>{{ coupon.code }}</strong>
                <small>{{ coupon.type === 'percentage' ? 'Theo %' : 'Theo số tiền' }}</small>
              </div>
            </td>
            <td>
              <div class="name-cell">
                <strong>{{ coupon.name }}</strong>
                <small>{{ coupon.description || 'Không có mô tả' }}</small>
              </div>
            </td>
            <td>{{ formatCouponValue(coupon) }}</td>
            <td>{{ formatMoney(coupon.min_order_amount || 0) }}</td>
            <td>
              <div class="usage-cell">
                <strong>{{ coupon.used_count || 0 }}</strong>
                <small>{{ coupon.usage_limit ? `/${coupon.usage_limit}` : 'Không giới hạn' }}</small>
              </div>
            </td>
            <td>
              <button
                  type="button"
                  class="status-pill"
                  :class="formatCouponBadgeClass(coupon)"
                  @click="handleToggleStatus(coupon)"
              >
                {{ formatCouponStatus(coupon) }}
              </button>
            </td>
            <td>
              <div class="time-cell">
                <strong>{{ formatDate(coupon.starts_at) || 'N/A' }}</strong>
                <small>{{ coupon.ends_at ? `Đến ${formatDate(coupon.ends_at)}` : 'Không giới hạn' }}</small>
              </div>
            </td>
            <td>
              <div class="action-group">
                <button type="button" class="action-btn action-edit" title="Chỉnh sửa" @click="openEditModal(coupon)">
                  <i class="bi bi-pencil"></i>
                </button>
                <button
                    type="button"
                    class="action-btn action-delete"
                    title="Xóa"
                    :disabled="deletingId === coupon.id"
                    @click="handleDelete(coupon)"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="!filteredCoupons.length">
            <td colspan="8">
              <div class="empty-state">
                <i class="bi bi-ticket-perforated"></i>
                <p>Không có coupon nào phù hợp với bộ lọc hiện tại.</p>
                <button type="button" class="secondary-action" @click="openCreateModal">Thêm coupon</button>
              </div>
            </td>
          </tr>
          </tbody>
        </table>
      </div>

      <ListPaginationControls
          :current-page="currentPage"
          :total-pages="totalPages"
          :page-size="pageSize"
          :total-items="filteredCoupons.length"
          :page-start="pageStart"
          :page-end="pageEnd"
          item-label="coupon"
          @update:currentPage="currentPage = $event"
          @update:pageSize="pageSize = $event"
      />
    </section>

    <Teleport to="body">
      <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
        <div class="modal-card">
          <div class="modal-header">
            <div>
              <p class="modal-kicker">{{ editingId ? 'Chỉnh sửa coupon' : 'Thêm coupon' }}</p>
              <h3>{{ editingId ? 'Cập nhật coupon' : 'Tạo coupon mới' }}</h3>
            </div>
            <button type="button" class="modal-close" @click="closeModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <form class="modal-form" @submit.prevent="submitForm">
            <div class="grid-2">
              <div class="field">
                <label>Mã coupon</label>
                <input v-model="form.code" type="text" class="control" placeholder="SALE2026" required />
              </div>
              <div class="field">
                <label>Tên coupon</label>
                <input v-model="form.name" type="text" class="control" placeholder="Giảm giá tháng 6" required />
              </div>
            </div>

            <div class="grid-2">
              <div class="field">
                <label>Kiểu giảm</label>
                <select v-model="form.type" class="control">
                  <option value="percentage">Giảm theo %</option>
                  <option value="fixed">Giảm theo số tiền</option>
                </select>
              </div>
              <div class="field">
                <label>Giá trị giảm</label>
                <input v-model="form.value" type="number" min="0" step="0.01" class="control" required />
              </div>
            </div>

            <div class="grid-3">
              <div class="field">
                <label>Giảm tối đa</label>
                <input v-model="form.max_discount" type="number" min="0" step="0.01" class="control" placeholder="Bỏ trống nếu không giới hạn" />
              </div>
              <div class="field">
                <label>Đơn tối thiểu</label>
                <input v-model="form.min_order_amount" type="number" min="0" step="0.01" class="control" />
              </div>
              <div class="field">
                <label>Giới hạn lượt dùng</label>
                <input v-model="form.usage_limit" type="number" min="1" class="control" placeholder="Không giới hạn" />
              </div>
            </div>

            <div class="grid-2">
              <div class="field">
                <label>Ngày bắt đầu</label>
                <input v-model="form.starts_at" type="datetime-local" class="control" />
              </div>
              <div class="field">
                <label>Ngày kết thúc</label>
                <input v-model="form.ends_at" type="datetime-local" class="control" />
              </div>
            </div>

            <div class="field">
              <label>Mô tả</label>
              <textarea v-model="form.description" class="control textarea" rows="4" placeholder="Nội dung mô tả coupon..." />
            </div>

            <label class="switch-row">
              <input v-model="form.is_active" type="checkbox" />
              <span>Kích hoạt coupon ngay</span>
            </label>

            <div class="modal-actions">
              <button type="button" class="secondary-action" @click="closeModal">Hủy</button>
              <button type="submit" class="primary-action" :disabled="saving">
                <i class="bi bi-check2"></i>
                {{ saving ? 'Đang lưu...' : 'Lưu coupon' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.admin-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-card,
.toolbar-card,
.table-card,
.state-card {
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
}

.hero-card {
  padding: 24px;
  background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 30%),
  linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.9fr);
  gap: 18px;
}

.eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 32px;
  font-weight: 900;
  line-height: 1.1;
}

.subtitle {
  max-width: 760px;
  margin: 10px 0 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.7;
}

.hero-actions {
  margin-top: 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

.primary-action,
.secondary-action {
  min-height: 44px;
  padding: 0 16px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
}

.primary-action {
  border: none;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
}

.secondary-action {
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.stat-card {
  min-height: 104px;
  padding: 18px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 14px;
}

.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  color: #ffffff;
  font-size: 22px;
}

.tone-blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
.tone-green { background: linear-gradient(135deg, #16a34a, #22c55e); }
.tone-orange { background: linear-gradient(135deg, #f59e0b, #fb923c); }
.tone-slate { background: linear-gradient(135deg, #475569, #64748b); }

.stat-card strong {
  display: block;
  color: #0f172a;
  font-size: 26px;
  font-weight: 900;
  line-height: 1;
}

.stat-card span {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 14px;
  font-weight: 700;
}

.toolbar-card {
  padding: 14px;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 12px;
  align-items: center;
}

.search-box {
  min-height: 48px;
  padding: 0 16px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 10px;
}

.search-box i {
  color: #64748b;
  font-size: 18px;
}

.search-box input,
.filter-select {
  width: 100%;
  border: 0;
  outline: none;
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
  background: transparent;
}

.filter-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.filter-select {
  min-height: 48px;
  padding: 0 16px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
}

.table-card {
  overflow: hidden;
}

.table-header {
  padding: 18px 20px;
  border-bottom: 1px solid #eef2f7;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.table-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
}

.table-header p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 13px;
}

.table-chip {
  min-height: 38px;
  padding: 0 14px;
  border-radius: 999px;
  background: #eff6ff;
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 800;
}

.inline-alert {
  padding: 12px 20px;
  border-bottom: 1px solid #fee2e2;
  background: #fff7f7;
  color: #b91c1c;
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 700;
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  min-width: 1240px;
}

.admin-table thead th {
  padding: 16px 20px;
  border-bottom: 1px solid #e5eaf3;
  color: #64748b;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}

.admin-table tbody td {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
  color: #0f172a;
  font-size: 14px;
  vertical-align: middle;
}

.code-cell,
.name-cell,
.usage-cell,
.time-cell {
  display: flex;
  flex-direction: column;
  gap: 3px;
}

.code-cell strong,
.name-cell strong,
.usage-cell strong,
.time-cell strong {
  font-weight: 800;
}

.code-cell small,
.name-cell small,
.usage-cell small,
.time-cell small {
  color: #64748b;
  font-size: 12px;
}

.status-pill {
  min-height: 34px;
  padding: 0 12px;
  border: 1px solid transparent;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 800;
}

.status-pill.active {
  background: #ecfdf5;
  color: #15803d;
  border-color: #bbf7d0;
}

.status-pill.inactive {
  background: #f8fafc;
  color: #475569;
  border-color: #e2e8f0;
}

.status-pill.expired {
  background: #fff7ed;
  color: #c2410c;
  border-color: #fed7aa;
}

.action-group {
  display: flex;
  justify-content: flex-start;
  gap: 8px;
}

.action-btn {
  width: 38px;
  height: 38px;
  border: none;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.action-edit {
  background: #f5f3ff;
  color: #7c3aed;
}

.action-delete {
  background: #fef2f2;
  color: #dc2626;
}

.action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.empty-state {
  padding: 48px 16px;
  text-align: center;
  color: #64748b;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
}

.empty-state i {
  font-size: 36px;
  color: #94a3b8;
}

.empty-state p {
  margin: 0;
  font-size: 14px;
  font-weight: 600;
}

.state-card {
  min-height: 260px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  color: #475569;
}

.grid-2,
.grid-3 {
  display: grid;
  gap: 12px;
}

.grid-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.grid-3 {
  grid-template-columns: repeat(3, minmax(0, 1fr));
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field label {
  color: #0f172a;
  font-size: 13px;
  font-weight: 800;
}

.control {
  min-height: 44px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #0f172a;
  font-size: 14px;
  outline: none;
}

.textarea {
  min-height: 110px;
  padding-top: 12px;
  resize: vertical;
}

.switch-row {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 14px;
  font-weight: 700;
  color: #0f172a;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1050;
  background: rgba(15, 23, 42, 0.45);
  display: grid;
  place-items: center;
  padding: 16px;
}

.modal-card {
  width: min(900px, 100%);
  max-height: calc(100vh - 32px);
  overflow: auto;
  border-radius: 22px;
  background: #ffffff;
  box-shadow: 0 24px 80px rgba(15, 23, 42, 0.22);
}

.modal-header {
  padding: 20px 20px 16px;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  border-bottom: 1px solid #eef2f7;
}

.modal-header h3 {
  margin: 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

.modal-close {
  width: 40px;
  height: 40px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #334155;
}

.modal-form {
  padding: 18px 20px 20px;
  display: grid;
  gap: 14px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 6px;
}

@media (max-width: 992px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .toolbar-card {
    grid-template-columns: 1fr;
  }

  .hero-stats {
    grid-template-columns: 1fr;
  }

  .grid-2,
  .grid-3 {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .hero-card,
  .toolbar-card,
  .table-header {
    padding: 16px;
  }

  .hero-copy h1 {
    font-size: 28px;
  }

  .modal-form {
    padding-left: 16px;
    padding-right: 16px;
  }

  .modal-actions {
    flex-direction: column-reverse;
  }
}
</style>
