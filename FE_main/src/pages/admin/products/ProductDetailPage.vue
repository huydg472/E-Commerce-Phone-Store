<script setup>
import {computed, onMounted, ref} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useProductStore} from '@/stores/productStore.js'
import {formatDate} from '@/utils/formatDate.js'

const route = useRoute()
const router = useRouter()
const productStore = useProductStore()
const {item: product, loading} = storeToRefs(productStore)

const loadingError = ref('')

const productId = computed(() => route.params.id)
const isActiveTab = (name) => route.name === name

const variantCount = computed(() => product.value?.product_variants?.length || product.value?.productVariants?.length || 0)
const descriptionText = computed(() => product.value?.description || product.value?.short_description || 'Chưa có mô tả.')

const loadProduct = async () => {
  loadingError.value = ''

  try {
    await productStore.fetchById(productId.value)
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được chi tiết sản phẩm.'
  }
}

onMounted(loadProduct)
</script>

<template>
  <div class="admin-page">
    <section class="page-head">
      <div>
        <p class="eyebrow">Quản lý sản phẩm</p>
        <h1>Chi tiết sản phẩm</h1>
        <p class="subtitle">Tổng quan đầy đủ về thông tin, trạng thái và nội dung hiển thị của sản phẩm.</p>
      </div>

      <div class="page-actions">
        <RouterLink :to="{ name: 'admin.products.edit', params: { id: productId } }" class="primary-action">
          <i class="bi bi-pencil"></i>
          Chỉnh sửa
        </RouterLink>
        <RouterLink to="/admin/products" class="secondary-action">
          <i class="bi bi-arrow-left"></i>
          Quay lại danh sách
        </RouterLink>
      </div>
    </section>

    <section class="page-tabs">
      <RouterLink :to="{ name: 'admin.products.show', params: { id: productId } }" class="tab-link"
                  :class="{ active: isActiveTab('admin.products.show') }">
        Thông tin
      </RouterLink>
      <RouterLink :to="{ name: 'admin.products.variants', params: { id: productId } }" class="tab-link"
                  :class="{ active: isActiveTab('admin.products.variants') }">
        Biến thể
      </RouterLink>
      <RouterLink :to="{ name: 'admin.products.specifications', params: { id: productId } }" class="tab-link"
                  :class="{ active: isActiveTab('admin.products.specifications') }">
        Thông số
      </RouterLink>
      <RouterLink :to="{ name: 'admin.products.images', params: { id: productId } }" class="tab-link"
                  :class="{ active: isActiveTab('admin.products.images') }">
        Hình ảnh
      </RouterLink>
    </section>

    <section v-if="loadingError" class="notice-card error">
      <i class="bi bi-exclamation-triangle"></i>
      <span>{{ loadingError }}</span>
    </section>

    <section v-else-if="loading && !product" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải chi tiết sản phẩm...</p>
    </section>

    <div v-else class="detail-layout">
      <section class="hero-panel">
        <div class="hero-image">
          <img :src="product?.thumbnail_url || '/images/default-product.png'" :alt="product?.name"/>
        </div>

        <div class="hero-body">
          <div class="hero-top">
            <div>
              <p class="hero-kicker">Mã #{{ product?.id }}</p>
              <h2>{{ product?.name }}</h2>
              <p class="hero-slug">{{ product?.slug }}</p>
            </div>

            <div class="hero-tags">
              <span class="status-tag" :class="product?.status === 'active' ? 'active' : 'inactive'">
                {{ product?.status === 'active' ? 'Đang hoạt động' : 'Tạm ẩn' }}
              </span>
            </div>
          </div>

          <div class="hero-metrics">
            <div class="metric-box">
              <span>Thương hiệu</span>
              <strong>{{ product?.brand?.name || 'Chưa có' }}</strong>
            </div>
            <div class="metric-box">
              <span>Danh mục</span>
              <strong>{{ product?.category?.name || 'Chưa có' }}</strong>
            </div>
            <div class="metric-box">
              <span>Biến thể</span>
              <strong>{{ variantCount }}</strong>
            </div>
          </div>

          <div class="hero-actions">
            <RouterLink :to="{ name: 'admin.products.variants', params: { id: productId } }" class="secondary-action">
              <i class="bi bi-list-check"></i>
              Biến thể
            </RouterLink>
            <RouterLink :to="{ name: 'admin.products.specifications', params: { id: productId } }"
                        class="secondary-action">
              <i class="bi bi-sliders"></i>
              Thông số
            </RouterLink>
            <RouterLink :to="{ name: 'admin.products.images', params: { id: productId } }" class="secondary-action">
              <i class="bi bi-images"></i>
              Hình ảnh
            </RouterLink>
          </div>
        </div>
      </section>

      <section class="info-grid">
        <article class="info-card">
          <h3>Thông tin chính</h3>
          <ul>
            <li><span>Tên</span><strong>{{ product?.name }}</strong></li>
            <li><span>Slug</span><strong>{{ product?.slug }}</strong></li>
            <li><span>Thương hiệu</span><strong>{{ product?.brand?.name || 'Chưa có' }}</strong></li>
            <li><span>Danh mục</span><strong>{{ product?.category?.name || 'Chưa có' }}</strong></li>
          </ul>
        </article>

        <article class="info-card">
          <h3>Nội dung mô tả</h3>
          <p>{{ descriptionText }}</p>
        </article>

        <article class="info-card">
          <h3>Hình ảnh & cập nhật</h3>
          <ul>
            <li><span>Ảnh đại diện</span><strong>{{ product?.thumbnail_url ? 'Đã thiết lập' : 'Chưa có' }}</strong></li>
            <li><span>Ngày tạo</span><strong>{{ formatDate(product?.created_at) }}</strong></li>
            <li><span>Cập nhật gần nhất</span><strong>{{ formatDate(product?.updated_at) }}</strong></li>
          </ul>
        </article>
      </section>
    </div>
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

