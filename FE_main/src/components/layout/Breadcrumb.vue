<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="breadcrumb">
    <template v-for="(item, index) in items" :key="item.text">
      <router-link v-if="item.to" :to="item.to">{{ item.text }}</router-link>
      <span v-else>{{ item.text }}</span>
      <span v-if="index < items.length - 1"> / </span>
    </template>
  </div>
</template>
