<script setup>
import {computed, onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import {useClientPagination} from '@/composables/useClientPagination.js'
import {useStockLogStore} from '@/stores/stockLogStore'
import {formatDate} from '@/utils/formatDate'

const router = useRouter()
const stockLogStore = useStockLogStore()

const pageLoading = ref(true)
const errorMessage = ref('')
const searchQuery = ref('')
const typeFilter = ref('all')

const typeLabels = {
  import: 'Nhập hàng',
  sale: 'Bán hàng',
  cancel_order: 'Hủy đơn',
  return: 'Trả hàng',
  adjustment: 'Điều chỉnh',
}

const typeClasses = {
  import: 'import',
  sale: 'sale',
  cancel_order: 'danger',
  return: 'return',
  adjustment: 'adjustment',
}

const stockLogs = computed(() => (Array.isArray(stockLogStore.items) ? stockLogStore.items : []))

const getVariant = (log) => {
  return log?.productVariant || log?.product_variant || log?.variant || {}
}

const getProduct = (log) => {
  const variant = getVariant(log)
  return variant?.product || log?.product || log?.product_data || {}
}

const getProductName = (log) => {
  const product = getProduct(log)
  const variant = getVariant(log)

  return (
      product?.name ||
      log?.product_name ||
      variant?.product_name ||
      variant?.product?.name ||
      'Không rõ'
  )
}

const getVariantSummary = (log) => {
  const variant = getVariant(log)
  const parts = [variant?.color, variant?.storage, variant?.ram]
      .map((value) => String(value ?? '').trim())
      .filter(Boolean)

  return parts.length ? parts.join(' · ') : 'Không màu'
}

const getVariantSku = (log) => {
  const variant = getVariant(log)
  return variant?.sku || log?.sku || 'N/A'
}

const filteredLogs = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return stockLogs.value.filter((log) => {
    const variant = getVariant(log)
    const product = getProduct(log)
    const user = log.user || {}
    const order = log.order || {}

    const matchesQuery = !query
        || [
          variant.sku,
          variant.color,
          variant.storage,
          product.name,
          user.name,
          user.email,
          order.order_code,
          log.note,
        ]
            .filter(Boolean)
            .some((value) => String(value).toLowerCase().includes(query))

    const matchesType = typeFilter.value === 'all' || log.type === typeFilter.value

    return matchesQuery && matchesType
  })
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems: paginatedLogs,
  pageStart,
  pageEnd,
} = useClientPagination(filteredLogs, {
  defaultPageSize: 5,
  pageSizeOptions: [5, 10],
})

const stats = computed(() => {
  const total = stockLogs.value.length
  const imports = stockLogs.value.filter((item) => item.type === 'import').length
  const sales = stockLogs.value.filter((item) => item.type === 'sale').length
  const adjustments = stockLogs.value.filter((item) => item.type === 'adjustment').length

  return [
    {label: 'Tổng log', value: total, icon: 'bi-list-check', color: 'blue'},
    {label: 'Nhập hàng', value: imports, icon: 'bi-box-arrow-in-down', color: 'green'},
    {label: 'Bán hàng', value: sales, icon: 'bi-bag-check', color: 'orange'},
    {label: 'Điều chỉnh', value: adjustments, icon: 'bi-sliders', color: 'slate'},
  ]
})

const loadPage = async () => {
  pageLoading.value = true
  errorMessage.value = ''

  try {
    await stockLogStore.fetchAll()
  } catch (error) {
    if (error.response?.status === 401) {
      await router.replace({name: 'login'})
      return
    }

    if (error.response?.status === 403) {
      await router.replace({name: 'forbidden'})
      return
    }

    errorMessage.value = error.response?.data?.message || 'Không tải được dữ liệu tồn kho.'
  } finally {
    pageLoading.value = false
  }
}

const typeLabel = (type) => typeLabels[type] || type || 'Không rõ'

onMounted(loadPage)
</script>

