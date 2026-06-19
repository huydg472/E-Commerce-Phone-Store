<script setup>
import {computed, watch} from 'vue'
import {useRouter} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useAuthStore} from '@/stores/authStore.js'
import {useCartStore} from '@/stores/cartStore'
import {useFavoriteStore} from '@/stores/favoriteStore'
import {usePublicSiteSettings} from '@/composables/usePublicSiteSettings'
import HeaderSearchBar from '@/components/layout/HeaderSearchBar.vue'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()
const favoriteStore = useFavoriteStore()
const {siteName, logoUrl, slogan} = usePublicSiteSettings()

const {items: cartItems, item: cartData} = storeToRefs(cartStore)
const {variantIds: favoriteVariantIds} = storeToRefs(favoriteStore)

const isLoggedIn = computed(() => authStore.isLoggedIn)


const cartQuantity = computed(() => {
  const items = cartData.value?.items ?? cartItems.value ?? []
  return Array.isArray(items) ? items.length : 0
})

const favoriteQuantity = computed(() => {
  return Array.isArray(favoriteVariantIds.value) ? favoriteVariantIds.value.length : 0
})

const displayName = computed(() => {
  return authStore.user?.['name'] || authStore.user?.['username'] || 'Tài khoản'
})

const handleLogout = async () => {
  await authStore.logout()
  favoriteStore.clear()
  await router.replace('/auth/login')
}

const refreshCart = async () => {
  if (!authStore.isLoggedIn) {
    cartStore.items = []
    cartStore.item = null
    return
  }

  try {
    await cartStore.fetchAll()
  } catch (error) {
    cartStore.items = []
    cartStore.item = null
  }
}

const refreshFavorites = async () => {
  if (!authStore.isLoggedIn) {
    favoriteStore.clear()
    return
  }

  await favoriteStore.ensureLoaded().catch(() => {
  })
}

watch(isLoggedIn, () => {
  refreshCart()
  refreshFavorites()
}, {immediate: true})
</script>
<template>
  <header class="site-header">
    <div class="container-fluid px-4">
      <div class="header-top">
        <RouterLink to="/" class="logo-wrap">
          <div class="logo-icon">
            <img v-if="logoUrl" :src="logoUrl" :alt="siteName" class="logo-image"/>
            <i v-else class="bi bi-bag"></i>
          </div>

          <span class="logo-text">
            {{ siteName }}
            <small>{{ slogan }}</small>
          </span>
        </RouterLink>

        <HeaderSearchBar/>

        <div class="header-actions">
          <RouterLink v-if="!isLoggedIn" to="/auth/login" class="header-action">
            <i class="bi bi-person"></i>
            <span>
              Tài khoản<br/>
              Đăng nhập
            </span>
          </RouterLink>

          <div v-else class="dropdown account-dropdown">
            <button class="header-action account-btn dropdown-toggle" type="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
              <i class="bi bi-person-circle"></i>
              <div class="account-info">
                <span>Xin chào</span>
                <strong>{{ displayName }}</strong>
              </div>
            </button>

            <ul class="dropdown-menu account-menu">
              <li>
                <RouterLink class="dropdown-item" :to="{ name: 'profile' }">
                  <i class="bi bi-person me-2"></i>
                  Tài khoản của tôi
                </RouterLink>
              </li>

              <li>
                <RouterLink class="dropdown-item" :to="{ name: 'orders.history' }">
                  <i class="bi bi-bag-check me-2"></i>
                  Đơn hàng của tôi
                </RouterLink>
              </li>

              <li>
                <RouterLink class="dropdown-item" :to="{ name: 'favorites' }">
                  <i class="bi bi-heart me-2"></i>
                  Sản phẩm yêu thích
                </RouterLink>
              </li>

              <li>
                <hr class="dropdown-divider"/>
              </li>

              <li>
                <button class="dropdown-item text-danger" type="button" @click="handleLogout">
                  <i class="bi bi-box-arrow-right me-2"></i>
                  Đăng xuất
                </button>
              </li>
            </ul>
          </div>

          <RouterLink
              :to="isLoggedIn ? { name: 'favorites' } : '/auth/login'"
              class="header-action favorite-action"
          >
            <i class="bi bi-heart"></i>
            <span>Yêu thích</span>
            <em v-if="isLoggedIn">{{ favoriteQuantity }}</em>
          </RouterLink>

          <RouterLink
              :to="isLoggedIn ? { name: 'cart' } : '/auth/login'"
              class="header-action cart-action"
          >
            <i class="bi bi-cart3"></i>
            <span>Giỏ hàng</span>
            <em>{{ cartQuantity }}</em>
          </RouterLink>
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
.site-header {
  background: var(--card-bg);
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 0;
  z-index: 100;
}

.header-top {
  min-height: 66px;
  display: grid;
  grid-template-columns: 300px minmax(360px, 1fr) 620px;
  align-items: center;
  gap: 24px;
  padding: 8px 0;
}

.logo-wrap {
  display: inline-flex;
  align-items: center;
  gap: 12px;
  color: var(--text-color);
  font-weight: 700;
  white-space: nowrap;
  text-decoration: none;
}

.logo-icon {
  width: 42px;
  height: 42px;
  border-radius: 10px;
  background: var(--primary-color);
  color: #ffffff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 23px;
  flex-shrink: 0;
  overflow: hidden;
}

.logo-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.logo-text {
  display: flex;
  flex-direction: column;
  gap: 2px;
  font-size: 20px;
  line-height: 1;
  letter-spacing: 1px;
  max-width: 220px;
  white-space: normal;
}

.logo-text small {
  color: var(--muted-color);
  font-size: 12px;
  line-height: 1.3;
  letter-spacing: 0;
}

