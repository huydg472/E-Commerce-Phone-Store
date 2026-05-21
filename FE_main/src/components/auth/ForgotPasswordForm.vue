<script setup>
import { ref } from 'vue'

const emit = defineEmits(['submit'])
const email = ref('')

function submitForm() {
  emit('submit', { email: email.value })
}
</script>

<template>
  <form @submit.prevent="submitForm">
    <div class="form-group">
      <label>Email</label>
      <input v-model="email" class="form-control" type="email">
    </div>

    <button class="btn btn-primary" type="submit">Gửi yêu cầu</button>
  </form>
</template>
