<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import {newsService} from '@/services/newsService'
import {formatDate} from '@/utils/formatDate'
import {useNotificationStore} from '@/stores/notificationStore.js'
import BaseModal from '@/components/common/BaseModal.vue'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import {useClientPagination} from '@/composables/useClientPagination.js'

const loading = ref(true)
const errorMessage = ref('')
const search = ref('')
const statusFilter = ref('all')
const categoryFilter = ref('all')
const showModal = ref(false)
const editingId = ref(null)
const saving = ref(false)
const deletingId = ref(null)
const formError = ref('')
const fieldErrors = reactive({})

const posts = ref([])
const categories = ref([])

const form = reactive({
  news_category_id: '',
  title: '',
  excerpt: '',
  content: '',
  featured_image_url: '',
  status: 'draft',
  is_featured: false,
  reading_minutes: 3,
})

const normalize = (value) => String(value ?? '').trim().toLowerCase()

const displayPosts = computed(() => Array.isArray(posts.value) ? posts.value : [])
const displayCategories = computed(() => Array.isArray(categories.value) ? categories.value : [])

const filteredPosts = computed(() => {
  const query = normalize(search.value)

  return displayPosts.value.filter((post) => {
    const categorySlug = String(post?.category?.slug ?? '')
    const matchesStatus = statusFilter.value === 'all' || post?.status === statusFilter.value
    const matchesCategory = categoryFilter.value === 'all' || categorySlug === categoryFilter.value
    const matchesQuery =
        !query ||
        [post?.title, post?.excerpt, post?.category?.name]
            .filter(Boolean)
            .some((field) => normalize(field).includes(query))

    return matchesStatus && matchesCategory && matchesQuery
  })
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems,
  pageStart,
  pageEnd,
} = useClientPagination(filteredPosts, {
  defaultPageSize: 6,
  pageSizeOptions: [6, 12, 18],
})

const stats = computed(() => {
  const total = displayPosts.value.length
  const published = displayPosts.value.filter((post) => post?.status === 'published').length
  const featured = displayPosts.value.filter((post) => Boolean(post?.is_featured)).length
  const drafts = total - published

  return {total, published, featured, drafts}
})

const categoryOptions = computed(() => displayCategories.value.filter((category) => category?.status === 'active'))

const clearFieldErrors = () => {
  Object.keys(fieldErrors).forEach((key) => delete fieldErrors[key])
}

const setFieldErrors = (errors = {}) => {
  clearFieldErrors()

  Object.entries(errors).forEach(([key, value]) => {
    fieldErrors[key] = Array.isArray(value) ? value[0] : value
  })
}

const loadData = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const [postsResponse, categoriesResponse] = await Promise.all([
      newsService.getAdminPosts(),
      newsService.getAdminCategories(),
    ])

    const postsPayload = postsResponse.data?.data ?? postsResponse.data ?? []
    const categoriesPayload = categoriesResponse.data?.data ?? categoriesResponse.data ?? []

    posts.value = Array.isArray(postsPayload) ? postsPayload : []
    categories.value = Array.isArray(categoriesPayload) ? categoriesPayload : []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không tải được bài viết.'
  } finally {
    loading.value = false
  }
}

const resetForm = () => {
  form.news_category_id = ''
  form.title = ''
  form.excerpt = ''
  form.content = ''
  form.featured_image_url = ''
  form.status = 'draft'
  form.is_featured = false
  form.reading_minutes = 3
  editingId.value = null
}

const openCreateModal = () => {
  resetForm()
  if (categoryOptions.value[0]) {
    form.news_category_id = String(categoryOptions.value[0].id)
  }
  formError.value = ''
  clearFieldErrors()
  showModal.value = true
}

