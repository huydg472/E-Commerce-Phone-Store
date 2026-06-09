<script setup>
defineProps({
  steps: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <div class="timeline-box">
    <span>Dòng trạng thái</span>
    <div class="timeline">
      <div
        v-for="step in steps"
        :key="step.key"
        class="timeline-step"
        :class="{ done: step.done, current: step.current }"
      >
        <i :class="step.done ? 'bi bi-check-circle-fill' : 'bi bi-circle'"></i>
        <span>{{ step.label }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.timeline-box {
  padding: 11px 12px;
  border: 1px solid #eef2f7;
  border-radius: 11px;
  background: #fafcff;
}

.timeline-box > span {
  display: block;
  margin-bottom: 6px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

.timeline {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.timeline-step {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #64748b;
  font-weight: 700;
}

.timeline-step i {
  color: #cbd5e1;
}

.timeline-step.done {
  color: #0f172a;
}

.timeline-step.done i {
  color: #2563eb;
}

.timeline-step.current {
  color: #2563eb;
}
</style>
