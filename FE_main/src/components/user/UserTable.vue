<script setup>
defineProps({
  users: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Họ tên</th>
        <th>Email</th>
        <th>Vai trò</th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="user in users" :key="user.id">
        <td>{{ user.id }}</td>
        <td>{{ user.name }}</td>
        <td>{{ user.email }}</td>
        <td>{{ user.role?.name || user.role_id }}</td>
      </tr>
    </tbody>
  </table>
</template>
