<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useProductStore } from '@/stores/productStore.js'
import { useProductVariantStore } from '@/stores/productVariantStore.js'
import { formatCurrency } from '@/utils/formatCurrency.js'
import { formatDate } from '@/utils/formatDate.js'

const route = useRoute()
const productStore = useProductStore()
const variantStore = useProductVariantStore()

const { item: product, loading: productLoading } = storeToRefs(productStore)

const loadingError = ref('')
const formError = ref('')
const showModal = ref(false)
const saving = ref(false)
const deletingId = ref(null)
const editingVariantId = ref(null)
const fieldErrors = reactive({})

const productId = computed(() => route.params.id)
const isActiveTab = (name) => route.name === name
const variantRows = computed(() => product.value?.productVariants || product.value?.product_variants || [])

const summary = computed(() => {
  const variants = variantRows.value
  const active = variants.filter((variant) => variant?.status === 'active').length
  const inactive = variants.length - active

  return {
    total: variants.length,
    active,
    inactive,
  }
})

const form = reactive({
  product_id: '',
  color: '',
  storage: '',
  ram: '',
  sku: '',
  import_price: '',
  price: '',
  sale_price: '',
  quantity: 0,
  status: 'active',
  description: '',
})

const clearFieldErrors = () => {
  Object.keys(fieldErrors).forEach((key) => {
    delete fieldErrors[key]
  })
}

const setFieldErrors = (errors = {}) => {
  clearFieldErrors()

  Object.entries(errors).forEach(([key, value]) => {
    fieldErrors[key] = Array.isArray(value) ? value[0] : value
  })
}

const resetForm = () => {
  form.product_id = String(productId.value || '')
  form.color = ''
  form.storage = ''
  form.ram = ''
  form.sku = ''
  form.import_price = ''
  form.price = ''
  form.sale_price = ''
  form.quantity = 0
  form.status = 'active'
  form.description = ''
  editingVariantId.value = null
}

const loadProduct = async () => {
  loadingError.value = ''

  try {
    await productStore.fetchById(productId.value)
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được dữ liệu sản phẩm.'
  }
}

const openCreateModal = () => {
  resetForm()
  formError.value = ''
  clearFieldErrors()
  showModal.value = true
}

const openEditModal = (variant) => {
  resetForm()
  editingVariantId.value = variant?.id ?? null
  form.product_id = String(variant?.product_id ?? productId.value ?? '')
  form.color = variant?.color ?? ''
  form.storage = variant?.storage ?? ''
  form.ram = variant?.ram ?? ''
  form.sku = variant?.sku ?? ''
  form.import_price = variant?.import_price ?? ''
  form.price = variant?.price ?? ''
  form.sale_price = variant?.sale_price ?? ''
  form.quantity = variant?.quantity ?? 0
  form.status = variant?.status ?? 'active'
  form.description = variant?.description ?? ''
  formError.value = ''
  clearFieldErrors()
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  formError.value = ''
  clearFieldErrors()
  resetForm()
}

const loadData = async () => {
  await loadProduct()
  resetForm()
}

const handleSubmit = async () => {
  saving.value = true
  formError.value = ''
  clearFieldErrors()

  try {
    const payload = {
      product_id: Number(form.product_id || productId.value),
      color: form.color.trim(),
      storage: form.storage.trim(),
      ram: form.ram.trim(),
      sku: form.sku.trim(),
      import_price: form.import_price === '' ? null : Number(form.import_price),
      price: Number(form.price),
      sale_price: form.sale_price === '' ? null : Number(form.sale_price),
      quantity: Number(form.quantity || 0),
      status: form.status,
      description: form.description.trim() || null,
    }

    if (editingVariantId.value) {
      await variantStore.update(editingVariantId.value, payload)
    } else {
      await variantStore.create(payload)
    }

    await loadProduct()
    closeModal()
  } catch (error) {
    formError.value = error.response?.data?.message || 'Không lưu được biến thể.'
    setFieldErrors(error.response?.data?.errors)
  } finally {
    saving.value = false
  }
}

const handleDelete = async (variant) => {
  if (!variant || deletingId.value) return

  if (!window.confirm(`Xóa biến thể SKU "${variant.sku}"?`)) {
    return
  }

  deletingId.value = variant.id
  loadingError.value = ''

  try {
    await variantStore.remove(variant.id)
    await loadProduct()
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không xóa được biến thể.'
  } finally {
    deletingId.value = null
  }
}

