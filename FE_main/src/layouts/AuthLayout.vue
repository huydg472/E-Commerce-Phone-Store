<template>
  <main class="auth-layout">
    <section class="auth-card">
      <router-view />
    </section>
  </main>
</template>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: grid;
  place-items: center;
  background: #eff6ff;
}

.auth-card {
  width: min(440px, calc(100% - 32px));
  background: #fff;
  border-radius: 16px;
  padding: 24px;
}
</style>
