<script setup>
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue'

function handleSubmit(form) {
  console.log('forgot password form', form)
}
</script>

<template>
  <section>
    <h1>Quên mật khẩu</h1>
    <ForgotPasswordForm @submit="handleSubmit" />
  </section>
</template>