.logo-text strong {
  color: var(--primary-color);
}

.search-box {
  position: relative;
  width: min(100%, 720px);
  height: 38px;
  display: grid;
  grid-template-columns: 1fr 128px;
  justify-self: center;
}

.search-box input {
  border: 1px solid var(--border-color);
  border-right: none;
  border-radius: 8px 0 0 8px;
  padding: 0 16px;
  outline: none;
  font-size: 13px;
  background: var(--card-bg);
  color: var(--text-color);
}

.search-box input:focus {
  border-color: var(--primary-color);
}

.search-box button {
  border: none;
  background: var(--primary-color);
  color: #ffffff;
  border-radius: 0 8px 8px 0;
  font-weight: 700;
  font-size: 13px;
}

.search-box button:hover {
  background: var(--primary-hover);
}

.search-suggestions {
  position: absolute;
  top: calc(100% + 8px);
  left: 0;
  right: 128px;
  max-height: 360px;
  overflow-y: auto;
  border: 1px solid var(--border-color);
  border-radius: 14px;
  background: #ffffff;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
  z-index: 20;
}

.search-suggestion {
  display: grid;
  grid-template-columns: 56px 1fr;
  gap: 12px;
  align-items: center;
  padding: 10px 12px;
  color: var(--text-color);
  text-decoration: none;
  border-bottom: 1px solid #eef2f7;
}

.search-suggestion:last-child {
  border-bottom: none;
}

.search-suggestion:hover {
  background: #f8fbff;
}

.suggestion-image {
  width: 56px;
  height: 56px;
  object-fit: cover;
  border-radius: 10px;
  background: #f8fafc;
}

.suggestion-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
  min-width: 0;
}

.suggestion-info strong {
  font-size: 13px;
  font-weight: 700;
  line-height: 1.4;
  color: #0f172a;
}

.suggestion-info span {
  font-size: 12px;
  color: #64748b;
}

.suggestion-info em {
  font-size: 12px;
  font-style: normal;
  font-weight: 800;
  color: #0d6efd;
}

.header-actions {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 26px;
  overflow: visible;
}

.header-action {
  min-height: 42px;
  color: var(--text-color);
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  line-height: 1.25;
  font-weight: 600;
  position: relative;
  white-space: nowrap;
  overflow: visible;
  text-decoration: none;
}

.header-action i {
  font-size: 28px;
  line-height: 1;
  color: var(--text-color);
}

.header-action:hover,
.header-action:hover i {
  color: var(--primary-color);
}

.cart-action {
  order: 2;
  padding-right: 12px;
}

.favorite-action {
  order: 1;
  padding-right: 12px;
}

.header-action em {
  position: absolute;
  top: -6px;
  right: -6px;
  width: 20px;
  height: 20px;
  border: 2px solid #ffffff;
  border-radius: 999px;
  background: var(--primary-color);
  color: #ffffff;
  font-size: 11px;
  font-style: normal;
  font-weight: 700;
  line-height: 16px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  z-index: 3;
}

.header-action span {
  display: block;
  font-size: 14px;
  color: #111827;
  line-height: 1.1;
}

.header-action strong {
  display: block;
  font-size: 15px;
  font-weight: 700;
  color: #111827;
}

.account-btn {
  cursor: pointer;
}

.dropdown-toggle::after {
  display: none;
}

.account-dropdown {
  position: relative;
  order: 3;
}

.account-btn {
  border: none;
  background: transparent;
  padding: 0;
  cursor: pointer;
  text-align: left;
}

.account-btn:focus {
  outline: none;
  box-shadow: none;
}

.account-btn:hover .account-info span,
.account-btn:hover .account-info strong,
.account-btn:hover i {
  color: var(--primary-color);
}

.account-info span {
  display: block;
  font-size: 13px;
  color: #111827;
  line-height: 1.2;
  font-weight: 500;
}

.account-info strong {
  display: block;
  font-size: 14px;
  color: #111827;
  line-height: 1.2;
  font-weight: 700;
  max-width: 110px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.account-menu {
  min-width: 210px;
  padding: 10px;
  margin-top: 12px;
  border: 1px solid var(--border-color);
  border-radius: 12px;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.12);
}

.account-menu .dropdown-item {
  display: flex;
  align-items: center;
  border-radius: 8px;
  padding: 9px 12px;
  color: #111827;
  font-size: 14px;
  font-weight: 500;
}

.account-menu .dropdown-item:hover {
  background: #eef5ff;
  color: var(--primary-color);
}

.account-menu .dropdown-divider {
  margin: 6px 0;
}

.dropdown-toggle::after {
  display: none;
}

@media (max-width: 1200px) {
  .header-top {
    grid-template-columns: 240px 1fr;
  }

  .header-actions {
    grid-column: 1 / -1;
    justify-content: center;
    padding-bottom: 12px;
  }
}

@media (max-width: 900px) {
  .header-top {
    grid-template-columns: 1fr;
    gap: 14px;
  }

  .header-actions {
    flex-wrap: wrap;
    justify-content: center;
    gap: 18px;
  }

  .search-box {
    grid-template-columns: 1fr;
    height: auto;
    gap: 8px;
    width: 100%;
  }

  .search-box input,
  .search-box button {
    height: 42px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
  }

  .search-suggestions {
    right: 0;
  }

  .search-suggestion {
    grid-template-columns: 48px 1fr;
  }
}

@media (max-width: 576px) {
  .logo-text {
    font-size: 18px;
  }

  .header-actions {
    align-items: flex-start;
  }

  .header-action i {
    font-size: 24px;
  }
}
</style>


