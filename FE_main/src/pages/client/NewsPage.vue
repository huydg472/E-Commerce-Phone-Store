<script setup>
import {computed, onMounted, ref, watch} from 'vue'
import {newsService} from '@/services/newsService'
import {formatDate} from '@/utils/formatDate'
import {useClientPagination} from '@/composables/useClientPagination.js'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'

const loading = ref(true)
const errorMessage = ref('')
const search = ref('')
const selectedCategory = ref('all')
const posts = ref([])
const categories = ref([])

const normalize = (value) => String(value ?? '').trim().toLowerCase()

const loadData = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const [postsResponse, categoriesResponse] = await Promise.all([
      newsService.getPublicPosts(),
      newsService.getPublicCategories(),
    ])

    const postsPayload = postsResponse.data?.data ?? postsResponse.data ?? []
    const categoriesPayload = categoriesResponse.data?.data ?? categoriesResponse.data ?? []

    posts.value = Array.isArray(postsPayload) ? postsPayload : []
    categories.value = Array.isArray(categoriesPayload) ? categoriesPayload : []
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không tải được tin tức.'
  } finally {
    loading.value = false
  }
}

const activeCategories = computed(() => categories.value.filter((category) => category?.status === 'active'))

const filteredPosts = computed(() => {
  const query = normalize(search.value)

  return (Array.isArray(posts.value) ? posts.value : []).filter((post) => {
    const categorySlug = String(post?.category?.slug ?? '')
    const matchesCategory = selectedCategory.value === 'all' || categorySlug === selectedCategory.value
    const matchesQuery =
        !query ||
        [post?.title, post?.excerpt, post?.category?.name]
            .filter(Boolean)
            .some((field) => normalize(field).includes(query))

    return matchesCategory && matchesQuery
  })
})

const featuredPost = computed(() => {
  return filteredPosts.value.find((post) => Boolean(post?.is_featured)) ?? filteredPosts.value[0] ?? null
})

const gridPosts = computed(() => {
  const featuredId = featuredPost.value?.id
  return filteredPosts.value.filter((post) => post?.id !== featuredId)
})

