<script setup>
const featuredPost = {
  id: 1,
  slug: 'danh-gia-iphone-16-pro-max',
  title: 'Đánh giá iPhone 16 Pro Max: Nâng cấp xứng đáng, trải nghiệm đỉnh cao',
  excerpt:
      'iPhone 16 Pro Max mang đến hiệu năng mạnh mẽ hơn, camera cải tiến và thời lượng pin ấn tượng. Đây có phải là chiếc iPhone tốt nhất hiện nay?',
  category: 'Đánh giá',
  published_at: '20/05/2024',
  reading_minutes: 6,
  image_url: 'https://placehold.co/720x360/e5e7eb/111827?text=iPhone+16+Pro+Max'
}

const posts = [
  {
    id: 2,
    slug: 'samsung-galaxy-z-fold6-ra-mat',
    title: 'Samsung Galaxy Z Fold6 ra mắt: Mỏng hơn, nhẹ hơn, mạnh mẽ hơn',
    excerpt:
        'Galaxy Z Fold6 có thiết kế mỏng nhẹ hơn đáng kể, hiệu năng vượt trội với chip Snapdragon 8 Gen 3.',
    category: 'Tin công nghệ',
    published_at: '18/05/2024',
    reading_minutes: 4,
    image_url: 'https://placehold.co/420x240/e8e9ff/111827?text=Galaxy+Z+Fold6'
  },
  {
    id: 3,
    slug: 'oppo-reno11-5g-thiet-ke-thoi-thuong',
    title: 'OPPO Reno11 5G: Thiết kế thời thượng, camera chân dung ấn tượng',
    excerpt:
        'Khám phá OPPO Reno11 5G với thiết kế sang trọng, camera 64MP và sạc nhanh 67W siêu tốc.',
    category: 'Tin công nghệ',
    published_at: '15/05/2024',
    reading_minutes: 3,
    image_url: 'https://placehold.co/420x240/f5e8ff/111827?text=OPPO+Reno11+5G'
  },
  {
    id: 4,
    slug: 'apple-m4-hieu-nang-but-pha',
    title: 'Apple M4: Hiệu năng bứt phá, tối ưu cho AI và đồ họa',
    excerpt:
        'Chip M4 mới của Apple mang đến hiệu năng CPU và GPU vượt trội, tối ưu cho công việc sáng tạo và AI.',
    category: 'Đánh giá',
    published_at: '12/05/2024',
    reading_minutes: 5,
    image_url: 'https://placehold.co/420x240/e0f2fe/111827?text=Apple+M4'
  },
  {
    id: 5,
    slug: 'uu-dai-thang-5-giam-den-3-trieu',
    title: 'Ưu đãi tháng 5: Giảm đến 3 triệu đồng cho điện thoại chính hãng',
    excerpt:
        'Nhiều mẫu điện thoại chính hãng đang có ưu đãi hấp dẫn tại Zin Mobile. Số lượng có hạn!',
    category: 'Khuyến mãi',
    published_at: '10/05/2024',
    reading_minutes: 2,
    image_url: 'https://placehold.co/420x240/dbeafe/111827?text=Uu+dai+den+3+trieu'
  }
]

const popularPosts = [
  {
    id: 1,
    slug: 'danh-gia-iphone-16-pro-max',
    title: 'Đánh giá iPhone 16 Pro Max: Nâng cấp xứng đáng',
    published_at: '20/05/2024',
    image_url: 'https://placehold.co/90x70/e5e7eb/111827?text=iPhone'
  },
  {
    id: 2,
    slug: 'samsung-galaxy-z-fold6-ra-mat',
    title: 'Samsung Galaxy Z Fold6 ra mắt: Mỏng hơn, nhẹ hơn',
    published_at: '18/05/2024',
    image_url: 'https://placehold.co/90x70/e8e9ff/111827?text=Fold6'
  },
  {
    id: 4,
    slug: 'apple-m4-hieu-nang-but-pha',
    title: 'Apple M4: Hiệu năng bứt phá, tối ưu cho AI và đồ họa',
    published_at: '12/05/2024',
    image_url: 'https://placehold.co/90x70/e0f2fe/111827?text=M4'
  },
  {
    id: 5,
    slug: 'uu-dai-thang-5-giam-den-3-trieu',
    title: 'Ưu đãi tháng 5: Giảm đến 3 triệu đồng',
    published_at: '10/05/2024',
    image_url: 'https://placehold.co/90x70/dbeafe/111827?text=Sale'
  }
]

