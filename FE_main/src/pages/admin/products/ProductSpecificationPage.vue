<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import {useRoute} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useProductStore} from '@/stores/productStore.js'
import {useProductSpecificationStore} from '@/stores/productSpecificationStore.js'
import {formatDate} from '@/utils/formatDate.js'
import ProductSpecificationForm from '@/components/product/ProductSpecificationForm.vue'

const route = useRoute()
const productStore = useProductStore()
const specificationStore = useProductSpecificationStore()

const {item: product, loading: productLoading} = storeToRefs(productStore)
const {items: specifications, loading: specificationLoading} = storeToRefs(specificationStore)

const loadingError = ref('')
const formError = ref('')
const showModal = ref(false)
const saving = ref(false)
const deletingId = ref(null)
const editingSpecificationId = ref(null)
const fieldErrors = reactive({})

const productId = computed(() => route.params.id)
const isActiveTab = (name) => route.name === name
const filteredSpecifications = computed(() => {
  const list = Array.isArray(specifications.value) ? specifications.value : []
  return list
      .filter((spec) => String(spec?.product_id) === String(productId.value))
      .sort((left, right) => Number(left?.sort_order ?? 0) - Number(right?.sort_order ?? 0))
})

const summary = computed(() => {
  const specs = filteredSpecifications.value
  return {
    total: specs.length,
    firstLabel: specs[0]?.spec_name || 'Chưa có thông số',
  }
})

