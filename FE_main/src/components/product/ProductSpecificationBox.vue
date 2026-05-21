<script setup>
defineProps({
  specifications: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="specification-box">
    <h3>Thông số kỹ thuật</h3>

    <table class="table">
      <tbody>
        <tr v-for="item in specifications" :key="item.id">
          <th>{{ item.name }}</th>
          <td>{{ item.value || item.description }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
