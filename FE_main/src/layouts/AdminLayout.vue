<script setup>
import { ref } from 'vue'
import AdminHeader from '@/components/layout/AdminHeader.vue'
import AdminSidebar from '@/components/layout/AdminSidebar.vue'

const isSidebarCollapsed = ref(false)
const isMobileSidebarOpen = ref(false)

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value
}

const openMobileSidebar = () => {
    isMobileSidebarOpen.value = true
}

const closeMobileSidebar = () => {
    isMobileSidebarOpen.value = false
}
</script>

<template>
    <div class="admin-layout">
        <AdminSidebar :collapsed="isSidebarCollapsed" :mobile-open="isMobileSidebarOpen" @toggle="toggleSidebar"
            @close-mobile="closeMobileSidebar" />

        <div class="admin-overlay" :class="{ show: isMobileSidebarOpen }" @click="closeMobileSidebar" />

        <main class="admin-main" :class="{ 'sidebar-collapsed': isSidebarCollapsed }">
            <AdminHeader @open-sidebar="openMobileSidebar" />

            <section class="admin-content">
                <RouterView />
            </section>
        </main>
    </div>
</template>

<style scoped>
.admin-layout {
    min-height: 100vh;
    background: #f6f8fc;
}

.admin-main {
    min-height: 100vh;
    margin-left: 290px;
    transition: margin-left 0.25s ease;
}

.admin-main.sidebar-collapsed {
    margin-left: 94px;
}

.admin-content {
    padding: 22px 34px 32px;
    overflow-x: hidden;
}

.admin-overlay {
    position: fixed;
    inset: 0;
    z-index: 1030;
    background: rgba(15, 23, 42, 0.42);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

.admin-overlay.show {
    opacity: 1;
    pointer-events: auto;
}

@media (max-width: 991.98px) {

    .admin-main,
    .admin-main.sidebar-collapsed {
        margin-left: 0;
    }

    .admin-content {
        padding: 18px 16px 28px;
    }
}
</style>