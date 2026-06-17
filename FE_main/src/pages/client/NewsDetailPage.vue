<script setup>
import {computed, onMounted, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {newsService} from '@/services/newsService'
import {formatDate} from '@/utils/formatDate'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const errorMessage = ref('')
const post = ref(null)
const relatedPosts = ref([])

const slug = computed(() => String(route.params.slug ?? ''))

const loadData = async () => {
  if (!slug.value) {
    errorMessage.value = 'Thiếu slug bài viết.'
    loading.value = false
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    const response = await newsService.getPublicPost(slug.value)
    const payload = response.data?.data ?? response.data ?? {}

    post.value = payload.post ?? null
    relatedPosts.value = Array.isArray(payload.related_posts) ? payload.related_posts : []
  } catch (error) {
    if (error.response?.status === 404) {
      await router.replace('/404')
      return
    }

    errorMessage.value = error.response?.data?.message || 'Không tải được bài viết.'
  } finally {
    loading.value = false
  }
}

watch(slug, loadData, {immediate: true})
</script>

<template>
  <main class="news-detail">
    <div class="detail-shell">
      <nav class="breadcrumb-wrap" aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item">
            <RouterLink to="/">Trang chủ</RouterLink>
          </li>
          <li class="breadcrumb-item">
            <RouterLink to="/tin-tuc">Tin tức</RouterLink>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            Chi tiết bài viết
          </li>
        </ol>
      </nav>

      <section v-if="loading" class="state-card">
        <div class="spinner-border text-primary" role="status"></div>
        <p>Đang tải bài viết...</p>
      </section>

      <section v-else-if="errorMessage" class="state-card error">
        <i class="bi bi-exclamation-triangle"></i>
        <p>{{ errorMessage }}</p>
      </section>

      <template v-else-if="post">
        <article class="hero-article">
          <div class="hero-image-wrap">
            <img :src="post.featured_image_url" :alt="post.title" class="hero-image">
          </div>

          <div class="hero-copy">
            <p class="eyebrow">{{ post.category?.name || 'Tin tức' }}</p>
            <h1>{{ post.title }}</h1>
            <div class="post-meta">
              <span>{{ formatDate(post.published_at) }}</span>
              <span>{{ post.reading_minutes }} phút đọc</span>
              <span>{{ Number(post.views_count || 0).toLocaleString('vi-VN') }} lượt xem</span>
            </div>
            <p class="excerpt">{{ post.excerpt }}</p>

            <div class="hero-actions">
              <RouterLink to="/tin-tuc" class="action-link primary-link">
                <i class="bi bi-arrow-left"></i>
                Quay lại tin tức
              </RouterLink>

              <RouterLink
                  v-if="post.category?.slug"
                  :to="`/tin-tuc/chu-de/${post.category.slug}`"
                  class="action-link secondary-link"
              >
                <i class="bi bi-tag"></i>
                {{ post.category.name }}
              </RouterLink>
            </div>
          </div>
        </article>

        <div class="content-grid">
          <section class="article-content card-surface">
            <h2>Nội dung bài viết</h2>
            <div class="article-body" v-text="post.content"></div>
          </section>

          <aside class="sidebar">
            <section class="card-surface">
              <h3>Bài viết liên quan</h3>
              <div v-if="relatedPosts.length" class="related-list">
                <article v-for="item in relatedPosts" :key="item.id" class="related-item">
                  <RouterLink :to="`/tin-tuc/${item.slug}`" class="related-image">
                    <img :src="item.featured_image_url" :alt="item.title">
                  </RouterLink>

                  <div class="related-copy">
                    <RouterLink :to="`/tin-tuc/${item.slug}`">
                      {{ item.title }}
                    </RouterLink>
                    <span>{{ formatDate(item.published_at) }}</span>
                  </div>
                </article>
              </div>

              <p v-else class="empty-text">Chưa có bài viết liên quan.</p>
            </section>

            <section class="card-surface">
              <h3>Chủ đề</h3>
              <RouterLink
                  v-if="post.category?.slug"
                  :to="`/tin-tuc/chu-de/${post.category.slug}`"
                  class="topic-chip"
              >
                {{ post.category.name }}
              </RouterLink>
            </section>
          </aside>
        </div>
      </template>
    </div>
  </main>
</template>

<style scoped>
.news-detail {
  padding: 28px 0 40px;
  background: linear-gradient(180deg, #f8fbff 0%, #ffffff 24%, #ffffff 100%);
}

.detail-shell {
  width: min(1200px, calc(100% - 32px));
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

.state-card {
  min-height: 220px;
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

.hero-article {
  overflow: hidden;
  border: 1px solid #e5eaf3;
  border-radius: 26px;
  background: #fff;
  box-shadow: 0 18px 50px rgba(15, 23, 42, 0.06);
}

.hero-image {
  width: 100%;
  height: 420px;
  object-fit: cover;
}

.hero-copy {
  padding: 28px;
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
  line-height: 1.12;
  font-weight: 900;
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

.excerpt {
  margin: 16px 0 0;
  color: #475569;
  line-height: 1.75;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 18px;
}

.action-link {
  min-height: 40px;
  padding: 0 14px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 13px;
  font-weight: 800;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease, background 0.2s ease;
}

.action-link:hover {
  transform: translateY(-1px);
}

.primary-link {
  border: none;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
  box-shadow: 0 10px 22px rgba(37, 99, 235, 0.18);
}

.secondary-link {
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}

.secondary-link:hover {
  border-color: #bfdbfe;
  background: #f8fbff;
  color: #2563eb;
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.45fr) 320px;
  gap: 22px;
  margin-top: 22px;
}

.card-surface {
  padding: 22px;
  border: 1px solid #e5eaf3;
  border-radius: 22px;
  background: #fff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.04);
}

.article-content h2,
.card-surface h3 {
  margin: 0;
  color: #0f172a;
  font-size: 20px;
  font-weight: 900;
}

.article-body {
  margin-top: 14px;
  color: #334155;
  line-height: 1.9;
  white-space: pre-line;
  font-size: 15px;
}

.article-body :deep(p) {
  margin: 0 0 14px;
}

.article-body :deep(h2),
.article-body :deep(h3) {
  margin: 24px 0 12px;
  color: #0f172a;
  line-height: 1.35;
}

.article-body :deep(ul),
.article-body :deep(ol) {
  padding-left: 22px;
  margin: 0 0 14px;
}

.article-body :deep(li) {
  margin-bottom: 8px;
}

.related-list {
  display: grid;
  gap: 14px;
  margin-top: 14px;
}

.related-item {
  display: flex;
  gap: 12px;
}

.related-image {
  flex: 0 0 88px;
  width: 88px;
  height: 64px;
  overflow: hidden;
  border-radius: 14px;
  background: #f1f5f9;
}

.related-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.related-copy {
  min-width: 0;
}

.related-copy a {
  display: -webkit-box;
  overflow: hidden;
  color: #0f172a;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
  line-height: 1.4;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.related-copy span {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.empty-text {
  margin: 14px 0 0;
  color: #64748b;
}

.topic-chip {
  display: inline-flex;
  align-items: center;
  min-height: 36px;
  margin-top: 14px;
  padding: 0 14px;
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  text-decoration: none;
  font-size: 13px;
  font-weight: 800;
}

.sidebar {
  position: relative;
}

.sidebar .card-surface {
  position: sticky;
  top: 24px;
}

@media (max-width: 1100px) {
  .content-grid {
    grid-template-columns: 1fr;
  }

  .sidebar .card-surface {
    position: static;
  }
}

@media (max-width: 767.98px) {
  .detail-shell {
    width: min(100% - 20px, 100%);
  }

  .hero-image {
    height: 240px;
  }
}
</style>