const popularPosts = computed(() => {
  return [...(Array.isArray(posts.value) ? posts.value : [])]
      .sort((left, right) => Number(right?.views_count ?? 0) - Number(left?.views_count ?? 0))
      .slice(0, 4)
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems,
  pageStart,
  pageEnd,
} = useClientPagination(gridPosts, {
  defaultPageSize: 8,
  pageSizeOptions: [8, 12, 16],
})

watch([search, selectedCategory], () => {
  currentPage.value = 1
})

onMounted(loadData)
</script>

<template>
  <main class="news-page">
    <div class="news-shell">
      <section class="hero-card">
        <div class="hero-copy">
          <p class="eyebrow">Tin tức công nghệ</p>
          <h1>Khám phá tin mới, đánh giá và xu hướng mua sắm</h1>
          <p class="subtitle">
            Cập nhật bài viết nổi bật, mẹo hay, khuyến mãi và so sánh thiết bị ngay trên ZinMobile.
          </p>

          <div class="search-row">
            <input
                v-model.trim="search"
                type="search"
                class="search-input"
                placeholder="Tìm bài viết, chủ đề hoặc mô tả..."
            />
          </div>

          <div class="category-chips">
            <button
                type="button"
                class="chip"
                :class="{ active: selectedCategory === 'all' }"
                @click="selectedCategory = 'all'"
            >
              Tất cả
            </button>
            <button
                v-for="category in activeCategories"
                :key="category.id"
                type="button"
                class="chip"
                :class="{ active: selectedCategory === category.slug }"
                @click="selectedCategory = category.slug"
            >
              {{ category.name }}
            </button>
          </div>
        </div>

        <div class="hero-metrics">
          <article class="metric-card">
            <strong>{{ posts.length }}</strong>
            <span>Bài viết</span>
          </article>
          <article class="metric-card">
            <strong>{{ activeCategories.length }}</strong>
            <span>Chủ đề</span>
          </article>
          <article class="metric-card">
            <strong>{{ popularPosts.length }}</strong>
            <span>Nổi bật</span>
          </article>
        </div>
      </section>

      <section v-if="loading" class="state-card">
        <div class="spinner-border text-primary" role="status"></div>
        <p>Đang tải tin tức...</p>
      </section>

      <section v-else-if="errorMessage" class="state-card error">
        <i class="bi bi-exclamation-triangle"></i>
        <p>{{ errorMessage }}</p>
      </section>

      <template v-else>
        <section v-if="featuredPost" class="featured-card">
          <RouterLink :to="`/tin-tuc/${featuredPost.slug}`" class="featured-image-link">
            <img :src="featuredPost.featured_image_url" :alt="featuredPost.title" class="featured-image">
          </RouterLink>

          <div class="featured-body">
            <p class="post-category">{{ featuredPost.category?.name || 'Tin tức' }}</p>
            <RouterLink :to="`/tin-tuc/${featuredPost.slug}`" class="featured-title">
              {{ featuredPost.title }}
            </RouterLink>
            <p class="featured-excerpt">{{ featuredPost.excerpt }}</p>

            <div class="post-meta">
              <span>{{ formatDate(featuredPost.published_at) }}</span>
              <span>{{ featuredPost.reading_minutes }} phút đọc</span>
              <span>{{ Number(featuredPost.views_count || 0).toLocaleString('vi-VN') }} lượt xem</span>
            </div>

            <RouterLink :to="`/tin-tuc/${featuredPost.slug}`" class="read-more">
              Đọc ngay
            </RouterLink>
          </div>
        </section>

        <div class="content-grid">
          <section class="article-panel">
            <div class="panel-head">
              <div>
                <h2>Bài viết mới</h2>
                <p v-if="selectedCategory !== 'all'">
                  Đang lọc theo chủ đề {{
                    activeCategories.find((category) => category.slug === selectedCategory)?.name
                  }}
                </p>
                <p v-else>
                  Hiển thị {{ pageStart }}-{{ pageEnd }} trong tổng số {{ gridPosts.length }} bài viết
                </p>
              </div>
            </div>

            <div class="article-grid">
              <article v-for="post in paginatedItems" :key="post.id" class="article-card">
                <RouterLink :to="`/tin-tuc/${post.slug}`" class="article-image-link">
                  <img :src="post.featured_image_url" :alt="post.title" class="article-image">
                </RouterLink>

                <div class="article-body">
                  <p class="post-category">{{ post.category?.name || 'Tin tức' }}</p>
                  <RouterLink :to="`/tin-tuc/${post.slug}`" class="article-title">
                    {{ post.title }}
                  </RouterLink>
                  <p class="article-excerpt">{{ post.excerpt }}</p>
                  <div class="post-meta compact">
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
                  :total-items="gridPosts.length"
                  :page-start="pageStart"
                  :page-end="pageEnd"
                  item-label="bài viết"
              />
            </div>
          </section>

          <aside class="sidebar">
            <section class="sidebar-box">
              <h3>Bài viết nổi bật</h3>
              <div v-for="item in popularPosts" :key="item.id" class="popular-item">
                <RouterLink :to="`/tin-tuc/${item.slug}`" class="popular-image">
                  <img :src="item.featured_image_url" :alt="item.title">
                </RouterLink>

                <div class="popular-content">
                  <RouterLink :to="`/tin-tuc/${item.slug}`">
                    {{ item.title }}
                  </RouterLink>
                  <span>{{ formatDate(item.published_at) }}</span>
                </div>
              </div>
            </section>

            <section class="sidebar-box">
              <h3>Chủ đề</h3>
              <ul class="topic-list">
                <li v-for="category in activeCategories" :key="category.id">
                  <RouterLink :to="`/tin-tuc/chu-de/${category.slug}`">
                    {{ category.name }}
                  </RouterLink>
                  <span>{{ Number(category.posts_count || 0) }}</span>
                </li>
              </ul>
              <RouterLink to="/tin-tuc/chu-de" class="view-all-topic">
                Xem tất cả chủ đề
                <span>›</span>
              </RouterLink>
            </section>
          </aside>
        </div>
      </template>
    </div>
  </main>
</template>

<style scoped>
.news-page {
  padding: 28px 0 40px;
  background: radial-gradient(circle at top left, rgba(37, 99, 235, 0.08), transparent 28%),
  linear-gradient(180deg, #f8fbff 0%, #ffffff 35%, #ffffff 100%);
}

.news-shell {
  width: min(1280px, calc(100% - 32px));
  margin: 0 auto;
}

.hero-card {
  display: grid;
  grid-template-columns: minmax(0, 1.6fr) minmax(240px, 0.8fr);
  gap: 20px;
  padding: 28px;
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

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: clamp(28px, 3vw, 42px);
  line-height: 1.08;
  font-weight: 900;
}

.subtitle {
  max-width: 780px;
  margin: 14px 0 0;
  color: #475569;
  font-size: 15px;
  line-height: 1.7;
}

.search-row {
  margin-top: 18px;
}

.search-input {
  width: 100%;
  min-height: 46px;
  padding: 0 16px;
  border: 1px solid #dbe4f0;
  border-radius: 14px;
  background: #fff;
  outline: none;
}

.search-input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.08);
}