const form = reactive({
  product_id: '',
  spec_name: '',
  spec_value: '',
  sort_order: '',
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
  form.spec_name = ''
  form.spec_value = ''
  form.sort_order = ''
  editingSpecificationId.value = null
}

const loadData = async () => {
  loadingError.value = ''

  try {
    await Promise.all([
      productStore.fetchById(productId.value),
      specificationStore.fetchAll(),
    ])
    resetForm()
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được dữ liệu thông số.'
  }
}

const openCreateModal = () => {
  resetForm()
  formError.value = ''
  clearFieldErrors()
  showModal.value = true
}

const openEditModal = (specification) => {
  resetForm()
  editingSpecificationId.value = specification?.id ?? null
  form.product_id = String(specification?.product_id ?? productId.value ?? '')
  form.spec_name = specification?.spec_name ?? ''
  form.spec_value = specification?.spec_value ?? ''
  form.sort_order = specification?.sort_order ?? ''
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

const handleSubmit = async () => {
  saving.value = true
  formError.value = ''
  clearFieldErrors()

  try {
    const payload = {
      product_id: Number(form.product_id || productId.value),
      spec_name: form.spec_name.trim(),
      spec_value: form.spec_value.trim() || null,
      sort_order: form.sort_order === '' ? null : Number(form.sort_order),
    }

    if (editingSpecificationId.value) {
      await specificationStore.update(editingSpecificationId.value, payload)
    } else {
      await specificationStore.create(payload)
    }

    await specificationStore.fetchAll()
    closeModal()
  } catch (error) {
    formError.value = error.response?.data?.message || 'Không lưu được thông số.'
    setFieldErrors(error.response?.data?.errors)
  } finally {
    saving.value = false
  }
}

const handleDelete = async (specification) => {
  if (!specification || deletingId.value) {
    return
  }

  if (!window.confirm(`Xóa thông số "${specification.spec_name}"?`)) {
    return
  }

  deletingId.value = specification.id
  loadingError.value = ''

  try {
    await specificationStore.remove(specification.id)
    await specificationStore.fetchAll()
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không xóa được thông số.'
  } finally {
    deletingId.value = null
  }
}

onMounted(loadData)
</script>

<template>
  <div class="admin-page">
    <section class="page-head">
      <div>
        <p class="eyebrow">Quản lý sản phẩm</p>
        <h1>Thông số sản phẩm</h1>
        <p class="subtitle">
          Quản lý các thông số hiển thị trên trang sản phẩm, ví dụ pin, chip, màn hình, camera, chất liệu.
        </p>
      </div>

      <div class="page-actions">
        <RouterLink :to="{ name: 'admin.products.show', params: { id: productId } }" class="secondary-action">
          <i class="bi bi-arrow-left"></i>
          Quay lại chi tiết
        </RouterLink>
        <button type="button" class="primary-action" @click="openCreateModal">
          <i class="bi bi-plus-lg"></i>
          Thêm thông số
        </button>
      </div>
    </section>

    <section class="page-tabs">
      <RouterLink :to="{ name: 'admin.products.show', params: { id: productId } }" class="tab-link"
                  :class="{ active: isActiveTab('admin.products.show') }">Thông tin
      </RouterLink>
      <RouterLink :to="{ name: 'admin.products.variants', params: { id: productId } }" class="tab-link"
                  :class="{ active: isActiveTab('admin.products.variants') }">Biến thể
      </RouterLink>
      <RouterLink :to="{ name: 'admin.products.specifications', params: { id: productId } }" class="tab-link"
                  :class="{ active: isActiveTab('admin.products.specifications') }">Thông số
      </RouterLink>
      <RouterLink :to="{ name: 'admin.products.images', params: { id: productId } }" class="tab-link"
                  :class="{ active: isActiveTab('admin.products.images') }">Hình ảnh
      </RouterLink>
    </section>

    <section v-if="loadingError && !filteredSpecifications.length" class="notice-card error">
      <i class="bi bi-exclamation-triangle"></i>
      <span>{{ loadingError }}</span>
    </section>

    <section v-else-if="(productLoading || specificationLoading) && !product" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải dữ liệu...</p>
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
            <span>Tổng thông số</span>
          </article>
          <article class="stat-card">
            <strong>{{ summary.firstLabel }}</strong>
            <span>Thông số đầu tiên</span>
          </article>
        </div>
      </section>

      <section class="table-card">
        <div class="table-header">
          <div>
            <h2>Danh sách thông số</h2>
            <p>Hiển thị theo thứ tự bạn đã cấu hình.</p>
          </div>
          <div class="table-chip">
            <i class="bi bi-funnel"></i>
            <span>{{ filteredSpecifications.length }} kết quả</span>
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
              <th>Thông số</th>
              <th>Giá trị</th>
              <th>Thứ tự</th>
              <th>Cập nhật</th>
              <th class="text-end">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="spec in filteredSpecifications" :key="spec.id">
              <td>
                <div class="spec-meta">
                  <strong>{{ spec.spec_name }}</strong>
                  <span>Mã #{{ spec.id }}</span>
                </div>
              </td>
              <td>
                <span class="value-pill">{{ spec.spec_value || '—' }}</span>
              </td>
              <td>
                <span class="order-pill">{{ spec.sort_order ?? 0 }}</span>
              </td>
              <td>{{ formatDate(spec.updated_at || spec.created_at) }}</td>
              <td>
                <div class="action-group">
                  <button type="button" class="action-btn action-edit" title="Chỉnh sửa" @click="openEditModal(spec)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button
                      type="button"
                      class="action-btn action-delete"
                      title="Xóa"
                      :disabled="deletingId === spec.id"
                      @click="handleDelete(spec)"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!filteredSpecifications.length">
              <td colspan="5">
                <div class="empty-state">
                  <i class="bi bi-sliders"></i>
                  <p>Chưa có thông số nào cho sản phẩm này.</p>
                  <button type="button" class="secondary-action" @click="openCreateModal">Thêm thông số</button>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>

    <ProductSpecificationForm
      :visible="showModal"
      :title="editingSpecificationId ? 'Chỉnh sửa thông số' : 'Thêm thông số'"
      :product-name="product?.name || ''"
      :form="form"
      :field-errors="fieldErrors"
      :form-error="formError"
      :saving="saving"
      @close="closeModal"
      @submit="handleSubmit"
    />

    <Teleport v-if="false" to="body">
      <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
        <div class="modal-card">
          <div class="modal-header">
            <div>
              <p class="modal-kicker">{{ editingSpecificationId ? 'Chỉnh sửa thông số' : 'Thêm thông số' }}</p>
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
                <label>Tên thông số</label>
                <input v-model="form.spec_name" type="text" class="control"
                       :class="{ invalid: fieldErrors.spec_name }"/>
                <small v-if="fieldErrors.spec_name" class="field-error">{{ fieldErrors.spec_name }}</small>
              </div>
              <div class="field">
                <label>Thứ tự hiển thị</label>
                <input v-model="form.sort_order" type="number" min="0" class="control"
                       :class="{ invalid: fieldErrors.sort_order }"/>
                <small v-if="fieldErrors.sort_order" class="field-error">{{ fieldErrors.sort_order }}</small>
              </div>
            </div>

            <div class="field">
              <label>Giá trị</label>
              <textarea v-model="form.spec_value" class="control textarea" rows="4"/>
            </div>

            <div class="modal-actions">
              <button type="button" class="secondary-action" @click="closeModal">Hủy</button>
              <button type="submit" class="primary-action" :disabled="saving">
                <i class="bi bi-check2"></i>
                {{ saving ? 'Đang lưu...' : 'Lưu thông số' }}
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
  grid-template-columns: repeat(2, minmax(120px, 1fr));
  gap: 12px;
}

.stat-card {
  min-width: 120px;
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
  min-width: 1040px;
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

.spec-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.spec-meta strong {
  font-weight: 800;
}

.spec-meta span {
  color: #64748b;
  font-size: 12px;
}

.value-pill,
.order-pill {
  min-height: 30px;
  padding: 0 10px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 800;
}

.value-pill {
  background: #eff6ff;
  color: #2563eb;
}

.order-pill {
  background: #f8fafc;
  color: #334155;
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

.grid-2 {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
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
  width: min(720px, 100%);
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

  .grid-2 {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .secondary-action,
  .primary-action {
    width: 100%;
    justify-content: center;
  }

  .page-tabs {
    padding: 6px;
  }

  .tab-link {
    flex: 1 1 calc(50% - 4px);
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
