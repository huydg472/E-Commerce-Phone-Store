<script setup>
defineProps({
  form: {
    type: Object,
    required: true,
  },
  roles: {
    type: Array,
    default: () => [],
  },
  buttonText: {
    type: String,
    default: 'Lưu người dùng',
  },
})

defineEmits(['submit'])
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="form-group">
      <label>Họ tên</label>
      <input v-model="form.name" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>Email</label>
      <input v-model="form.email" class="form-control" type="email">
    </div>

    <div class="form-group">
      <label>Vai trò</label>
      <select v-model="form.role_id" class="form-control">
        <option value="">-- Chọn vai trò --</option>
        <option v-for="role in roles" :key="role.id" :value="role.id">
          {{ role.name }}
        </option>
      </select>
    </div>

    <button class="btn btn-primary" type="submit">{{ buttonText }}</button>
  </form>
</template>
