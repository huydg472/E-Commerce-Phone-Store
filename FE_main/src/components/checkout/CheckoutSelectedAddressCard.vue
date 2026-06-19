<script setup>
defineProps({
  selectedShippingAddress: {
    type: Object,
    default: null,
  },
  selectedAddressLine: {
    type: String,
    default: '',
  },
})

defineEmits(['open-address-picker'])
</script>

<template>
  <div class="selected-address-card">
    <div class="selected-address-card__main">
      <div class="selected-address-card__icon">
        <i class="bi bi-geo-alt-fill"></i>
      </div>
      <div class="selected-address-card__content">
        <div class="selected-address-card__header">
          <strong>{{ selectedShippingAddress.receiver_name }}</strong>
        </div>
        <p>{{ selectedAddressLine || 'Chưa có địa chỉ chi tiết' }}</p>
        <small>
          <i class="bi bi-telephone"></i>
          {{ selectedShippingAddress.receiver_phone || 'Chưa có số điện thoại' }}
        </small>
      </div>
    </div>

    <button type="button" class="link-btn" @click="$emit('open-address-picker')">
      Chọn địa chỉ khác
    </button>
  </div>
</template>

