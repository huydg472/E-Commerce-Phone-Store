<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    default: 'pending',
  },
})

const statusMap = {
  pending: { label: 'Chờ xác nhận', className: 'pending' },
  confirmed: { label: 'Đã xác nhận', className: 'confirmed' },
  processing: { label: 'Đang xử lý', className: 'processing' },
  shipping: { label: 'Đang giao', className: 'shipping' },
  completed: { label: 'Hoàn thành', className: 'completed' },
  cancelled: { label: 'Đã hủy', className: 'cancelled' },
}

const meta = computed(() => statusMap[props.status] || statusMap.pending)
</script>

<template>
  <div class="order-status-badge" :class="meta.className">
    {{ meta.label }}
  </div>
</template>

<style scoped>
.order-status-badge {
  display: inline-flex;
  align-items: center;
  width: fit-content;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
}

.order-status-badge.pending {
  color: #c2410c;
  background: #fff7ed;
}

.order-status-badge.confirmed {
  color: #7c3aed;
  background: #f5f3ff;
}

.order-status-badge.processing {
  color: #2563eb;
  background: #eff6ff;
}

.order-status-badge.shipping {
  color: #0f766e;
  background: #ecfeff;
}

.order-status-badge.completed {
  color: #15803d;
  background: #ecfdf5;
}

.order-status-badge.cancelled {
  color: #dc2626;
  background: #fef2f2;
}
</style>