const topics = [
  {
    id: 1,
    name: 'Đánh giá',
    slug: 'danh-gia',
    total: 24
  },
  {
    id: 2,
    name: 'Tin công nghệ',
    slug: 'tin-cong-nghe',
    total: 32
  },
  {
    id: 3,
    name: 'Khuyến mãi',
    slug: 'khuyen-mai',
    total: 18
  },
  {
    id: 4,
    name: 'Thủ thuật',
    slug: 'thu-thuat',
    total: 15
  },
  {
    id: 5,
    name: 'So sánh',
    slug: 'so-sanh',
    total: 12
  }
]
</script>
<template>
  <main class="news-page">
    <div class="container">
      <!-- Breadcrumb -->
      <nav class="news-breadcrumb" aria-label="breadcrumb">
        <ol class="breadcrumb mb-2">
          <li class="breadcrumb-item">
            <RouterLink to="/">Trang chủ</RouterLink>
          </li>
          <li class="breadcrumb-item active" aria-current="page">
            Tin tức
          </li>
        </ol>
      </nav>

      <!-- Page title -->
      <div class="page-heading">
        <h1>Tin tức công nghệ</h1>
        <p>Cập nhật xu hướng, đánh giá sản phẩm và thủ thuật công nghệ mới nhất.</p>
      </div>

      <div class="row g-4 align-items-start">
        <!-- Main content -->
        <div class="col-lg-9">
          <!-- Featured article -->
          <article class="featured-card">
            <div class="row g-0 align-items-center">
              <div class="col-md-6">
                <div class="featured-image-wrap">
                  <img
                      :src="featuredPost.image_url"
                      :alt="featuredPost.title"
                      class="featured-image"
                  />

                  <span class="featured-badge">
                    Nổi bật
                  </span>
                </div>
              </div>

              <div class="col-md-6">
                <div class="featured-content">
                  <p class="post-category">
                    {{ featuredPost.category }}
                  </p>

                  <h2>
                    {{ featuredPost.title }}
                  </h2>

                  <p class="featured-desc">
                    {{ featuredPost.excerpt }}
                  </p>

                  <div class="post-meta">
                    <span>{{ featuredPost.published_at }}</span>
                    <span></span>
                    <span>{{ featuredPost.reading_minutes }} phút đọc</span>
                  </div>

                  <RouterLink
                      :to="`/tin-tuc/${featuredPost.slug}`"
                      class="btn btn-primary btn-read"
                  >
                    Đọc ngay
                  </RouterLink>
                </div>
              </div>
            </div>
          </article>

          <!-- Article grid -->
          <div class="row g-4 mt-1">
            <div
                v-for="post in posts"
                :key="post.id"
                class="col-md-6 col-xl-3"
            >
              <article class="news-card h-100">
                <RouterLink :to="`/tin-tuc/${post.slug}`" class="news-image-link">
                  <img
                      :src="post.image_url"
                      :alt="post.title"
                      class="news-image"
                  />
                </RouterLink>

                <div class="news-body">
                  <p class="post-category">
                    {{ post.category }}
                  </p>

                  <RouterLink :to="`/tin-tuc/${post.slug}`" class="news-title">
                    {{ post.title }}
                  </RouterLink>

                  <p class="news-desc">
                    {{ post.excerpt }}
                  </p>

                  <div class="post-meta">
                    <span>{{ post.published_at }}</span>
                    <span></span>
                    <span>{{ post.reading_minutes }} phút đọc</span>
                  </div>
                </div>
              </article>
            </div>
          </div>

          <!-- Load more -->
          <div class="text-center mt-4">
            <button type="button" class="btn btn-outline-primary btn-load-more">
              Xem thêm bài viết
              <span class="ms-1">⌄</span>
            </button>
          </div>
        </div>

        <!-- Sidebar -->
        <aside class="col-lg-3">
          <!-- Popular posts -->
          <section class="sidebar-box">
            <h3>Bài viết nổi bật</h3>

            <div
                v-for="item in popularPosts"
                :key="item.id"
                class="popular-item"
            >
              <RouterLink :to="`/tin-tuc/${item.slug}`" class="popular-image">
                <img :src="item.image_url" :alt="item.title"/>
              </RouterLink>

              <div class="popular-content">
                <RouterLink :to="`/tin-tuc/${item.slug}`">
                  {{ item.title }}
                </RouterLink>

                <span>{{ item.published_at }}</span>
              </div>
            </div>
          </section>

          <!-- Topics -->
          <section class="sidebar-box mt-3">
            <h3>Chủ đề quan tâm</h3>

            <ul class="topic-list">
              <li v-for="topic in topics" :key="topic.id">
                <RouterLink :to="`/tin-tuc/chu-de/${topic.slug}`">
                  {{ topic.name }}
                </RouterLink>

                <span>{{ topic.total }}</span>
              </li>
            </ul>

            <RouterLink to="/tin-tuc/chu-de" class="view-all-topic">
              Xem tất cả chủ đề
              <span>›</span>
            </RouterLink>
          </section>
        </aside>
      </div>
    </div>
  </main>
</template>

<style scoped>
.news-page {
  padding: 28px 0 36px;
  background: #fff;
  color: #111827;
}

.news-breadcrumb {
  margin-bottom: 8px;
}

.breadcrumb {
  font-size: 14px;
  font-weight: 500;
}