const openEditModal = (post) => {
  editingId.value = post?.id ?? null
  form.news_category_id = String(post?.news_category_id ?? post?.category?.id ?? '')
  form.title = post?.title || ''
  form.excerpt = post?.excerpt || ''
  form.content = post?.content || ''
  form.featured_image_url = post?.featured_image_url || ''
  form.status = post?.status || 'draft'
  form.is_featured = Boolean(post?.is_featured)
  form.reading_minutes = Number(post?.reading_minutes || 3)
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

const submitForm = async () => {
  saving.value = true
  formError.value = ''
  clearFieldErrors()

  try {
    const payload = {
      news_category_id: form.news_category_id === '' ? null : Number(form.news_category_id),
      title: form.title.trim(),
      excerpt: form.excerpt.trim(),
      content: form.content.trim(),
      featured_image_url: form.featured_image_url.trim() || null,
      status: form.status,
      is_featured: Boolean(form.is_featured),
      reading_minutes: Number(form.reading_minutes || 3),
    }

    if (editingId.value) {
      await newsService.updatePost(editingId.value, payload)
      useNotificationStore().success('Đã sửa bài viết.')
    } else {
      await newsService.createPost(payload)
      useNotificationStore().success('Đã thêm bài viết.')
    }

    await loadData()
    closeModal()
  } catch (error) {
    formError.value = error.response?.data?.message || 'Không lưu được bài viết.'
    setFieldErrors(error.response?.data?.errors)
  } finally {
    saving.value = false
  }
}

const handleToggleStatus = async (post) => {
  const nextStatus = post?.status === 'published' ? 'draft' : 'published'
  const previousStatus = post?.status

  post.status = nextStatus

  try {
    await newsService.updatePost(post.id, {
      news_category_id: post?.news_category_id ?? post?.category?.id ?? null,
      title: post.title,
      excerpt: post.excerpt,
      content: post.content,
      featured_image_url: post.featured_image_url,
      status: nextStatus,
      is_featured: Boolean(post.is_featured),
      reading_minutes: Number(post.reading_minutes || 3),
    })
    useNotificationStore().success('Đã đổi trạng thái bài viết.')
  } catch (error) {
    post.status = previousStatus
    errorMessage.value = error.response?.data?.message || 'Không đổi được trạng thái.'
  }
}

const handleDelete = async (post) => {
  if (!post || deletingId.value) {
    return
  }

  if (!window.confirm(`Xóa bài viết "${post.title}"?`)) {
    return
  }

  deletingId.value = post.id

  try {
    await newsService.deletePost(post.id)
    useNotificationStore().success('Đã xóa bài viết.')
    await loadData()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không xóa được bài viết.'
  } finally {
    deletingId.value = null
  }
}

onMounted(loadData)
</script>

<template>
  <div class="admin-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Quản lý nội dung</p>
        <h1>Bài viết tin tức</h1>
        <p class="subtitle">Soạn nội dung, gán danh mục, đánh dấu bài nổi bật và xuất bản ngay trong admin.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="openCreateModal">
            <i class="bi bi-plus-lg"></i>
            Thêm bài viết
          </button>
          <button type="button" class="secondary-action"
                  @click="search = ''; statusFilter = 'all'; categoryFilter = 'all'">
            <i class="bi bi-arrow-counterclockwise"></i>
            Xóa bộ lọc
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <article class="stat-card">
          <strong>{{ stats.total }}</strong>
          <span>Tổng bài viết</span>
        </article>
        <article class="stat-card">
          <strong>{{ stats.published }}</strong>
          <span>Đã xuất bản</span>
        </article>
        <article class="stat-card">
          <strong>{{ stats.featured }}</strong>
          <span>Nổi bật</span>
        </article>
        <article class="stat-card">
          <strong>{{ stats.drafts }}</strong>
          <span>Bản nháp</span>
        </article>
      </div>
    </section>

    <section v-if="loading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải bài viết...</p>
    </section>

    <section v-else-if="errorMessage" class="notice-card error">
      <i class="bi bi-exclamation-triangle"></i>
      <span>{{ errorMessage }}</span>
    </section>

    <template v-else>
      <section class="filter-card">
        <input v-model.trim="search" type="search" class="search-input" placeholder="Tìm bài viết...">
        <select v-model="statusFilter" class="status-select">
          <option value="all">Tất cả trạng thái</option>
          <option value="published">Đã xuất bản</option>
          <option value="draft">Bản nháp</option>
        </select>
        <select v-model="categoryFilter" class="status-select">
          <option value="all">Tất cả danh mục</option>
          <option v-for="category in categoryOptions" :key="category.id" :value="category.slug">
            {{ category.name }}
          </option>
        </select>
      </section>

      <section class="table-card">
        <div class="table-header">
          <div>
            <h2>Danh sách bài viết</h2>
            <p>Hiển thị {{ pageStart }}-{{ pageEnd }} trong tổng số {{ filteredPosts.length }} bài viết.</p>
          </div>
        </div>

        <div class="table-responsive">
          <table class="table align-middle admin-table mb-0">
            <thead>
            <tr>
              <th>Bài viết</th>
              <th>Danh mục</th>
              <th>Trạng thái</th>
              <th>Nổi bật</th>
              <th>Lượt xem</th>
              <th>Xuất bản</th>
              <th class="text-end">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="post in paginatedItems" :key="post.id">
              <td>
                <div class="post-meta-box">
                  <img :src="post.featured_image_url" :alt="post.title" class="thumb">
                  <div>
                    <strong>{{ post.title }}</strong>
                    <div class="muted">{{ post.excerpt }}</div>
                  </div>
                </div>
              </td>
              <td>{{ post.category?.name || 'Chưa gán' }}</td>
              <td>
                <button
                    type="button"
                    class="status-pill"
                    :class="post.status === 'published' ? 'is-active' : 'is-inactive'"
                    @click="handleToggleStatus(post)"
                >
                  {{ post.status === 'published' ? 'Xuất bản' : 'Bản nháp' }}
                </button>
              </td>
              <td>
                <span class="feature-pill" :class="post.is_featured ? 'is-active' : 'is-inactive'">
                  {{ post.is_featured ? 'Có' : 'Không' }}
                </span>
              </td>
              <td>{{ Number(post.views_count || 0).toLocaleString('vi-VN') }}</td>
              <td>{{ formatDate(post.published_at) || 'N/A' }}</td>
              <td class="text-end">
                <button type="button" class="action-btn action-edit" @click="openEditModal(post)">
                  <i class="bi bi-pencil"></i>
                </button>
                <button type="button" class="action-btn action-delete" :disabled="deletingId === post.id"
                        @click="handleDelete(post)">
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
            </tbody>
          </table>
        </div>

        <div v-if="totalPages > 1" class="pagination-wrap">
          <ListPaginationControls
              v-model:currentPage="currentPage"
              v-model:pageSize="pageSize"
              :total-pages="totalPages"
              :total-items="filteredPosts.length"
              :page-start="pageStart"
              :page-end="pageEnd"
              item-label="bài viết"
          />
        </div>
      </section>
    </template>

    <BaseModal :show="showModal" :title="editingId ? 'Sửa bài viết tin tức' : 'Thêm bài viết tin tức'"
               @close="closeModal">
      <form class="modal-form" @submit.prevent="submitForm">
        <label class="field">
          <span>Danh mục</span>
          <select v-model="form.news_category_id" :class="{ invalid: fieldErrors.news_category_id }">
            <option value="">Chọn danh mục</option>
            <option v-for="category in categoryOptions" :key="category.id" :value="String(category.id)">
              {{ category.name }}
            </option>
          </select>
          <small v-if="fieldErrors.news_category_id" class="field-error">{{ fieldErrors.news_category_id }}</small>
        </label>

        <label class="field">
          <span>Tiêu đề</span>
          <input v-model.trim="form.title" type="text" :class="{ invalid: fieldErrors.title }">
          <small v-if="fieldErrors.title" class="field-error">{{ fieldErrors.title }}</small>
        </label>

        <label class="field">
          <span>Mô tả ngắn</span>
          <textarea v-model.trim="form.excerpt" rows="3" :class="{ invalid: fieldErrors.excerpt }"></textarea>
          <small v-if="fieldErrors.excerpt" class="field-error">{{ fieldErrors.excerpt }}</small>
        </label>

        <label class="field">
          <span>Nội dung</span>
          <textarea v-model.trim="form.content" rows="8" :class="{ invalid: fieldErrors.content }"></textarea>
          <small v-if="fieldErrors.content" class="field-error">{{ fieldErrors.content }}</small>
        </label>

        <label class="field">
          <span>Ảnh đại diện URL</span>
          <input v-model.trim="form.featured_image_url" type="text"
                 :class="{ invalid: fieldErrors.featured_image_url }">
          <small v-if="fieldErrors.featured_image_url" class="field-error">{{ fieldErrors.featured_image_url }}</small>
        </label>

        <div class="field-grid">
          <label class="field">
            <span>Trạng thái</span>
            <select v-model="form.status" :class="{ invalid: fieldErrors.status }">
              <option value="draft">Bản nháp</option>
              <option value="published">Xuất bản</option>
            </select>
            <small v-if="fieldErrors.status" class="field-error">{{ fieldErrors.status }}</small>
          </label>
          <label class="field">
            <span>Thời gian đọc</span>
            <input v-model.number="form.reading_minutes" type="number" min="1"
                   :class="{ invalid: fieldErrors.reading_minutes }">
            <small v-if="fieldErrors.reading_minutes" class="field-error">{{ fieldErrors.reading_minutes }}</small>
          </label>
        </div>

        <label class="checkbox-field">
          <input v-model="form.is_featured" type="checkbox">
          <span>Đánh dấu nổi bật</span>
        </label>

        <p v-if="formError" class="form-error">{{ formError }}</p>

        <div class="modal-actions">
          <button type="button" class="secondary-action" @click="closeModal">Hủy</button>
          <button type="submit" class="primary-action" :disabled="saving">
            {{ saving ? 'Đang lưu...' : 'Lưu bài viết' }}
          </button>
        </div>
      </form>
    </BaseModal>
  </div>
</template>

<style scoped>
.hero-card,
.filter-card,
.table-card,
.state-card,
.notice-card {
  margin-bottom: 18px;
}

.hero-card {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 24px;
  border: 1px solid #e5eaf3;
  border-radius: 24px;
  background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
}

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 32px;
  font-weight: 900;
}

