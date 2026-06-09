<script setup>
defineProps({
  title: {
    type: String,
    default: 'Giỏ hàng đang trống',
  },
  description: {
    type: String,
    default: 'Hãy thêm sản phẩm bạn thích vào giỏ để xem tại đây.',
  },
  linkTo: {
    type: String,
    default: '/products',
  },
  linkLabel: {
    type: String,
    default: 'Tiếp tục mua sắm',
  },
})
</script>

<template>
  <div class="cart-empty-state">
    <i class="bi bi-cart3"></i>
    <h3>{{ title }}</h3>
    <p>{{ description }}</p>
    <RouterLink :to="linkTo">
      {{ linkLabel }}
    </RouterLink>
  </div>
</template>

<style scoped>
.cart-empty-state {
  padding: 42px 24px 48px;
  text-align: center;
}

.cart-empty-state i {
  color: #0d6efd;
  font-size: 44px;
}

.cart-empty-state h3 {
  margin: 14px 0 8px;
  color: #111827;
  font-size: 18px;
  font-weight: 900;
}

.cart-empty-state p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
}

.cart-empty-state a {
  margin-top: 16px;
  color: #0d6efd;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 180px;
  min-height: 42px;
  padding: 0 18px;
  border: 1px solid #0d6efd;
  border-radius: 8px;
  background: #f8fbff;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
}
</style>
