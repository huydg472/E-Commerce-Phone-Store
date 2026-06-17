<script setup>
import {computed, onMounted, ref, watch} from 'vue'
import {useRoute} from 'vue-router'
import {newsService} from '@/services/newsService'
import {formatDate} from '@/utils/formatDate'
import {useClientPagination} from '@/composables/useClientPagination.js'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'

const route = useRoute()

const loading = ref(true)
const errorMessage = ref('')
const categories = ref([])
const posts = ref([])

const slug = computed(() => String(route.params.slug ?? ''))

const loadData = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const [categoriesResponse, postsResponse] = await Promise.all([
      newsService.getPublicCategories(),
      newsService.getPublicPosts(),
    ])

    const categoriesPayload = categoriesResponse.data?.data ?? categoriesResponse.data ?? []
    const postsPayload = postsResponse.data?.data ?? postsResponse.data ?? []

    categories.value = Array.isArray(categoriesPayload) ? categoriesPayload : []
    posts.value = Array.isArray(postsPayload) ? postsPayload : []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không tải được chủ đề.'
  } finally {
    loading.value = false
  }
}

const activeCategories = computed(() => categories.value.filter((category) => category?.status === 'active'))

const selectedCategory = computed(() => {
  if (!slug.value) {
    return null
  }

  return activeCategories.value.find((category) => category?.slug === slug.value) || null
})