.subtitle {
  margin: 10px 0 0;
  color: #475569;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 16px;
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

.primary-action:hover {
  filter: brightness(0.98);
}

.secondary-action:hover {
  border-color: #bfdbfe;
  color: #2563eb;
  background: #f8fbff;
}

.hero-stats {
  display: grid;
  gap: 12px;
  min-width: 220px;
}

.stat-card {
  padding: 16px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #fff;
}

.stat-card strong {
  display: block;
  color: #0f172a;
  font-size: 28px;
  font-weight: 900;
}

.stat-card span,
.muted {
  color: #64748b;
  font-size: 13px;
}

.filter-card {
  padding: 14px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #fff;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 180px 220px;
  gap: 12px;
}

.search-input,
.status-select,
.field input,
.field select,
.field textarea {
  width: 100%;
  min-height: 44px;
  padding: 0 14px;
  border: 1px solid #dbe4f0;
  border-radius: 12px;
  background: #fff;
}

.field textarea {
  min-height: 110px;
  padding: 12px 14px;
  resize: vertical;
}

.table-card {
  padding: 20px;
  border: 1px solid #e5eaf3;
  border-radius: 22px;
  background: #fff;
}

.table-header {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}

.table-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 20px;
  font-weight: 900;
}

.table-header p {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 13px;
}

