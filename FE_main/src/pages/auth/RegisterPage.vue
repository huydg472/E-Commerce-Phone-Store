<script setup>
import { useRouter } from 'vue-router'
import RegisterForm from '@/components/auth/RegisterForm.vue'
import { authService } from '@/services/authService'

const router = useRouter()

async function handleRegister(form) {
  await authService.register(form)
  router.push('/auth/login')
}
</script>

<template>
  <section>
    <h1>Đăng ký</h1>
    <RegisterForm @submit="handleRegister" />
  </section>
</template>
