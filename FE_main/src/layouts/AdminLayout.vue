<script setup>
import { ref } from 'vue'
import AdminHeader from '@/components/layout/AdminHeader.vue'
import AdminSidebar from '@/components/layout/AdminSidebar.vue'

const sidebarOpen = ref(false)
</script>

<template>
  <div class="admin-layout">
    <AdminSidebar :is-open="sidebarOpen" @close="sidebarOpen = false"/>

    <div class="admin-main">
      <AdminHeader @open-sidebar="sidebarOpen = true" />

      <main class="admin-content">
        <RouterView />
      </main>
    </div>
  </div>
</template>

<style scoped>
.admin-layout {
  min-height: 100vh;
  background: #f6f8fc;
  display: flex;
}

.admin-main {
  flex: 1;
  min-width: 0;
  display: flex;
  flex-direction: column;
}

.admin-content {
  flex: 1;
  padding: 24px 28px 28px;
  background: #f6f8fc;
}

@media (max-width: 1200px) {
  .admin-content {
    padding: 20px 22px 24px;
  }
}

@media (max-width: 700px) {
  .admin-content {
    padding: 16px;
  }
}
</style>