.page-actions,
.hero-actions {
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

.notice-card,
.state-card,
.hero-panel,
.info-card {
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
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

.detail-layout {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-panel {
  padding: 18px;
  display: grid;
  grid-template-columns: minmax(280px, 0.85fr) minmax(0, 1.15fr);
  gap: 18px;
}

.hero-image {
  border-radius: 18px;
  overflow: hidden;
  background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
  min-height: 320px;
}

.hero-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-body {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-top {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
}

.hero-kicker {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.hero-top h2 {
  margin: 0;
  color: #0f172a;
  font-size: 28px;
  font-weight: 900;
  line-height: 1.15;
}

.hero-slug {
  margin: 8px 0 0;
  color: #64748b;
  font-size: 13px;
}

.hero-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  justify-content: flex-end;
}

.status-tag {
  min-height: 34px;
  padding: 0 12px;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  font-size: 12px;
  font-weight: 800;
}

.status-tag.active {
  background: #ecfdf5;
  color: #15803d;
}

.status-tag.inactive {
  background: #fff7ed;
  color: #c2410c;
}

.hero-metrics {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.metric-box {
  padding: 16px;
  border: 1px solid #edf2f7;
  border-radius: 16px;
  background: #fbfdff;
}

.metric-box span {
  display: block;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.metric-box strong {
  display: block;
  margin-top: 6px;
  color: #0f172a;
  font-size: 16px;
  font-weight: 900;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 18px;
}

.info-card {
  padding: 18px;
}

.info-card h3 {
  margin: 0 0 14px;
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
}

.info-card ul {
  margin: 0;
  padding: 0;
  list-style: none;
  display: grid;
  gap: 12px;
}

.info-card li {
  padding: 12px;
  border-radius: 14px;
  background: #f8fafc;
}

.info-card li span {
  display: block;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.info-card li strong,
.info-card p {
  display: block;
  margin-top: 6px;
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
  line-height: 1.6;
}

.info-card p {
  margin: 0;
  font-weight: 600;
  color: #334155;
}

@media (max-width: 1200px) {
  .hero-panel {
    grid-template-columns: 1fr;
  }

  .info-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 992px) {
  .page-head,
  .hero-top {
    flex-direction: column;
  }
}

@media (max-width: 768px) {
  .hero-panel,
  .info-card {
    padding: 16px;
  }

  .page-tabs {
    padding: 6px;
  }

  .tab-link {
    flex: 1 1 calc(50% - 4px);
    justify-content: center;
  }

  .hero-metrics {
    grid-template-columns: 1fr;
  }

  .page-actions,
  .hero-actions {
    width: 100%;
  }

  .secondary-action,
  .primary-action {
    width: 100%;
    justify-content: center;
  }
}
</style>
