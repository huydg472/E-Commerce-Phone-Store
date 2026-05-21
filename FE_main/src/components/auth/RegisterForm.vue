<script setup>
import { reactive } from 'vue'

const emit = defineEmits(['submit'])

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

function submitForm() {
  emit('submit', form)
}
</script>

<template>
  <form @submit.prevent="submitForm">
    <div class="form-group">
      <label>Họ tên</label>
      <input v-model="form.name" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>Email</label>
      <input v-model="form.email" class="form-control" type="email">
    </div>

    <div class="form-group">
      <label>Mật khẩu</label>
      <input v-model="form.password" class="form-control" type="password">
    </div>

    <div class="form-group">
      <label>Nhập lại mật khẩu</label>
      <input v-model="form.password_confirmation" class="form-control" type="password">
    </div>

    <button class="btn btn-primary" type="submit">Đăng ký</button>
  </form>
</template>
