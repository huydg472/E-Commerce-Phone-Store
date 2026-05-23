<script setup>
import Footer from '@/components/layout/Footer.vue';
import Header from '@/components/layout/Header.vue';
import Navbar from "@/components/layout/Navbar.vue";

</script>

<template>
  <Header/>

  <Navbar/>

  <main>
    <RouterView/>
  </main>

  <Footer/>
</template>

<style scoped>
.default-layout {
  min-height: 100vh;
  background: var(--card-bg);
}
</style>