const filteredPosts = computed(() => {
  if (!slug.value) {
    return [...(Array.isArray(posts.value) ? posts.value : [])]
  }

  return (Array.isArray(posts.value) ? posts.value : []).filter((post) => String(post?.category?.slug ?? '') === slug.value)
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems,
  pageStart,
  pageEnd,
} = useClientPagination(filteredPosts, {
  defaultPageSize: 8,
  pageSizeOptions: [8, 12, 16],
})

watch(slug, () => {
  currentPage.value = 1
})

onMounted(loadData)
</script>

<template>
  <main class="topic-page">
    <div class="topic-shell">
      <nav class="breadcrumb-wrap" aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <RouterLink to="/">Trang chủ</RouterLink>
          </li>
          <li class="breadcrumb-item">
            <RouterLink to="/tin-tuc">Tin tức</RouterLink>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            Chủ đề
          </li>
        </ol>
      </nav>

      <section class="hero-card">
        <div>
          <p class="eyebrow">Danh mục bài viết</p>
          <h1>{{ selectedCategory?.name || 'Tất cả chủ đề tin tức' }}</h1>
          <p class="subtitle">
            {{ selectedCategory?.description || 'Khám phá toàn bộ chuyên mục, chọn một chủ đề cụ thể hoặc xem các bài viết mới nhất.' }}
          </p>
        </div>

        <div class="hero-stat">
          <strong>{{ filteredPosts.length }}</strong>
          <span>Bài viết</span>
        </div>
      </section>

      <section v-if="loading" class="state-card">
        <div class="spinner-border text-primary" role="status"></div>
        <p>Đang tải chủ đề...</p>
      </section>

      <section v-else-if="errorMessage" class="state-card error">
        <i class="bi bi-exclamation-triangle"></i>
        <p>{{ errorMessage }}</p>
      </section>

      <template v-else>
        <section class="category-strip">
          <RouterLink
              to="/tin-tuc/chu-de"
              class="category-pill"
              :class="{ active: !slug }"
          >
            Tất cả
          </RouterLink>
          <RouterLink
              v-for="category in activeCategories"
              :key="category.id"
              :to="`/tin-tuc/chu-de/${category.slug}`"
              class="category-pill"
              :class="{ active: slug === category.slug }"
          >
            {{ category.name }}
            <span>{{ Number(category.posts_count || 0) }}</span>
          </RouterLink>
        </section>

        <section class="post-panel">
          <div class="panel-head">
            <div>
              <h2>{{ selectedCategory?.name || 'Bài viết theo chủ đề' }}</h2>
              <p>Hiển thị {{ pageStart }}-{{ pageEnd }} trong tổng số {{ filteredPosts.length }} bài viết</p>
            </div>
          </div>

          <div class="post-grid">
            <article v-for="post in paginatedItems" :key="post.id" class="post-card">
              <RouterLink :to="`/tin-tuc/${post.slug}`" class="post-image-link">
                <img :src="post.featured_image_url" :alt="post.title" class="post-image">
              </RouterLink>

              <div class="post-body">
                <p class="post-category">{{ post.category?.name || 'Tin tức' }}</p>
                <RouterLink :to="`/tin-tuc/${post.slug}`" class="post-title">
                  {{ post.title }}
                </RouterLink>
                <p class="post-excerpt">{{ post.excerpt }}</p>
                <div class="post-meta">
                  <span>{{ formatDate(post.published_at) }}</span>
                  <span>{{ post.reading_minutes }} phút đọc</span>
                </div>
              </div>
            </article>
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
    </div>
  </main>
</template>

<style scoped>
.topic-page {
  padding: 28px 0 40px;
  background: linear-gradient(180deg, #f8fbff 0%, #ffffff 28%, #ffffff 100%);
}

.topic-shell {
  width: min(1280px, calc(100% - 32px));
  margin: 0 auto;
}

.breadcrumb-wrap {
  margin-bottom: 16px;
}

.breadcrumb {
  margin: 0;
  font-size: 14px;
}

.breadcrumb a {
  color: #64748b;
  text-decoration: none;
}

.breadcrumb-item.active {
  color: #2563eb;
}

.hero-card {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  padding: 26px;
  border: 1px solid #e5eaf3;
  border-radius: 24px;
  background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
  box-shadow: 0 18px 50px rgba(15, 23, 42, 0.06);
}

.eyebrow {
  margin: 0 0 10px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.hero-card h1 {
  margin: 0;
  color: #0f172a;
  font-size: clamp(26px, 2.8vw, 38px);
  font-weight: 900;
}

.subtitle {
  margin: 12px 0 0;
  color: #475569;
  line-height: 1.7;
}

.hero-stat {
  min-width: 120px;
  padding: 18px 20px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #fff;
  align-self: center;
}

.hero-stat strong {
  display: block;
  color: #0f172a;
  font-size: 30px;
  font-weight: 900;
}

.hero-stat span {
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.state-card {
  min-height: 220px;
  margin-top: 20px;
  border: 1px dashed #d6deeb;
  border-radius: 20px;
  background: #ffffff;
  color: #334155;
  display: grid;
  place-items: center;
  gap: 10px;
}

.state-card.error {
  color: #b91c1c;
}

.category-strip {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 22px;
}

.category-pill {
  min-height: 38px;
  padding: 0 14px;
  border: 1px solid #d7e2f0;
  border-radius: 999px;
  background: #fff;
  color: #334155;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
}

.category-pill span {
  padding: 2px 7px;
  border-radius: 999px;
  background: #f8fafc;
  color: #64748b;
  font-size: 12px;
}

.category-pill.active {
  border-color: #2563eb;
  background: #eff6ff;
  color: #1d4ed8;
}

.post-panel {
  margin-top: 22px;
  padding: 22px;
  border: 1px solid #e5eaf3;
  border-radius: 24px;
  background: #fff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
}

.panel-head h2 {
  margin: 0;
  color: #0f172a;
  font-size: 20px;
  font-weight: 900;
}

.panel-head p {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 13px;
}

.post-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
  margin-top: 18px;
}

.post-card {
  overflow: hidden;
  border: 1px solid #edf2f7;
  border-radius: 18px;
  background: #fff;
}

.post-image-link {
  display: block;
}

.post-image {
  width: 100%;
  height: 170px;
  object-fit: cover;
}

.post-body {
  padding: 14px;
}

.post-category {
  margin: 0;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.post-title {
  display: block;
  margin-top: 4px;
  color: #0f172a;
  text-decoration: none;
  font-size: 16px;
  line-height: 1.45;
  font-weight: 900;
}

.post-excerpt {
  margin: 12px 0 0;
  color: #475569;
  line-height: 1.7;
}

.post-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 16px;
  margin-top: 14px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.pagination-wrap {
  margin-top: 18px;
}

@media (max-width: 1100px) {
  .hero-card {
    flex-direction: column;
  }
}

@media (max-width: 767.98px) {
  .topic-shell {
    width: min(100% - 20px, 100%);
  }

  .post-grid {
    grid-template-columns: 1fr;
  }
}
</style>
