<script setup>
defineProps({
  shippingMethods: {
    type: Array,
    required: true,
  },
  shippingMethod: {
    type: String,
    required: true,
  },
})

defineEmits(['update:shippingMethod'])
</script>

<template>
  <div class="shipping-methods">
    <label
        v-for="method in shippingMethods"
        :key="method.id"
        class="shipping-card"
        :class="{ active: shippingMethod === method.id }"
    >
      <input
          :checked="shippingMethod === method.id"
          class="form-check-input"
          type="radio"
          name="shipping_method"
          :value="method.id"
          @change="$emit('update:shippingMethod', method.id)"
      />

      <div class="shipping-icon">
        <i :class="`bi ${method.icon}`"></i>
      </div>

      <div class="shipping-content">
        <h3>{{ method.title }}</h3>
        <p>{{ method.feeLabel }}</p>
      </div>
    </label>
  </div>
</template>

