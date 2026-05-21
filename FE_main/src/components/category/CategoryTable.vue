<script setup>
defineProps({
  categories: {
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
        <th>Tên danh mục</th>
        <th>Trạng thái</th>
        <th>Hành động</th>
      </tr>
    </thead>

    <tbody>
      <tr v-for="category in categories" :key="category.id">
        <td>{{ category.id }}</td>
        <td>{{ category.name }}</td>
        <td>{{ category.status }}</td>
        <td class="actions">
          <button @click="$emit('edit', category)">Sửa</button>
          <button @click="$emit('delete', category.id)">Xoá</button>
        </td>
      </tr>
    </tbody>
  </table>
</template>
