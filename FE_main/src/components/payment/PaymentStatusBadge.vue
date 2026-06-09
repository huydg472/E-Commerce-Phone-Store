<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    default: 'unpaid',
  },
})

const statusMap = {
  unpaid: { label: 'Chưa thanh toán', className: 'unpaid' },
  pending: { label: 'Chờ thanh toán', className: 'pending' },
  paid: { label: 'Đã thanh toán', className: 'paid' },
  failed: { label: 'Thất bại', className: 'failed' },
  refunded: { label: 'Đã hoàn tiền', className: 'refunded' },
}

const meta = computed(() => statusMap[props.status] || statusMap.unpaid)
</script>

<template>
  <div class="payment-status-badge" :class="meta.className">
    {{ meta.label }}
  </div>
</template>

<style scoped>
.payment-status-badge {
  display: inline-flex;
  align-items: center;
  width: fit-content;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
}

.payment-status-badge.unpaid,
.payment-status-badge.pending {
  color: #b45309;
  background: #fff7ed;
}

.payment-status-badge.paid {
  color: #15803d;
  background: #ecfdf5;
}

.payment-status-badge.failed {
  color: #dc2626;
  background: #fef2f2;
}

.payment-status-badge.refunded {
  color: #0f766e;
  background: #ecfeff;
}
</style>
