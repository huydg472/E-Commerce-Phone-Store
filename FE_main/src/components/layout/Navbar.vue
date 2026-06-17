<script setup>
import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue'
import {storeToRefs} from 'pinia'
import {useRoute} from 'vue-router'
import {useBrandStore} from '@/stores/brandStore.js'
import {useCategoryStore} from '@/stores/categoryStore.js'

const route = useRoute()
const brandStore = useBrandStore()
const categoryStore = useCategoryStore()

const {items: brands} = storeToRefs(brandStore)
const {items: categories} = storeToRefs(categoryStore)

const menuOpen = ref(false)
const menuRoot = ref(null)

const brandPriority = ['Apple', 'Samsung', 'OPPO', 'Xiaomi', 'Vivo', 'Realme']

const normalizedCategories = computed(() => {
  const list = Array.isArray(categories.value) ? categories.value : []

  return [...list]
      .filter((category) => category?.status !== 'inactive')
      .sort((left, right) => String(left?.name ?? '').localeCompare(String(right?.name ?? ''), 'vi', {sensitivity: 'base'}))
})

const highlightedCategories = computed(() => normalizedCategories.value.slice(0, 8))
const featuredBrands = computed(() => {
  const list = Array.isArray(brands.value) ? brands.value : []

  return [...list]
      .sort((left, right) => {
        const leftIndex = brandPriority.indexOf(String(left?.name ?? '').trim())
        const rightIndex = brandPriority.indexOf(String(right?.name ?? '').trim())

        const safeLeftIndex = leftIndex === -1 ? Number.POSITIVE_INFINITY : leftIndex
        const safeRightIndex = rightIndex === -1 ? Number.POSITIVE_INFINITY : rightIndex

        if (safeLeftIndex !== safeRightIndex) {
          return safeLeftIndex - safeRightIndex
        }

        return String(left?.name ?? '').localeCompare(String(right?.name ?? ''), 'vi', {sensitivity: 'base'})
      })
      .slice(0, 6)
})

const quickLinks = [
  {
    label: 'Sản phẩm mới',
    description: 'Xem các mẫu mới nhất',
    to: {name: 'products.index'},
    icon: 'bi-stars',
  },
  {
    label: 'Phụ kiện',
    description: 'Ốp lưng, sạc, tai nghe',
    to: {name: 'products.accessories'},
    icon: 'bi-headphones',
  },
  {
    label: 'Tin tức',
    description: 'Bài viết và đánh giá',
    to: {name: 'news'},
    icon: 'bi-newspaper',
  },
]

const toggleMenu = () => {
  menuOpen.value = !menuOpen.value
}

const closeMenu = () => {
  menuOpen.value = false
}

const handleDocumentClick = (event) => {
  if (!menuRoot.value) {
    return
  }

  if (!menuRoot.value.contains(event.target)) {
    closeMenu()
  }
}

watch(
    () => route.fullPath,
    () => {
      closeMenu()
    }
)

