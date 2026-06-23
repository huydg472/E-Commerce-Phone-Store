<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: String,
    required: true,
  },
  percent: {
    type: String,
    required: true,
  },
  icon: {
    type: String,
    required: true,
  },
  variant: {
    type: String,
    default: 'primary',
  },
})
</script>

<template>
  <article class="stat-card" :class="{ 'stat-card--revenue': title === 'Tổng doanh thu' }">
    <div class="stat-icon" :class="variant">
      <i class="bi" :class="icon"></i>
    </div>

    <div class="stat-content">
      <h3>{{ title }}</h3>
      <strong>{{ value }}</strong>
      <p>
        <i class="bi bi-arrow-up"></i>
        {{ percent }} so với 7 ngày trước
      </p>
    </div>
  </article>
</template>

<style scoped>
.stat-card {
  min-height: 108px;
  display: flex;
  align-items: center;
  gap: 18px;
  padding: 20px;
  overflow: hidden;
  background: #ffffff;
  border: 1px solid #e5e9f1;
  border-radius: 10px;
  box-shadow: 0 9px 25px rgba(15, 23, 42, 0.05);
}

.stat-icon {
  width: 72px;
  height: 72px;
  flex: 0 0 auto;
  display: grid;
  place-items: center;
  border-radius: 14px;
  font-size: 34px;
}

.stat-icon.primary {
  color: #0d6efd;
  background: #e8f2ff;
}

.stat-icon.success {
  color: #16a34a;
  background: #e9f8ed;
}

.stat-icon.purple {
  color: #7c3aed;
  background: #f0e5ff;
}

.stat-icon.warning {
  color: #f59e0b;
  background: #fff1df;
}

.stat-content {
  min-width: 0;
  flex: 1;
}

.stat-card h3 {
  margin: 0 0 6px;
  color: #273246;
  font-size: 16px;
  font-weight: 600;
}

.stat-card strong {
  display: block;
  color: #0f172a;
  font-size: 27px;
  font-weight: 800;
  line-height: 1.05;
  letter-spacing: 0.01em;
  white-space: normal;
  overflow-wrap: anywhere;
}

.stat-card p {
  margin: 8px 0 0;
  color: #344256;
  font-size: 14px;
  line-height: 1.2;
}

.stat-card p i {
  margin-right: 3px;
  color: #16a34a;
}

@media (max-width: 1599.98px) {
  .stat-card {
    gap: 14px;
    padding: 14px;
  }

  .stat-icon {
    width: 58px;
    height: 58px;
    font-size: 29px;
  }

  .stat-card h3 {
    font-size: 15px;
  }

  .stat-card strong {
    font-size: 24px;
  }

  .stat-card p {
    font-size: 13px;
  }
}

@media (max-width: 575.98px) {
  .stat-card {
    height: auto;
    padding: 18px;
  }

  .stat-icon {
    width: 64px;
    height: 64px;
    font-size: 30px;
  }

  .stat-card strong {
    font-size: 24px;
  }
}
</style>