<template>
  <div class="admin-page">
    <div class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Quản lý tồn kho</p>
        <h1>Nhật ký tồn kho</h1>
        <p class="subtitle">Theo dõi mọi biến động số lượng theo sản phẩm, đơn hàng và người thao tác.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" :disabled="pageLoading" @click="loadPage">
            <i :class="pageLoading ? 'bi bi-arrow-repeat spin' : 'bi bi-arrow-clockwise'"></i>
            {{ pageLoading ? 'Đang tải' : 'Tải lại' }}
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <div v-for="stat in stats" :key="stat.label" class="stat-card">
          <div class="stat-icon" :class="stat.color">
            <i :class="`bi ${stat.icon}`"></i>
          </div>
          <div class="stat-content">
            <strong>{{ stat.value }}</strong>
            <span>{{ stat.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model.trim="searchQuery" type="text" placeholder="Tìm theo SKU, sản phẩm, người thao tác..."/>
      </div>

      <select v-model="typeFilter" class="filter-select">
        <option value="all">Tất cả loại</option>
        <option value="import">Nhập hàng</option>
        <option value="sale">Bán hàng</option>
        <option value="cancel_order">Hủy đơn</option>
        <option value="return">Trả hàng</option>
        <option value="adjustment">Điều chỉnh</option>
      </select>

      <div class="result-chip">
        <i class="bi bi-funnel"></i>
        {{ filteredLogs.length }} kết quả
      </div>
    </div>

    <p v-if="errorMessage" class="error-banner">{{ errorMessage }}</p>

    <div class="table-card">
      <div v-if="!filteredLogs.length" class="empty-state">
        <i class="bi bi-box-seam"></i>
        <p>Chưa có log tồn kho nào phù hợp bộ lọc.</p>
      </div>

      <div v-else class="table-wrap">
        <table class="data-table">
          <colgroup>
            <col class="col-product"/>
            <col class="col-user"/>
            <col class="col-order"/>
            <col class="col-type"/>
            <col class="col-before"/>
            <col class="col-change"/>
            <col class="col-after"/>
            <col class="col-time"/>
            <col class="col-note"/>
          </colgroup>

          <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>Người thao tác</th>
            <th>Đơn hàng</th>
            <th>Loại</th>
            <th>Tồn trước</th>
            <th>Thay đổi</th>
            <th>Tồn sau</th>
            <th>Thời gian</th>
            <th>Ghi chú</th>
          </tr>
          </thead>

          <tbody>
          <tr v-for="log in paginatedLogs" :key="log.id">
            <td>
              <strong>{{ getProductName(log) }}</strong>
              <div class="muted">
                {{ getVariantSku(log) }} ·
                {{ getVariantSummary(log) }}
              </div>
            </td>
            <td>
              <strong>{{ log.user?.name || 'Hệ thống' }}</strong>
              <div class="muted">{{ log.user?.email || 'Tự động' }}</div>
            </td>
            <td>
              <strong>{{ log.order?.order_code || 'Không có' }}</strong>
              <div class="muted">#{{ log.order_id || 'N/A' }}</div>
            </td>
            <td>
                <span class="type-pill" :class="typeClasses[log.type] || 'default'">
                  {{ typeLabel(log.type) }}
                </span>
            </td>
            <td>
              <strong>{{ log.quantity_before }}</strong>
            </td>
            <td>
              <strong :class="Number(log.quantity_change) >= 0 ? 'increase' : 'decrease'">
                {{ log.quantity_change > 0 ? `+${log.quantity_change}` : log.quantity_change }}
              </strong>
            </td>
            <td>
              <strong>{{ log.quantity_after }}</strong>
            </td>
            <td>
              <span>{{ formatDate(log.created_at) }}</span>
            </td>
            <td>
              <span class="note-text">{{ log.note || 'Không có ghi chú' }}</span>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ListPaginationControls
        v-if="!errorMessage"
        :current-page="currentPage"
        :total-pages="totalPages"
        :page-size="pageSize"
        :total-items="filteredLogs.length"
        :page-start="pageStart"
        :page-end="pageEnd"
        item-label="log tồn kho"
        @update:currentPage="currentPage = $event"
        @update:pageSize="pageSize = $event"
    />
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
.table-card {
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.hero-card {
  padding: 24px;
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
  gap: 18px;
  background: linear-gradient(135deg, #ffffff, #f4f8ff);
}

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 34px;
  font-weight: 900;
}

.subtitle {
  margin: 10px 0 0;
  color: #64748b;
  font-size: 15px;
  line-height: 1.6;
  max-width: 720px;
}

.hero-actions {
  margin-top: 18px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.primary-action {
  min-height: 44px;
  padding: 0 18px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  border: none;
  color: #ffffff;
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  box-shadow: 0 12px 26px rgba(37, 99, 235, 0.2);
}

.primary-action:disabled {
  opacity: 0.7;
  cursor: progress;
}

.spin {
  animation: spin 0.9s linear infinite;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  align-content: start;
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

.stat-icon.blue {
  background: linear-gradient(135deg, #2563eb, #3b82f6);
}

.stat-icon.green {
  background: linear-gradient(135deg, #16a34a, #22c55e);
}

.stat-icon.orange {
  background: linear-gradient(135deg, #f59e0b, #fb923c);
}

.stat-icon.slate {
  background: linear-gradient(135deg, #475569, #64748b);
}

.stat-content strong {
  display: block;
  color: #0f172a;
  font-size: 26px;
  font-weight: 900;
  line-height: 1;
}

.stat-content span {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 14px;
  font-weight: 700;
}

.toolbar-card {
  padding: 14px;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 240px auto;
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

.filter-select {
  min-height: 48px;
  padding: 0 16px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
}

.result-chip {
  min-height: 48px;
  padding: 0 16px;
  border-radius: 999px;
  background: #eef4ff;
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  white-space: nowrap;
}

.error-banner {
  margin: 0;
  padding: 14px 16px;
  border-radius: 14px;
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #b91c1c;
  font-weight: 700;
}

.state-card {
  min-height: 220px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
  display: grid;
  place-items: center;
  gap: 12px;
  color: #64748b;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.05);
}

.table-card {
  overflow: hidden;
}

.table-wrap {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  min-width: 1400px;
  border-collapse: collapse;
  table-layout: fixed;
}

.data-table th,
.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #edf2f7;
  text-align: left;
  vertical-align: middle;
}

.data-table th {
  color: #64748b;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}

.data-table td {
  color: #0f172a;
  font-size: 14px;
  font-weight: 600;
}

.muted {
  margin-top: 4px;
  color: #64748b;
  font-size: 12px;
  font-weight: 500;
}

.type-pill {
  display: inline-flex;
  align-items: center;
  min-height: 32px;
  padding: 0 12px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 800;
}

.type-pill.import {
  background: #ecfdf5;
  color: #15803d;
}

.type-pill.sale {
  background: #eff6ff;
  color: #2563eb;
}

.type-pill.danger {
  background: #fef2f2;
  color: #dc2626;
}

.type-pill.return {
  background: #f5f3ff;
  color: #7c3aed;
}

.type-pill.adjustment,
.type-pill.default {
  background: #fff7ed;
  color: #c2410c;
}

.increase {
  color: #15803d;
}

.decrease {
  color: #dc2626;
}

.note-text {
  color: #64748b;
  font-weight: 500;
}

.empty-state {
  min-height: 240px;
  display: grid;
  place-items: center;
  text-align: center;
  color: #64748b;
}

.empty-state i {
  margin-bottom: 10px;
  font-size: 34px;
  color: #2563eb;
}

.col-product {
  width: 22%;
}

.col-user {
  width: 14%;
}

.col-order {
  width: 12%;
}

.col-type {
  width: 10%;
}

.col-before {
  width: 9%;
}

.col-change {
  width: 9%;
}

.col-after {
  width: 9%;
}

.col-time {
  width: 13%;
}

.col-note {
  width: 12%;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

@media (max-width: 1199.98px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .toolbar-card {
    grid-template-columns: 1fr;
  }
}
</style>