.breadcrumb a {
  color: #64748b;
  text-decoration: none;
}

.breadcrumb a:hover {
  color: #0d6efd;
}

.breadcrumb-item.active {
  color: #0d6efd;
}

.page-heading {
  margin-bottom: 22px;
}

.page-heading h1 {
  margin-bottom: 8px;
  font-size: 30px;
  font-weight: 800;
  letter-spacing: 0.2px;
}

.page-heading p {
  margin-bottom: 0;
  color: #475569;
  font-size: 15px;
  font-weight: 500;
}

/* Featured */
.featured-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  background: #fff;
}

.featured-image-wrap {
  position: relative;
  height: 255px;
  overflow: hidden;
  background: #f1f5f9;
}

.featured-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.featured-badge {
  position: absolute;
  top: 18px;
  left: 18px;
  padding: 7px 18px;
  background: #0d6efd;
  color: #fff;
  border-radius: 5px;
  font-size: 13px;
  font-weight: 700;
}

.featured-content {
  position: relative;
  min-height: 255px;
  padding: 34px 34px 28px;
}

.post-category {
  margin-bottom: 9px;
  color: #0d6efd;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.featured-content h2 {
  max-width: 620px;
  margin-bottom: 14px;
  color: #111827;
  font-size: 25px;
  line-height: 1.32;
  font-weight: 800;
}

.featured-desc {
  max-width: 640px;
  margin-bottom: 24px;
  color: #475569;
  font-size: 15px;
  line-height: 1.7;
  font-weight: 500;
}

.post-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #64748b;
  font-size: 13px;
  font-weight: 600;
}

.post-meta span:nth-child(2) {
  width: 1px;
  height: 14px;
  background: #cbd5e1;
}

.btn-read {
  position: absolute;
  right: 34px;
  bottom: 28px;
  min-width: 115px;
  height: 35px;
  border-radius: 4px;
  font-size: 14px;
  font-weight: 700;
}

/* News card */
.news-card {
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
  transition: 0.2s ease;
}

.news-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
}

.news-image-link {
  display: block;
  background: #f1f5f9;
}

.news-image {
  width: 100%;
  height: 150px;
  object-fit: cover;
}

.news-body {
  padding: 14px 14px 15px;
}

.news-title {
  display: -webkit-box;
  min-height: 46px;
  margin-bottom: 8px;
  overflow: hidden;
  color: #111827;
  text-decoration: none;
  font-size: 15px;
  line-height: 1.45;
  font-weight: 800;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.news-title:hover {
  color: #0d6efd;
}

.news-desc {
  display: -webkit-box;
  min-height: 57px;
  margin-bottom: 14px;
  overflow: hidden;
  color: #475569;
  font-size: 13px;
  line-height: 1.55;
  font-weight: 500;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
}

/* Sidebar */
.sidebar-box {
  padding: 18px;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  background: #fff;
}

.sidebar-box h3 {
  margin-bottom: 18px;
  color: #111827;
  font-size: 18px;
  font-weight: 800;
}

.popular-item {
  display: flex;
  gap: 13px;
}

.popular-item + .popular-item {
  margin-top: 16px;
}

.popular-image {
  flex: 0 0 78px;
  width: 78px;
  height: 58px;
  overflow: hidden;
  border-radius: 5px;
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
  color: #111827;
  text-decoration: none;
  font-size: 13px;
  line-height: 1.35;
  font-weight: 800;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

.popular-content a:hover {
  color: #0d6efd;
}

.popular-content span {
  display: block;
  margin-top: 5px;
  color: #64748b;
  font-size: 12px;
  font-weight: 600;
}

.topic-list {
  padding: 0;
  margin: 0;
  list-style: none;
}

.topic-list li {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 7px 0;
}

.topic-list a {
  color: #334155;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
}

.topic-list a:hover {
  color: #0d6efd;
}

.topic-list span {
  min-width: 36px;
  padding: 3px 8px;
  border: 1px solid #e5e7eb;
  border-radius: 999px;
  background: #f8fafc;
  color: #475569;
  text-align: center;
  font-size: 12px;
  font-weight: 700;
}

.view-all-topic {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 14px;
  color: #0d6efd;
  text-decoration: none;
  font-size: 14px;
  font-weight: 700;
}

.btn-load-more {
  min-width: 175px;
  height: 38px;
  border-radius: 5px;
  font-size: 14px;
  font-weight: 700;
}

/* Responsive */
@media (max-width: 991.98px) {
  .featured-content {
    min-height: auto;
  }

  .btn-read {
    position: static;
    margin-top: 18px;
  }
}

@media (max-width: 767.98px) {
  .news-page {
    padding-top: 20px;
  }

  .page-heading h1 {
    font-size: 24px;
  }

  .featured-image-wrap {
    height: 210px;
  }

  .featured-content {
    padding: 22px;
  }

  .featured-content h2 {
    font-size: 20px;
  }

  .news-image {
    height: 180px;
  }
}
</style>