<script setup>
import { computed } from 'vue'

const props = defineProps({
    image: {
        type: String,
        default: '',
    },
    name: {
        type: String,
        default: '',
    },
    price: {
        type: String,
        default: '',
    },
    oldPrice: {
        type: String,
        default: '',
    },
})

const productImage = computed(() => {
    return props.image || 'https://placehold.co/300x180/f1f5f9/2563eb?text=Phone'
})
</script>

<template>
    <div class="product-card">
        <button class="wishlist-btn">
            <i class="bi bi-heart"></i>
        </button>

        <div class="product-image">
            <img :src="productImage" :alt="name" />
        </div>

        <div class="product-info">
            <h5>{{ name }}</h5>

            <div class="product-price">
                <span>{{ price }}</span>
                <del v-if="oldPrice">{{ oldPrice }}</del>
            </div>
        </div>
    </div>
</template>

<style scoped>
.product-card {
    min-height: 215px;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    background: var(--card-bg);
    position: relative;
    padding: 14px 16px 16px;
    overflow: hidden;
}

.product-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
}

.wishlist-btn {
    position: absolute;
    right: 14px;
    top: 12px;
    border: none;
    background: transparent;
    color: var(--muted-color);
    font-size: 20px;
    z-index: 2;
}

.wishlist-btn:hover {
    color: var(--danger-color);
}

.product-image {
    height: 108px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 10px;
}

.product-image img {
    width: 100%;
    max-width: 180px;
    height: 108px;
    object-fit: cover;
    border-radius: 8px;
}

.product-info h5 {
    min-height: 46px;
    margin: 0 0 10px;
    font-size: 15px;
    line-height: 1.35;
    font-weight: 700;
    color: var(--text-color);
}

.product-price {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.product-price span {
    color: var(--text-color);
    font-size: 17px;
    line-height: 1.3;
    font-weight: 800;
}

.product-price del {
    color: var(--muted-color);
    font-size: 13px;
    line-height: 1.3;
}

@media (max-width: 576px) {
    .product-card {
        height: auto;
        min-height: 250px;
    }
}
</style>