onMounted(() => {
  categoryStore.fetchAll({status: 'active'}).catch(() => {
  })
  brandStore.fetchAll({status: 'active'}).catch(() => {
  })
  document.addEventListener('click', handleDocumentClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
})
</script>

<template>
  <div ref="menuRoot" class="navbar-wrap">
    <div class="container-fluid px-4">
      <div class="header-nav">
        <button
            class="category-btn"
            :class="{ active: menuOpen }"
            type="button"
            @click.stop="toggleMenu"
        >
          <i class="bi bi-list"></i>
          <span>Danh mục sản phẩm</span>
          <i class="bi bi-chevron-down category-caret" :class="{ open: menuOpen }"></i>
        </button>

        <nav class="main-menu">
          <RouterLink
              v-slot="{ href, navigate, isExactActive }"
              to="/"
              custom
          >
            <a :href="href" :class="{ 'is-active': isExactActive }" @click="navigate">Trang chủ</a>
          </RouterLink>

          <RouterLink
              v-slot="{ href, navigate, isExactActive }"
              to="/products"
              custom
          >
            <a :href="href" :class="{ 'is-active': isExactActive }" @click="navigate">Sản phẩm</a>
          </RouterLink>

          <RouterLink
              v-slot="{ href, navigate, isExactActive }"
              to="/phu-kien"
              custom
          >
            <a :href="href" :class="{ 'is-active': isExactActive }" @click="navigate">Phụ kiện</a>
          </RouterLink>

          <RouterLink
              v-slot="{ href, navigate, isExactActive }"
              to="/tin-tuc"
              custom
          >
            <a :href="href" :class="{ 'is-active': isExactActive }" @click="navigate">Tin tức</a>
          </RouterLink>

          <RouterLink
              v-slot="{ href, navigate, isExactActive }"
              to="/lien-he"
              custom
          >
            <a :href="href" :class="{ 'is-active': isExactActive }" @click="navigate">Liên hệ</a>
          </RouterLink>
        </nav>
      </div>
    </div>

    <transition name="mega-fade">
      <div v-if="menuOpen" class="mega-menu-panel">
        <div class="container-fluid px-4">
          <div class="mega-menu-card">
            <div class="mega-menu-grid">
              <section class="mega-column mega-category">
                <div class="mega-title-row">
                  <h3>Danh mục nổi bật</h3>
                </div>

                <div class="mega-list">
                  <RouterLink
                      v-for="category in highlightedCategories"
                      :key="category.id"
                      :to="{ name: 'products.index', query: { category: category.slug } }"
                      class="mega-link"
                  >
                    <div class="mega-link-icon">
                      <i class="bi bi-grid-1x2"></i>
                    </div>
                    <div class="mega-link-content">
                      <strong>{{ category.name }}</strong>
                      <span>Xem sản phẩm theo danh mục</span>
                    </div>
                  </RouterLink>
                </div>
              </section>

              <section class="mega-column">
                <div class="mega-title-row">
                  <h3>Thương hiệu hot</h3>
                </div>

                <div class="tag-grid">
                  <RouterLink
                      v-for="brand in featuredBrands"
                      :key="brand.id"
                      :to="{ name: 'products.index', query: { brand: brand.slug } }"
                      class="tag-pill"
                  >
                    {{ brand.name }}
                  </RouterLink>
                </div>

              </section>

              <section class="mega-column">
                <div class="mega-title-row">
                  <h3>Lối tắt nhanh</h3>
                </div>

                <div class="quick-list">
                  <RouterLink
                      v-for="item in quickLinks"
                      :key="item.label"
                      :to="item.to"
                      class="quick-link"
                  >
                    <div class="quick-link-icon">
                      <i :class="`bi ${item.icon}`"></i>
                    </div>
                    <div class="quick-link-content">
                      <strong>{{ item.label }}</strong>
                      <span>{{ item.description }}</span>
                    </div>
                  </RouterLink>
                </div>
              </section>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style scoped>
.navbar-wrap {
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  border-top: 1px solid #eef2f7;
  border-bottom: 1px solid #dbe3ef;
  box-shadow: 0 8px 22px rgba(15, 23, 42, 0.035);
  position: sticky;
  top: 66px;
  z-index: 99;
}

.header-nav {
  min-height: 58px;
  display: grid;
  grid-template-columns: 270px minmax(0, 1fr);
  align-items: center;
  gap: 28px;
  padding: 9px 0;
}

.category-btn {
  height: 42px;
  border: none;
  border-radius: 999px;
  background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
  color: #1d4ed8;
  font-weight: 800;
  font-size: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: flex-start;
  gap: 10px;
  padding: 0 16px;
  width: 100%;
  max-width: 260px;
  border: 1px solid #bfdbfe;
  box-shadow: 0 8px 18px rgba(37, 99, 235, 0.08);
  transform: translateZ(0);
  will-change: transform, box-shadow;
  transition: border-color 0.24s cubic-bezier(0.22, 1, 0.36, 1),
  color 0.24s cubic-bezier(0.22, 1, 0.36, 1),
  box-shadow 0.24s cubic-bezier(0.22, 1, 0.36, 1),
  transform 0.24s cubic-bezier(0.22, 1, 0.36, 1);
}

.category-btn.active {
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
  border-color: #2563eb;
  box-shadow: 0 10px 22px rgba(37, 99, 235, 0.22);
}

.category-btn:hover {
  border-color: #2563eb;
  box-shadow: 0 12px 24px rgba(37, 99, 235, 0.14);
  transform: translate3d(0, -1px, 0);
}

.category-btn i {
  font-size: 18px;
}

.category-caret {
  margin-left: auto;
  transition: transform 0.24s cubic-bezier(0.22, 1, 0.36, 1);
  font-size: 12px;
}

.category-caret.open {
  transform: rotate(180deg);
}

.main-menu {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 14px;
  max-width: 100%;
}

.main-menu a {
  min-height: 38px;
  padding: 0 18px;
  border-radius: 999px;
  color: #0f172a;
  font-weight: 800;
  font-size: 15px;
  line-height: 38px;
  text-decoration: none;
  position: relative;
  isolation: isolate;
  overflow: hidden;
  transform: translateZ(0);
  transition: color 0.22s cubic-bezier(0.22, 1, 0.36, 1);
}

.main-menu a::before {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: inherit;
  background: #eff6ff;
  box-shadow: inset 0 0 0 1px #bfdbfe;
  opacity: 0;
  transform: scale(0.86);
  transition: opacity 0.22s cubic-bezier(0.22, 1, 0.36, 1),
  transform 0.22s cubic-bezier(0.22, 1, 0.36, 1);
  z-index: -1;
}

.main-menu a.is-active,
.main-menu a:hover {
  color: #1d4ed8;
}

.main-menu a.is-active::before,
.main-menu a:hover::before {
  opacity: 1;
  transform: scale(1);
}

.mega-menu-panel {
  position: absolute;
  left: 0;
  right: 0;
  top: calc(100% + 10px);
  padding-bottom: 12px;
}

.mega-menu-card {
  border: 1px solid #e5e7eb;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.98);
  box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
  overflow: hidden;
}

.mega-menu-grid {
  display: grid;
  grid-template-columns: 1.25fr 1fr 0.95fr;
}

.mega-column {
  padding: 20px 18px 18px;
}

.mega-column + .mega-column {
  border-left: 1px solid #eef2f7;
}

.mega-title-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
}

