<script setup>
import {computed, onMounted, reactive, ref, watch} from 'vue'
import {useRouter} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useProductStore} from '@/stores/productStore.js'
import {useBrandStore} from '@/stores/brandStore.js'
import {useCategoryStore} from '@/stores/categoryStore.js'
import ProductForm from '@/components/product/ProductForm.vue'

const router = useRouter()
const productStore = useProductStore()
const brandStore = useBrandStore()
const categoryStore = useCategoryStore()

const {loading: productLoading} = storeToRefs(productStore)
const {items: brands} = storeToRefs(brandStore)
const {items: categories} = storeToRefs(categoryStore)

const saving = ref(false)
const formError = ref('')
const fieldErrors = reactive({})

const form = reactive({
  brand_id: '',
  category_id: '',
  name: '',
  slug: '',
  thumbnail_url: '',
  thumbnail_file: null,
  thumbnail_preview_url: '',
  short_description: '',
  description: '',
  status: 'active',
})

const slugify = (value) => {
  return String(value ?? '')
      .trim()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '')
}

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

const submitLabel = computed(() => (saving.value ? 'Đang lưu...' : 'Tạo sản phẩm'))

const buildPayload = () => {
  const payload = {
    brand_id: Number(form.brand_id),
    category_id: Number(form.category_id),
    name: form.name.trim(),
    slug: form.slug.trim(),
    thumbnail_url: form.thumbnail_url.trim() || null,
    short_description: form.short_description.trim() || null,
    description: form.description.trim() || null,
    status: form.status,
  }

  if (!form.thumbnail_file) {
    return payload
  }

  const formData = new FormData()
  Object.entries(payload).forEach(([key, value]) => {
    if (value !== null && value !== undefined) {
      formData.append(key, value)
    }
  })
  formData.append('thumbnail_file', form.thumbnail_file)

  return formData
}

const handleThumbnailChange = (event) => {
  const file = event.target.files?.[0] || null
  form.thumbnail_file = file

  if (form.thumbnail_preview_url) {
    URL.revokeObjectURL(form.thumbnail_preview_url)
  }

  form.thumbnail_preview_url = file ? URL.createObjectURL(file) : ''
}

watch(
    () => form.name,
    (value) => {
      form.slug = slugify(value)
    }
)

const loadInitialData = async () => {
  try {
    await Promise.all([
      brandStore.fetchAll({status: 'active'}),
      categoryStore.fetchAll({status: 'active'}),
    ])
  } catch (error) {
    formError.value = error.response?.data?.message || 'Không tải được dữ liệu khởi tạo.'
  }
}

const handleSubmit = async () => {
  saving.value = true
  formError.value = ''
  clearFieldErrors()

  try {
    const response = await productStore.create(buildPayload())
    const created = response.data?.data ?? response.data ?? null

    if (created?.id) {
      await router.push({name: 'admin.products.show', params: {id: created.id}})
      return
    }

    await router.push({name: 'admin.products.index'})
  } catch (error) {
    formError.value = error.response?.data?.message || 'Không tạo được sản phẩm.'
    setFieldErrors(error.response?.data?.errors)
  } finally {
    saving.value = false
  }
}

onMounted(loadInitialData)
</script>

<template>
  <div class="admin-page">
    <section class="page-head">
      <div>
        <p class="eyebrow">Quản lý sản phẩm</p>
        <h1>Tạo sản phẩm mới</h1>
        <p class="subtitle">Nhập thông tin cốt lõi để đưa sản phẩm lên hệ thống admin.</p>
      </div>

      <div class="page-actions">
        <RouterLink to="/admin/products" class="secondary-action">
          <i class="bi bi-arrow-left"></i>
          Quay lại danh sách
        </RouterLink>
      </div>
    </section>

    <section v-if="formError && !productLoading" class="notice-card error">
      <i class="bi bi-exclamation-triangle"></i>
      <span>{{ formError }}</span>
    </section>

    <ProductForm
      :form="form"
      :brands="brands"
      :categories="categories"
      :field-errors="fieldErrors"
      :saving="saving"
      :loading="productLoading"
      :submit-label="submitLabel"
      cancel-to="/admin/products"
      @submit="handleSubmit"
      @thumbnail-change="handleThumbnailChange"
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

.eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.page-head h1 {
  margin: 0;
  color: #0f172a;
  font-size: 30px;
  font-weight: 900;
}

.subtitle {
  margin: 8px 0 0;
  color: #64748b;
  font-size: 14px;
}

.page-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

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
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}

.notice-card {
  min-height: 56px;
  padding: 0 16px;
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
  display: flex;
  align-items: center;
  gap: 10px;
  color: #b91c1c;
}

.notice-card.error {
  background: #fff7f7;
  border-color: #fecaca;
}

@media (max-width: 992px) {
  .page-head {
    flex-direction: column;
  }
}
</style>
