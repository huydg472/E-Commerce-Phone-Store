<script setup>
import { computed } from 'vue'
import { formatCurrency } from '@/utils/formatCurrency'

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
})

const imageSrc = computed(() => {
  return (
    props.item?.productVariant?.product?.thumbnail_url ||
    props.item?.productVariant?.product?.thumbnailUrl ||
    props.item?.productVariant?.product?.image ||
    '/images/default-product.png'
  )
})

const variantName = computed(() => props.item?.variant_name || props.item?.productVariant?.name || 'Không có biến thể')
</script>

<template>
  <article class="order-item">
    <img :src="imageSrc" :alt="item.product_name" />

    <div class="item-info">
      <h3>{{ item.product_name }}</h3>
      <p>{{ variantName }}</p>
      <p>SKU: {{ item.sku || 'N/A' }}</p>
      <p>Số lượng: {{ item.quantity }}</p>
    </div>

    <div class="item-meta">
      <span>{{ formatCurrency(item.unit_price) }} x {{ item.quantity }}</span>
      <strong>{{ formatCurrency(item.total_price) }}</strong>
    </div>
  </article>
</template>

<style scoped>
.order-item {
  display: grid;
  grid-template-columns: 64px minmax(0, 1fr) auto;
  gap: 12px;
  align-items: center;
  padding: 11px;
  border: 1px solid #eef2f7;
  border-radius: 11px;
}

.order-item img {
  width: 64px;
  height: 64px;
  border-radius: 10px;
  object-fit: cover;
  background: #f3f4f6;
}

.item-info h3 {
  margin: 0 0 4px;
  font-size: 14px;
  font-weight: 800;
}

.item-info p {
  margin: 0 0 2px;
  color: #64748b;
  font-size: 12px;
}

.item-meta {
  text-align: right;
}

.item-meta span {
  display: block;
  margin-bottom: 4px;
  color: #64748b;
  font-size: 12px;
}

.item-meta strong {
  color: #0d6efd;
  font-size: 14px;
  font-weight: 850;
}
</style>
