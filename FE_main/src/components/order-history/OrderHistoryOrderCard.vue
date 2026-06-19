<script setup>
defineProps({
  order: {
    type: Object,
    required: true,
  },
  statusMap: {
    type: Object,
    required: true,
  },
  pendingPaymentMethods: {
    type: Object,
    required: true,
  },
})

defineEmits(['view-detail', 'primary-action', 'reorder'])
</script>

<template>
  <article class="order-card">
    <div class="order-card-header">
      <div class="order-code">
        <span>Mã đơn hàng:</span>
        <strong>{{ order.code }}</strong>
      </div>

      <div class="order-date">
        <span>Ngày đặt:</span>
        <strong>{{ order.orderDate }}</strong>
      </div>

      <span class="status-badge" :class="statusMap[order.status]?.className || 'pending'">
        {{ statusMap[order.status]?.label || order.status }}
      </span>
    </div>

    <div class="order-card-body">
      <div class="product-info">
        <div class="product-preview">
          <img
              v-for="(preview, index) in order.product.previewProducts"
              :key="`${order.id}-${index}`"
              :src="preview.image"
              :alt="preview.name"
              :class="{ stacked: order.product.previewProducts.length > 1 && index > 0 }"
          />
        </div>

        <div class="product-text">
          <h3>{{ order.product.name }}</h3>
          <p v-if="order.product.color">Phiên bản: {{ order.product.color }}</p>
          <p>
            Số lượng: {{ order.product.quantity }}
            <span v-if="order.product.extraCount">+ {{ order.product.extraCount }} sản phẩm khác</span>
          </p>
        </div>
      </div>

      <div class="order-total">
        <span>Tổng tiền</span>
        <strong>{{ order.totalFormatted }}</strong>
      </div>

      <div class="order-actions">
        <button type="button" class="action-btn outline-btn" @click="$emit('view-detail', order)">
          Xem chi tiết
        </button>

        <button
            v-if="order.status === 'pending' && order.paymentStatus !== 'paid' && pendingPaymentMethods.has(order.paymentMethod)"
            type="button"
            class="action-btn primary-btn"
            @click="$emit('primary-action', order)"
        >
          Thanh toán
        </button>

        <button
            v-else-if="order.status === 'pending' && order.paymentStatus !== 'paid' && order.paymentMethod === 'cod'"
            type="button"
            class="action-btn danger-btn"
            @click="$emit('primary-action', order)"
        >
          Hủy đơn
        </button>

        <button v-if="order.status !== 'pending'" type="button" class="text-action blue"
                @click="$emit('reorder', order)">
          <i class="bi bi-arrow-clockwise"></i>
          Mua lại
        </button>
      </div>
    </div>

    <div class="order-address">
      <div class="order-address__head">
        <span>Địa chỉ nhận hàng</span>
      </div>
      <p>
        <i class="bi bi-geo-alt"></i>
        {{ order.address || 'Chưa có địa chỉ' }}
      </p>
    </div>
  </article>
</template>

