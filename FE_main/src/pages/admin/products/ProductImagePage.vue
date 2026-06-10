<script setup>
import {computed, onMounted, reactive, ref, watch} from 'vue'
import {useRoute} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useProductStore} from '@/stores/productStore.js'
import {useProductVariantStore} from '@/stores/productVariantStore.js'
import {useProductImageStore} from '@/stores/productImageStore.js'
import {formatDate} from '@/utils/formatDate.js'
import ProductImageUpload from '@/components/product/ProductImageUpload.vue'

const route = useRoute()
const productStore = useProductStore()
const variantStore = useProductVariantStore()
const imageStore = useProductImageStore()

const {item: product, loading: productLoading} = storeToRefs(productStore)
const {items: variants} = storeToRefs(variantStore)
const {items: images, loading: imageLoading} = storeToRefs(imageStore)

const loadingError = ref('')
const formError = ref('')
const showModal = ref(false)
const saving = ref(false)
const deletingId = ref(null)
const editingImageId = ref(null)
const selectedVariantId = ref('')
const fieldErrors = reactive({})

const productId = computed(() => route.params.id)
const isActiveTab = (name) => route.name === name
const currentVariants = computed(() => {
  const list = Array.isArray(variants.value) ? variants.value : []
  return list
      .filter((variant) => String(variant?.product_id) === String(productId.value))
      .sort((left, right) => Number(left?.id ?? 0) - Number(right?.id ?? 0))
})

const currentVariant = computed(() => {
  return currentVariants.value.find((variant) => String(variant?.id) === String(selectedVariantId.value)) || null
})

const filteredImages = computed(() => {
  const list = Array.isArray(images.value) ? images.value : []
  return list
      .filter((image) => String(image?.product_variant_id ?? image?.productVariant?.id) === String(selectedVariantId.value))
      .sort((left, right) => Number(left?.sort_order ?? 0) - Number(right?.sort_order ?? 0))
})

const summary = computed(() => {
  return {
    variants: currentVariants.value.length,
    images: filteredImages.value.length,
  }
})

const form = reactive({
  product_variant_id: '',
  image_url: '',
  alt_text: '',
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
  form.product_variant_id = String(selectedVariantId.value || '')
  form.image_url = ''
  form.alt_text = ''
  form.sort_order = ''
  editingImageId.value = null
}

const loadData = async () => {
  loadingError.value = ''

  try {
    await Promise.all([
      productStore.fetchById(productId.value),
      variantStore.fetchAll(),
      imageStore.fetchAll(),
    ])

    const firstVariant = currentVariants.value[0]
    selectedVariantId.value = String(firstVariant?.id || '')
    resetForm()
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được dữ liệu hình ảnh.'
  }
}

watch(currentVariants, (nextVariants) => {
  if (!nextVariants.length) {
    selectedVariantId.value = ''
    return
  }

  if (!selectedVariantId.value || !nextVariants.some((variant) => String(variant.id) === String(selectedVariantId.value))) {
    selectedVariantId.value = String(nextVariants[0].id)
  }
}, {immediate: true})

watch(selectedVariantId, () => {
  if (!selectedVariantId.value) {
    return
  }

  form.product_variant_id = String(selectedVariantId.value)
}, {immediate: true})

const openCreateModal = () => {
  if (!selectedVariantId.value) {
    loadingError.value = 'Hãy chọn một biến thể trước khi thêm ảnh.'
    return
  }

  resetForm()
  formError.value = ''
  clearFieldErrors()
  showModal.value = true
}

const openEditModal = (image) => {
  resetForm()
  editingImageId.value = image?.id ?? null
  form.product_variant_id = String(image?.product_variant_id ?? image?.productVariant?.id ?? selectedVariantId.value ?? '')
  form.image_url = image?.image_url ?? ''
  form.alt_text = image?.alt_text ?? ''
  form.sort_order = image?.sort_order ?? ''
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
      product_variant_id: Number(form.product_variant_id || selectedVariantId.value),
      image_url: form.image_url.trim(),
      alt_text: form.alt_text.trim() || null,
      sort_order: form.sort_order === '' ? null : Number(form.sort_order),
    }

    if (editingImageId.value) {
      await imageStore.update(editingImageId.value, payload)
    } else {
      await imageStore.create(payload)
    }

    await imageStore.fetchAll()
    closeModal()
  } catch (error) {
    formError.value = error.response?.data?.message || 'Không lưu được ảnh biến thể.'
    setFieldErrors(error.response?.data?.errors)
  } finally {
    saving.value = false
  }
}

