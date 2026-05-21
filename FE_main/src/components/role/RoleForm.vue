<script setup>
defineProps({
  form: {
    type: Object,
    required: true,
  },
  permissions: {
    type: Array,
    default: () => [],
  },
})

defineEmits(['submit'])
</script>

<template>
  <form @submit.prevent="$emit('submit')">
    <div class="form-group">
      <label>Tên vai trò</label>
      <input v-model="form.name" class="form-control" type="text">
    </div>

    <div class="form-group">
      <label>Mô tả</label>
      <textarea v-model="form.description" class="form-control" />
    </div>

    <div class="form-group">
      <label>Quyền</label>
      <label v-for="permission in permissions" :key="permission.id">
        <input v-model="form.permission_ids" type="checkbox" :value="permission.id">
        {{ permission.name }}
      </label>
    </div>

    <button class="btn btn-primary" type="submit">Lưu vai trò</button>
  </form>
</template>
