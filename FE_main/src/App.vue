<script setup>
import {watch} from 'vue'
import ToastStack from '@/components/common/ToastStack.vue'
import {usePublicSiteSettings} from '@/composables/usePublicSiteSettings'

const {siteName: siteTitle, faviconUrl} = usePublicSiteSettings()

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

watch([siteTitle, faviconUrl], applyDocumentBranding, {immediate: true})
</script>

<template>
  <main>
    <RouterView/>
  </main>
  <ToastStack/>
</template>

<style scoped></style>
