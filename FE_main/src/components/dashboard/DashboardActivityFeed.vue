<script setup>
defineProps({
  activities: {
    type: Array,
    required: true,
  },
})
</script>

<template>
  <section class="dashboard-card activity-card">
    <div class="card-head">
      <div>
        <h4>Hoạt động gần đây</h4>
        <p>Dữ liệu được lấy từ bản ghi mới nhất trong hệ thống</p>
      </div>
    </div>

    <div v-if="activities.length" class="activity-list">
      <article v-for="item in activities" :key="item.title + item.time" class="activity-item">
        <div class="activity-icon" :class="`tone-${item.tone}`">
          <i :class="item.icon"></i>
        </div>

        <div class="activity-content">
          <strong>{{ item.title }}</strong>
          <span>{{ item.detail }}</span>
          <small>{{ item.time }}</small>
        </div>
      </article>
    </div>

    <div v-else class="empty-state">
      Chưa có dữ liệu hoạt động.
    </div>
  </section>
</template>

