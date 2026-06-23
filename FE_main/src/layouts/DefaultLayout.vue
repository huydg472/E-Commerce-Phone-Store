<script setup>
import Footer from '@/components/layout/Footer.vue';
import Header from '@/components/layout/Header.vue';
import Navbar from "@/components/layout/Navbar.vue";
import {onMounted} from 'vue'
import {useProductStore} from '@/stores/productStore.js'
import {useBrandStore} from '@/stores/brandStore.js'

const productStore = useProductStore()
const brandStore = useBrandStore()

onMounted(() => {
  if (!productStore.items.length) {
    void productStore.fetchAll({status: 'active', per_page: 500}).catch(() => {})
  }

  void brandStore.fetchAll({status: 'active', type: 'phone'}).catch(() => {})
  void brandStore.fetchAll({status: 'active', type: 'accessory'}).catch(() => {})

  const prefetchRoutes = () => {
    void import('@/pages/client/ProductListPage.vue').catch(() => {})
    void import('@/pages/client/AccessoriesPage.vue').catch(() => {})
    void import('@/pages/client/ProductDetailPage.vue').catch(() => {})
  }

  if (typeof window !== 'undefined') {
    if (typeof window.requestIdleCallback === 'function') {
      window.requestIdleCallback(prefetchRoutes, {timeout: 2000})
    } else {
      window.setTimeout(prefetchRoutes, 0)
    }
  }
})

</script>

<template>
  <Header/>

  <Navbar/>

  <main>
    <RouterView/>
  </main>

  <Footer/>
</template>

<style scoped>
.default-layout {
  min-height: 100vh;
  background: var(--card-bg);
}
</style>
