<script setup>
defineProps({
  brands: {
    type: Array,
    default: () => [],
  },
})

defineEmits(['edit', 'delete'])
</script>

<template>
  <table class="table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Logo</th>
        <th>Tên thương hiệu</th>
        <th>Trạng thái</th>
        <th>Hành động</th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="brand in brands" :key="brand.id">
        <td>{{ brand.id }}</td>
        <td>
          <img v-if="brand.logo_url" :src="brand.logo_url" :alt="brand.name" width="40">
        </td>
        <td>{{ brand.name }}</td>
        <td>{{ brand.status }}</td>
        <td class="actions">
          <button @click="$emit('edit', brand)">Sửa</button>
          <button @click="$emit('delete', brand.id)">Xoá</button>
        </td>
      </tr>
    </tbody>
  </table>
</template>
