<script setup>
defineProps({
  items: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <nav class="breadcrumb-wrap" aria-label="breadcrumb">
    <ol class="breadcrumb mb-0">
      <li
          v-for="(item, index) in items"
          :key="index"
          class="breadcrumb-item"
          :class="{ active: index === items.length - 1 }"
      >
        <router-link
            v-if="item.to && index !== items.length - 1"
            :to="item.to"
        >
          {{ item.label }}
        </router-link>

        <span v-else>
          {{ item.label }}
        </span>
      </li>
    </ol>
  </nav>
</template>

<style scoped>
.breadcrumb-wrap {
  margin-bottom: 8px;
}

.breadcrumb-item {
  font-size: 14px;
  font-weight: 500;
}

.breadcrumb-item a {
  color: #64748b;
  text-decoration: none;
}

.breadcrumb-item.active span {
  color: #0d6efd;
  font-weight: 600;
}
</style>