const handleDelete = async (image) => {
  if (!image || deletingId.value) {
    return
  }

  if (!window.confirm('Xóa ảnh biến thể này?')) {
    return
  }

  deletingId.value = image.id
  loadingError.value = ''

  try {
    await imageStore.remove(image.id)
    await imageStore.fetchAll()
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không xóa được ảnh biến thể.'
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
        <h1>Hình ảnh biến thể</h1>
        <p class="subtitle">
          Mỗi biến thể có thể có nhiều ảnh riêng. Chọn biến thể để quản lý album ảnh của nó.
        </p>
      </div>

      <div class="page-actions">
        <RouterLink :to="{ name: 'admin.products.show', params: { id: productId } }" class="secondary-action">
          <i class="bi bi-arrow-left"></i>
          Quay lại chi tiết
        </RouterLink>
        <button type="button" class="primary-action" @click="openCreateModal">
          <i class="bi bi-plus-lg"></i>
          Thêm ảnh
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

    <section v-if="loadingError && !currentVariants.length" class="notice-card error">
      <i class="bi bi-exclamation-triangle"></i>
      <span>{{ loadingError }}</span>
    </section>

    <section v-else-if="(productLoading || imageLoading) && !product" class="state-card">
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
            <strong>{{ summary.variants }}</strong>
            <span>Biến thể</span>
          </article>
          <article class="stat-card">
            <strong>{{ summary.images }}</strong>
            <span>Ảnh của biến thể</span>
          </article>
        </div>
      </section>

      <section class="selector-card">
        <div class="selector-copy">
          <h2>Chọn biến thể</h2>
          <p>Ảnh sẽ được quản lý theo biến thể đang chọn.</p>
        </div>

        <select v-model="selectedVariantId" class="control selector-control">
          <option value="">-- Chọn biến thể --</option>
          <option v-for="variant in currentVariants" :key="variant.id" :value="String(variant.id)">
            {{ variant.color }} · {{ variant.storage }} · {{ variant.ram }} · {{ variant.sku }}
          </option>
        </select>
      </section>

      <section class="table-card">
        <div class="table-header">
          <div>
            <h2>Album ảnh</h2>
            <p v-if="currentVariant">
              {{ currentVariant.color }} · {{ currentVariant.storage }} · {{ currentVariant.ram }}
            </p>
          </div>
          <div class="table-chip">
            <i class="bi bi-funnel"></i>
            <span>{{ filteredImages.length }} kết quả</span>
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
              <th>Ảnh</th>
              <th>Mô tả</th>
              <th>Thứ tự</th>
              <th>Cập nhật</th>
              <th class="text-end">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="image in filteredImages" :key="image.id">
              <td>
                <div class="image-thumb">
                  <img :src="image.image_url" :alt="image.alt_text || 'Ảnh biến thể'"/>
                </div>
              </td>
              <td>
                <div class="image-meta">
                  <strong>{{ image.alt_text || 'Không có mô tả' }}</strong>
                  <span>{{ image.image_url }}</span>
                </div>
              </td>
              <td>
                <span class="order-pill">{{ image.sort_order ?? 0 }}</span>
              </td>
              <td>{{ formatDate(image.updated_at || image.created_at) }}</td>
              <td>
                <div class="action-group">
                  <button type="button" class="action-btn action-edit" title="Chỉnh sửa" @click="openEditModal(image)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button
                      type="button"
                      class="action-btn action-delete"
                      title="Xóa"
                      :disabled="deletingId === image.id"
                      @click="handleDelete(image)"
                  >
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!selectedVariantId">
              <td colspan="5">
                <div class="empty-state">
                  <i class="bi bi-arrow-down-circle"></i>
                  <p>Hãy chọn một biến thể để xem và quản lý ảnh.</p>
                </div>
              </td>
            </tr>

            <tr v-else-if="!filteredImages.length">
              <td colspan="5">
                <div class="empty-state">
                  <i class="bi bi-images"></i>
                  <p>Biến thể này chưa có ảnh nào.</p>
                  <button type="button" class="secondary-action" @click="openCreateModal">Thêm ảnh</button>
                </div>
              </td>
            </tr>
            </tbody>
          </table>
        </div>
      </section>
    </template>

    <ProductImageUpload
      :visible="showModal"
      :title="editingImageId ? 'Chỉnh sửa ảnh' : 'Thêm ảnh'"
      :product-name="product?.name || ''"
      :form="form"
      :variants="currentVariants"
      :field-errors="fieldErrors"
      :form-error="formError"
      :saving="saving"
      @close="closeModal"
      @submit="handleSubmit"
    />
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
.hero-copy h2,
.selector-copy h2 {
  margin: 0;
  color: #0f172a;
  font-weight: 900;
}

.page-head h1 {
  font-size: 30px;
}

.subtitle,
.hero-slug,
.selector-copy p {
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
.notice-card,
.selector-card {
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

.selector-card {
  padding: 18px 20px;
  display: flex;
  align-items: end;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.selector-control {
  min-width: min(100%, 420px);
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
  min-width: 1020px;
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

.image-thumb {
  width: 64px;
  height: 64px;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #e2e8f0;
  background: #f8fafc;
}

.image-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.image-meta {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.image-meta strong {
  font-weight: 800;
}

.image-meta span {
  color: #64748b;
  font-size: 12px;
  word-break: break-all;
}

.order-pill {
  min-height: 30px;
  padding: 0 10px;
  border-radius: 999px;
  background: #f8fafc;
  color: #334155;
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 800;
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

.control.invalid {
  border-color: #fca5a5;
  box-shadow: 0 0 0 3px rgba(252, 165, 165, 0.12);
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

  .hero-card,
  .selector-card {
    flex-direction: column;
    align-items: stretch;
  }

  .hero-stats {
    grid-template-columns: 1fr;
  }

  .selector-control {
    min-width: 100%;
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
