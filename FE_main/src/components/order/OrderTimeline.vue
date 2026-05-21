<script setup>
defineProps({
  logs: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <ul>
    <li v-for="log in logs" :key="log.id">
      {{ log.status }} - {{ log.created_at }}
    </li>
  </ul>
</template>