const handleToggleStatus = async (variant) => {
  const nextStatus = variant?.status === 'active' ? 'inactive' : 'active'
  const previousStatus = variant?.status

  variant.status = nextStatus
  loadingError.value = ''

  try {
    await variantStore.update(variant.id, { status: nextStatus })
  } catch (error) {
    variant.status = previousStatus
    loadingError.value = error.response?.data?.message || 'Không cập nhật được trạng thái biến thể.'
  }
}

const formatMoney = (value) => {
  return formatCurrency(value) || '---'
}

onMounted(loadData)
</script>

<template>
  <div class="admin-page">
    <section class="page-head">
      <div>
        <p class="eyebrow">Quản lý sản phẩm</p>
        <h1>Biến thể sản phẩm</h1>
        <p class="subtitle">
          Quản lý giá, tồn kho, màu sắc, dung lượng và trạng thái bán của từng biến thể.
        </p>
      </div>

      <div class="page-actions">
        <RouterLink :to="{ name: 'admin.products.show', params: { id: productId } }" class="secondary-action">
          <i class="bi bi-arrow-left"></i>
          Quay lại chi tiết
        </RouterLink>
        <button type="button" class="primary-action" @click="openCreateModal">
          <i class="bi bi-plus-lg"></i>
          Thêm biến thể
        </button>
      </div>
    </section>

    <section class="page-tabs">
      <RouterLink :to="{ name: 'admin.products.show', params: { id: productId } }" class="tab-link" :class="{ active: isActiveTab('admin.products.show') }">
        Thông tin
      </RouterLink>
      <RouterLink :to="{ name: 'admin.products.variants', params: { id: productId } }" class="tab-link" :class="{ active: isActiveTab('admin.products.variants') }">
        Biến thể
      </RouterLink>
      <RouterLink :to="{ name: 'admin.products.specifications', params: { id: productId } }" class="tab-link" :class="{ active: isActiveTab('admin.products.specifications') }">
        Thông số
      </RouterLink>
      <RouterLink :to="{ name: 'admin.products.images', params: { id: productId } }" class="tab-link" :class="{ active: isActiveTab('admin.products.images') }">
        Hình ảnh
      </RouterLink>
    </section>

    <section v-if="loadingError && !variantRows.length" class="notice-card error">
      <i class="bi bi-exclamation-triangle"></i>
      <span>{{ loadingError }}</span>
    </section>

    <section v-else-if="productLoading && !product" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải dữ liệu sản phẩm...</p>
    </section>

    <template v-else>
      <section class="hero-card">
        <div class="hero-copy">
          <p class="hero-kicker">Sản phẩm hiện tại</p>
          <h2>{{ product?.name }}</h2>
          <p class="hero-slug">{{ product?.slug }}</p>
        </div>

        <div class="hero-stats">
          <article class="stat-card">
            <strong>{{ summary.total }}</strong>
            <span>Tổng biến thể</span>
          </article>
          <article class="stat-card">
            <strong>{{ summary.active }}</strong>
            <span>Đang hoạt động</span>
          </article>
          <article class="stat-card">
            <strong>{{ summary.inactive }}</strong>
            <span>Tạm ẩn</span>
          </article>
        </div>
      </section>

      <section class="table-card">
        <div class="table-header">
          <div>
            <h2>Danh sách biến thể</h2>
            <p>Mỗi biến thể giữ giá riêng và tồn kho riêng.</p>
          </div>
          <div class="table-chip">
            <i class="bi bi-funnel"></i>
            <span>{{ variantRows.length }} kết quả</span>
          </div>
        </div>

        <div v-if="loadingError" class="inline-alert">
          <i class="bi bi-exclamation-circle"></i>
          <span>{{ loadingError }}</span>
        </div>

        <div class="table-responsive">
          <table class="table align-middle admin-table mb-0">
            <thead>
              <tr>
                <th>Biến thể</th>
                <th>SKU</th>
                <th>Giá</th>
                <th>Sale</th>
                <th>Tồn kho</th>
                <th>Trạng thái</th>
                <th>Cập nhật</th>
                <th class="text-end">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="variant in variantRows" :key="variant.id">
                <td>
                  <div class="variant-meta">
                    <strong>{{ variant.color }}</strong>
                    <span>{{ variant.storage }} · {{ variant.ram }}</span>
                  </div>
                </td>
                <td>
                  <span class="sku-pill">{{ variant.sku }}</span>
                </td>
                <td>{{ formatMoney(variant.price) }}</td>
                <td>{{ formatMoney(variant.sale_price) }}</td>
                <td>
                  <span class="stock-pill" :class="{ low: Number(variant.quantity || 0) <= 5 }">
                    {{ variant.quantity || 0 }}
                  </span>
                </td>
                <td>
                  <button
                    type="button"
                    class="status-pill"
                    :class="variant.status === 'active' ? 'is-active' : 'is-inactive'"
                    @click="handleToggleStatus(variant)"
                    :title="variant.status === 'active' ? 'Hoạt động' : 'Tạm ẩn'"
                    :aria-label="variant.status === 'active' ? 'Hoạt động' : 'Tạm ẩn'"
                  >
                    <i :class="variant.status === 'active' ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
                  </button>
                </td>
                <td>{{ formatDate(variant.updated_at || variant.created_at) }}</td>
                <td>
                  <div class="action-group">
                    <button type="button" class="action-btn action-edit" title="Chỉnh sửa" @click="openEditModal(variant)">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button
                      type="button"
                      class="action-btn action-delete"
                      title="Xóa"
                      :disabled="deletingId === variant.id"
                      @click="handleDelete(variant)"
                    >
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="!variantRows.length">
                <td colspan="8">
                  <div class="empty-state">
                    <i class="bi bi-layers"></i>
                    <p>Chưa có biến thể nào cho sản phẩm này.</p>
                    <button type="button" class="secondary-action" @click="openCreateModal">Thêm biến thể</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>

    <Teleport to="body">
      <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
        <div class="modal-card">
          <div class="modal-header">
            <div>
              <p class="modal-kicker">{{ editingVariantId ? 'Chỉnh sửa biến thể' : 'Thêm biến thể' }}</p>
              <h3>{{ product?.name }}</h3>
            </div>
            <button type="button" class="modal-close" @click="closeModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div v-if="formError" class="modal-alert">{{ formError }}</div>

          <form class="modal-form" @submit.prevent="handleSubmit">
            <div class="grid-2">
              <div class="field">
                <label>Màu sắc</label>
                <input v-model="form.color" type="text" class="control" :class="{ invalid: fieldErrors.color }" />
                <small v-if="fieldErrors.color" class="field-error">{{ fieldErrors.color }}</small>
              </div>
              <div class="field">
                <label>Dung lượng</label>
                <input v-model="form.storage" type="text" class="control" :class="{ invalid: fieldErrors.storage }" placeholder="128GB" />
                <small v-if="fieldErrors.storage" class="field-error">{{ fieldErrors.storage }}</small>
              </div>
            </div>

            <div class="grid-2">
              <div class="field">
                <label>RAM</label>
                <input v-model="form.ram" type="text" class="control" :class="{ invalid: fieldErrors.ram }" placeholder="8GB" />
                <small v-if="fieldErrors.ram" class="field-error">{{ fieldErrors.ram }}</small>
              </div>
              <div class="field">
                <label>SKU</label>
                <input v-model="form.sku" type="text" class="control" :class="{ invalid: fieldErrors.sku }" />
                <small v-if="fieldErrors.sku" class="field-error">{{ fieldErrors.sku }}</small>
              </div>
            </div>

            <div class="grid-3">
              <div class="field">
                <label>Giá nhập</label>
                <input v-model="form.import_price" type="number" min="0" class="control" :class="{ invalid: fieldErrors.import_price }" />
                <small v-if="fieldErrors.import_price" class="field-error">{{ fieldErrors.import_price }}</small>
              </div>
              <div class="field">
                <label>Giá bán</label>
                <input v-model="form.price" type="number" min="0" class="control" :class="{ invalid: fieldErrors.price }" required />
                <small v-if="fieldErrors.price" class="field-error">{{ fieldErrors.price }}</small>
              </div>
              <div class="field">
                <label>Giá sale</label>
                <input v-model="form.sale_price" type="number" min="0" class="control" :class="{ invalid: fieldErrors.sale_price }" />
                <small v-if="fieldErrors.sale_price" class="field-error">{{ fieldErrors.sale_price }}</small>
              </div>
            </div>

            <div class="grid-2">
              <div class="field">
                <label>Số lượng</label>
                <input v-model="form.quantity" type="number" min="0" class="control" :class="{ invalid: fieldErrors.quantity }" />
                <small v-if="fieldErrors.quantity" class="field-error">{{ fieldErrors.quantity }}</small>
              </div>
              <div class="field">
                <label>Trạng thái</label>
                <select v-model="form.status" class="control" :class="{ invalid: fieldErrors.status }">
                  <option value="active">Đang hoạt động</option>
                  <option value="inactive">Tạm ẩn</option>
                </select>
                <small v-if="fieldErrors.status" class="field-error">{{ fieldErrors.status }}</small>
              </div>
            </div>

            <div class="field">
              <label>Mô tả</label>
              <textarea v-model="form.description" class="control textarea" rows="4" />
            </div>

            <div class="modal-actions">
              <button type="button" class="secondary-action" @click="closeModal">Hủy</button>
              <button type="submit" class="primary-action" :disabled="saving">
                <i class="bi bi-check2"></i>
                {{ saving ? 'Đang lưu...' : 'Lưu biến thể' }}
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

