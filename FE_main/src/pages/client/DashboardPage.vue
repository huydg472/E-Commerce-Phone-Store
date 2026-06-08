<script setup>
import {onMounted} from 'vue'
import {useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

onMounted(async () => {
  if (!authStore.isLoggedIn) {
    await router.replace({name: 'login'})
    return
  }

  if (!authStore.user) {
    await authStore.fetchMe().catch(() => {})
  }

  if (authStore.isAdmin || authStore.isStaff) {
    await router.replace('/admin/dashboard')
    return
  }

  await router.replace('/')
})
</script>

<template>
  <div class="d-flex min-vh-100 align-items-center justify-content-center">
    <div class="text-center">
      <div class="spinner-border text-primary mb-3" role="status"></div>
      <div>Đang chuyển hướng...</div>
    </div>
  </div>
</template>