.mega-title-row h3 {
  margin: 0;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
}

.mega-list,
.quick-list {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.mega-link,
.quick-link {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 11px 12px;
  border-radius: 14px;
  text-decoration: none;
  color: inherit;
  transition: transform 0.2s ease, background-color 0.2s ease;
}

.mega-link:hover,
.quick-link:hover {
  background: #f8fbff;
  transform: translateY(-1px);
}

.mega-link-icon,
.quick-link-icon {
  width: 38px;
  height: 38px;
  border-radius: 12px;
  background: #eff6ff;
  color: #2563eb;
  display: grid;
  place-items: center;
  flex-shrink: 0;
}

.quick-link-icon {
  background: #f8fafc;
  color: #0f172a;
}

.mega-link-content,
.quick-link-content {
  min-width: 0;
}

.mega-link-content strong,
.quick-link-content strong {
  display: block;
  margin-bottom: 2px;
  color: #111827;
  font-size: 14px;
  font-weight: 800;
}

.mega-link-content span,
.quick-link-content span {
  color: #64748b;
  font-size: 12px;
  line-height: 1.4;
}

.tag-grid {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.tag-pill {
  min-height: 34px;
  padding: 0 14px;
  border-radius: 999px;
  border: 1px solid #dbe3ef;
  color: #334155;
  text-decoration: none;
  font-size: 13px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  background: #ffffff;
}

.tag-pill:hover {
  border-color: #2563eb;
  color: #2563eb;
}

.mega-fade-enter-active,
.mega-fade-leave-active {
  transition: opacity 0.22s cubic-bezier(0.22, 1, 0.36, 1),
  transform 0.22s cubic-bezier(0.22, 1, 0.36, 1);
}

.mega-fade-enter-from,
.mega-fade-leave-to {
  opacity: 0;
  transform: translate3d(0, -8px, 0) scale(0.985);
}

@media (max-width: 1200px) {
  .navbar-wrap {
    top: 130px;
  }

  .header-nav {
    gap: 18px;
  }

  .main-menu {
    gap: 10px;
  }

  .mega-menu-grid {
    grid-template-columns: 1fr 1fr;
  }

  .mega-column:last-child {
    grid-column: 1 / -1;
  }
}

@media (max-width: 900px) {
  .navbar-wrap {
    position: static;
  }

  .header-nav {
    grid-template-columns: 1fr;
    gap: 16px;
    padding-bottom: 12px;
  }

  .main-menu {
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    max-width: 100%;
  }

  .mega-menu-panel {
    position: static;
    padding-bottom: 0;
    margin-top: 8px;
  }

  .mega-menu-grid {
    grid-template-columns: 1fr;
  }

  .mega-column + .mega-column {
    border-left: none;
    border-top: 1px solid #eef2f7;
  }
}

@media (max-width: 576px) {
  .main-menu {
    justify-content: flex-start;
  }

  .category-btn {
    max-width: 100%;
  }

  .mega-column {
    padding: 16px;
  }
}
</style>
