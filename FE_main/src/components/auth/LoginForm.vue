<script setup>
import { reactive } from 'vue'

const emit = defineEmits(['submit'])

const form = reactive({
  email: '',
  password: '',
})

function submitForm() {
  emit('submit', form)
}
</script>

<template>
  <form @submit.prevent="submitForm">
    <div class="form-group">
      <label>Email</label>
      <input v-model="form.email" class="form-control" type="email">
    </div>

    <div class="form-group">
      <label>Mật khẩu</label>
      <input v-model="form.password" class="form-control" type="password">
    </div>

    <button class="btn btn-primary" type="submit">
      Đăng nhập
    </button>
  </form>
</template>
