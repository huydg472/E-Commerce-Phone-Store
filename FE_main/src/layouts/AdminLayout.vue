<script setup>
import AdminSidebar from '@/components/layout/AdminSidebar.vue'
import AdminHeader from '@/components/layout/AdminHeader.vue'
</script>

<template>
  <div class="admin-layout">
    <AdminSidebar />

    <div class="admin-main">
      <AdminHeader />

      <main class="admin-content">
        <router-view />
      </main>
    </div>
  </div>
</template>
