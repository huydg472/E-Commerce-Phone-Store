<script setup>
import Footer from '@/components/layout/Footer.vue';
import Header from '@/components/layout/Header.vue';

</script>

<template>
    <Header />

    <main>
        <RouterView />
    </main>

    <Footer />
</template>

<style scoped>
.default-layout {
    min-height: 100vh;
    background: var(--card-bg);
}
</style>
