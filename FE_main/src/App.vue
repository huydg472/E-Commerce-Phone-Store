<script setup>
import {computed, onBeforeUnmount, onMounted, watch} from 'vue'
import {useRoute} from 'vue-router'
import {useAuthStore} from '@/stores/authStore'
import ToastStack from '@/components/common/ToastStack.vue'
import {usePublicSiteSettings} from '@/composables/usePublicSiteSettings'
import MaintenancePage from '@/pages/error/MaintenancePage.vue'
import {useSettingsStore} from '@/stores/settingsStore'

const authStore = useAuthStore()
const settingsStore = useSettingsStore()
const route = useRoute()
const {siteName: siteTitle, faviconUrl, maintenanceMode} = usePublicSiteSettings()
const SETTINGS_SYNC_KEY = 'zinmobile:settings-sync-ts'

const isAdminBypass = computed(() => authStore.isAdminOrStaff)
const showMaintenancePage = computed(() => {
  if (!maintenanceMode.value) {
    return false
  }

  if (route.meta?.requiresAdmin) {
    return false
  }

  if (isAdminBypass.value) {
    return false
  }

  return !route.meta?.allowDuringMaintenance
})

const applyDocumentBranding = () => {
  if (typeof document === 'undefined') {
    return
  }

  document.title = siteTitle.value

  let iconLink = document.querySelector('link[rel="icon"]')

  if (!iconLink) {
    iconLink = document.createElement('link')
    iconLink.rel = 'icon'
    document.head.appendChild(iconLink)
  }

  iconLink.href = faviconUrl.value
}

const refreshSettings = async () => {
  await settingsStore.fetchPublic(true).catch(() => {
  })
}

const handleStorageSync = (event) => {
  if (event.key !== SETTINGS_SYNC_KEY) {
    return
  }

  void refreshSettings()
}

onMounted(() => {
  window.addEventListener('storage', handleStorageSync)
})

onBeforeUnmount(() => {
  window.removeEventListener('storage', handleStorageSync)
})

watch([siteTitle, faviconUrl], applyDocumentBranding, {immediate: true})
</script>

<template>
  <main>
    <MaintenancePage v-if="showMaintenancePage"/>
    <RouterView v-else/>
  </main>
  <ToastStack/>
</template>

<style scoped></style>
