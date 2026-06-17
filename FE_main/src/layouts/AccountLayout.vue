<script setup>
import AccountSidebar from '@/components/account/AccountSidebar.vue'
</script>

<template>
  <div class="account-layout">
    <div class="account-container">
      <div class="account-grid">
        <div class="account-sidebar-col">
          <AccountSidebar/>
        </div>

        <div class="account-content-col">
          <RouterView/>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.account-layout {
  background: #f8fbff;
}

.account-container {
  width: min(100% - 32px, 1180px);
  margin: 0 auto;
  padding: 24px 0 36px;
}

.account-grid {
  display: grid;
  grid-template-columns: 260px minmax(0, 1fr);
  gap: 18px;
  align-items: start;
}

.account-content-col {
  min-width: 0;
}

@media (max-width: 992px) {
  .account-container {
    width: min(100% - 24px, 1180px);
  }

  .account-grid {
    grid-template-columns: 1fr;
  }
}
</style>