.category-chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 16px;
}

.chip {
  min-height: 38px;
  padding: 0 14px;
  border: 1px solid #d7e2f0;
  border-radius: 999px;
  background: #fff;
  color: #334155;
  font-size: 14px;
  font-weight: 700;
}

.chip.active {
  border-color: #2563eb;
  background: #eff6ff;
  color: #1d4ed8;
}

.hero-metrics {
  display: grid;
  gap: 12px;
}

.metric-card {
  padding: 18px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
}

.metric-card strong {
  display: block;
  color: #0f172a;
  font-size: 26px;
  font-weight: 900;
}

.metric-card span {
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

.featured-card {
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
  margin-top: 22px;
  border: 1px solid #e5eaf3;
  border-radius: 24px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 16px 40px rgba(15, 23, 42, 0.05);
}

.featured-image-link {
  display: block;
  min-height: 100%;
}

.featured-image {
  width: 100%;
  height: 100%;
  min-height: 300px;
  object-fit: cover;
}

.featured-body {
  padding: 28px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.post-category {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.featured-title,
.article-title,
.popular-content a {
  color: #0f172a;
  text-decoration: none;
}

.featured-title {
  display: block;
  font-size: clamp(22px, 2.2vw, 34px);
  line-height: 1.15;
  font-weight: 900;
}

.featured-excerpt,
.article-excerpt {
  margin: 14px 0 0;
  color: #475569;
  line-height: 1.7;
}

.post-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 16px;
  margin-top: 16px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.post-meta.compact {
  margin-top: 12px;
}

.read-more {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 42px;
  margin-top: 18px;
  padding: 0 18px;
  border-radius: 12px;
  background: #2563eb;
  color: #fff;
  font-weight: 800;
  text-decoration: none;
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) 320px;
  gap: 22px;
  margin-top: 22px;
}

.article-panel,
.sidebar-box {
  border: 1px solid #e5eaf3;
  border-radius: 22px;
  background: #fff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
}

.article-panel {
  padding: 22px;
}

.panel-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.panel-head h2,
.sidebar-box h3 {
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

.article-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 18px;
}

.article-card {
  overflow: hidden;
  border: 1px solid #edf2f7;
  border-radius: 18px;
  background: #fff;
}

.article-image-link {
  display: block;
}

.article-image {
  width: 100%;
  height: 170px;
  object-fit: cover;
}

.article-body {
  padding: 14px;
}

.article-title {
  display: block;
  margin-top: 4px;
  font-size: 16px;
  line-height: 1.45;
  font-weight: 900;
}

.sidebar {
  display: grid;
  gap: 18px;
}

.sidebar-box {
  padding: 18px;
}

.sidebar-box + .sidebar-box {
  margin-top: 18px;
}

.popular-item {
  display: flex;
  gap: 12px;
}

.popular-item + .popular-item {
  margin-top: 16px;
}

.popular-image {
  flex: 0 0 86px;
  width: 86px;
  height: 64px;
  overflow: hidden;
  border-radius: 14px;
  background: #f1f5f9;
}

.popular-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.popular-content {
  min-width: 0;
}

.popular-content a {
  display: -webkit-box;
  overflow: hidden;
  font-size: 14px;
  line-height: 1.4;
  font-weight: 800;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.popular-content span {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.topic-list {
  padding: 0;
  margin: 14px 0 0;
  list-style: none;
}

.topic-list li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 8px 0;
}

.topic-list a {
  color: #334155;
  text-decoration: none;
  font-size: 14px;
  font-weight: 700;
}

.topic-list span {
  min-width: 34px;
  padding: 3px 8px;
  border-radius: 999px;
  background: #f8fafc;
  color: #475569;
  text-align: center;
  font-size: 12px;
  font-weight: 800;
}

.view-all-topic {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  margin-top: 12px;
  color: #2563eb;
  text-decoration: none;
  font-weight: 800;
}

.pagination-wrap {
  margin-top: 18px;
}

@media (max-width: 1100px) {
  .hero-card,
  .featured-card,
  .content-grid {
    grid-template-columns: 1fr;
  }

  .hero-metrics {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 767.98px) {
  .news-shell {
    width: min(100% - 20px, 100%);
  }

  .hero-card,
  .featured-body,
  .article-panel,
  .sidebar-box {
    padding: 18px;
  }

  .hero-metrics,
  .article-grid {
    grid-template-columns: 1fr;
  }

  .featured-image {
    min-height: 220px;
  }
}
</style>