.post-meta-box {
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.thumb {
  width: 78px;
  height: 58px;
  border-radius: 14px;
  object-fit: cover;
  background: #f1f5f9;
}

.status-pill,
.feature-pill {
  display: inline-flex;
  align-items: center;
  min-height: 30px;
  padding: 0 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
}

.status-pill.is-active,
.feature-pill.is-active {
  background: #ecfdf5;
  color: #047857;
}

.status-pill.is-inactive,
.feature-pill.is-inactive {
  background: #fff7ed;
  color: #c2410c;
}

.action-btn {
  width: 36px;
  height: 36px;
  border: none;
  border-radius: 10px;
  margin-left: 6px;
}

.action-edit {
  background: #eff6ff;
  color: #2563eb;
}

.action-delete {
  background: #fef2f2;
  color: #dc2626;
}

.pagination-wrap {
  margin-top: 14px;
}

.modal-form {
  display: grid;
  gap: 14px;
}

.field {
  display: grid;
  gap: 8px;
}

.field-grid {
  display: grid;
  grid-template-columns: 1fr 160px;
  gap: 12px;
}

.field span {
  color: #334155;
  font-size: 13px;
  font-weight: 800;
}

.checkbox-field {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: #334155;
  font-size: 13px;
  font-weight: 700;
}

.field-error,
.form-error {
  color: #dc2626;
  font-size: 12px;
  font-weight: 700;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 4px;
}

@media (max-width: 991.98px) {
  .hero-card,
  .filter-card {
    grid-template-columns: 1fr;
    flex-direction: column;
  }

  .secondary-action,
  .primary-action {
    width: 100%;
    justify-content: center;
  }
}
</style>
