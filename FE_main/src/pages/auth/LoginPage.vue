<script setup>
import { useRouter } from 'vue-router'
import LoginForm from '@/components/auth/LoginForm.vue'
import { useAuthStore } from '@/stores/authStore'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogin(form) {
  await authStore.login(form)
  router.push('/admin/dashboard')
}
</script>

<template>
  <section>
    <h1>Đăng nhập</h1>
    <LoginForm @submit="handleLogin" />

    <p>
      Chưa có tài khoản?
      <router-link to="/auth/register">Đăng ký</router-link>
    </p>
  </section>
</template>