.page-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.eyebrow,
.hero-kicker,
.modal-kicker {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.page-head h1,
.hero-copy h2 {
  margin: 0;
  color: #0f172a;
  font-weight: 900;
}

.page-head h1 {
  font-size: 30px;
}

.subtitle,
.hero-slug {
  margin: 8px 0 0;
  color: #64748b;
  font-size: 14px;
}

.page-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.page-tabs {
  padding: 8px;
  border: 1px solid #e5eaf3;
  border-radius: 16px;
  background: #ffffff;
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.04);
}

.tab-link {
  min-height: 40px;
  padding: 0 14px;
  border-radius: 12px;
  background: #f8fafc;
  color: #475569;
  display: inline-flex;
  align-items: center;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
}

.tab-link.active {
  background: #e0efff;
  color: #2563eb;
}

.secondary-action,
.primary-action {
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

.secondary-action {
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}

.primary-action {
  border: none;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
}

.hero-card,
.table-card,
.state-card,
.notice-card {
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
}

.hero-card {
  padding: 20px 22px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(3, minmax(110px, 1fr));
  gap: 12px;
}

.stat-card {
  min-width: 110px;
  padding: 14px 16px;
  border-radius: 16px;
  background: #f8fbff;
}

.stat-card strong {
  display: block;
  color: #0f172a;
  font-size: 24px;
  font-weight: 900;
}

.stat-card span {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
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
  min-width: 1180px;
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

.variant-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.variant-meta strong {
  font-weight: 800;
}

.variant-meta span {
  color: #64748b;
  font-size: 12px;
}

.sku-pill,
.stock-pill {
  min-height: 30px;
  padding: 0 10px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 800;
}

.sku-pill {
  background: #f8fafc;
  color: #334155;
}

.stock-pill {
  background: #ecfdf5;
  color: #15803d;
}

.stock-pill.low {
  background: #fff7ed;
  color: #c2410c;
}

.status-pill {
  width: 42px;
  height: 34px;
  padding: 0;
  border: 1px solid transparent;
  border-radius: 999px;
  position: relative;
  overflow: hidden;
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  font-weight: 800;
  transition: 0.2s ease;
}

.status-pill i {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  font-size: 16px;
  line-height: 1;
}

.status-pill.is-active {
  background: #ecfdf5;
  color: #15803d;
  border-color: #bbf7d0;
}

.status-pill.is-inactive {
  background: #fff7ed;
  color: #c2410c;
  border-color: #fed7aa;
}

.action-group {
  display: flex;
  justify-content: flex-end;
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

.notice-card {
  min-height: 56px;
  padding: 0 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  color: #b91c1c;
}

.notice-card.error {
  background: #fff7f7;
  border-color: #fecaca;
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

.control.invalid {
  border-color: #fca5a5;
  box-shadow: 0 0 0 3px rgba(252, 165, 165, 0.12);
}

.field-error {
  color: #dc2626;
  font-size: 12px;
  font-weight: 700;
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

.modal-alert {
  margin: 16px 20px 0;
  padding: 12px 14px;
  border: 1px solid #fecaca;
  border-radius: 14px;
  background: #fff7f7;
  color: #b91c1c;
  font-size: 13px;
  font-weight: 700;
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
  .page-head {
    flex-direction: column;
  }

  .hero-card {
    flex-direction: column;
    align-items: stretch;
  }

  .hero-stats {
    grid-template-columns: 1fr;
  }

  .page-tabs {
    padding: 6px;
  }

  .tab-link {
    flex: 1 1 calc(50% - 4px);
    justify-content: center;
  }

  .grid-2,
  .grid-3 {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .secondary-action,
  .primary-action {
    width: 100%;
    justify-content: center;
  }

  .modal-header,
  .modal-form {
    padding-left: 16px;
    padding-right: 16px;
  }

  .modal-actions {
    flex-direction: column-reverse;
  }
}
</style>
