<template>
    <div class="home-page">
        <section class="container-fluid px-4">
            <div class="hero-banner">
                <img :src="placeholder.hero" alt="Hero banner" />
            </div>
        </section>

        <ServicePolicyBar />

        <section class="container-fluid px-4 featured-section">
            <div class="section-heading">
                <h2>Sản phẩm nổi bật</h2>

                <RouterLink to="/san-pham">
                    Xem tất cả
                    <i class="bi bi-chevron-right"></i>
                </RouterLink>
            </div>

            <div class="product-slider-wrap">
                <button class="product-nav product-nav-left">
                    <i class="bi bi-chevron-left"></i>
                </button>

                <div class="product-grid">
                    <ProductCard v-for="product in featuredProducts" :key="product.id" :image="product.image"
                        :name="product.name" :price="product.price" :old-price="product.oldPrice" />
                </div>

                <button class="product-nav product-nav-right">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </section>

        <section class="container-fluid px-4 promo-section">
            <div class="promo-grid">
                <RouterLink to="/phu-kien" class="promo-placeholder-card">
                    <img :src="placeholder.accessories" alt="Accessories placeholder" />
                </RouterLink>

                <RouterLink to="/thu-cu-doi-moi" class="promo-placeholder-card">
                    <img :src="placeholder.tradeIn" alt="Trade in placeholder" />
                </RouterLink>
            </div>
        </section>
    </div>
</template>

<script setup>
import ServicePolicyBar from '@/components/common/ServicePolicyBar.vue'
import ProductCard from '@/components/product/ProductCard.vue'

const placeholder = {
    hero: 'https://placehold.co/1800x420/e8f0fb/2563eb?text=Hero+Banner',
    product: 'https://placehold.co/300x180/f1f5f9/2563eb?text=Phone',
    accessories: 'https://placehold.co/900x180/e8f0fb/2563eb?text=Accessories',
    tradeIn: 'https://placehold.co/900x180/f1ecfb/2563eb?text=Trade+In',
}

const featuredProducts = [
    {
        id: 1,
        image: placeholder.product,
        name: 'iPhone 15 Pro Max 256GB',
        price: '34.990.000đ',
        oldPrice: '',
    },
    {
        id: 2,
        image: placeholder.product,
        name: 'Samsung Galaxy S24 Ultra 5G 256GB',
        price: '28.990.000đ',
        oldPrice: '30.990.000đ',
    },
    {
        id: 3,
        image: placeholder.product,
        name: 'Xiaomi 14 5G 256GB',
        price: '21.990.000đ',
        oldPrice: '',
    },
    {
        id: 4,
        image: placeholder.product,
        name: 'OPPO Reno11 F 5G 256GB',
        price: '8.990.000đ',
        oldPrice: '',
    },
    {
        id: 5,
        image: placeholder.product,
        name: 'Realme C67 8GB/256GB',
        price: '6.490.000đ',
        oldPrice: '',
    },
]
</script>

<style scoped>
.home-page {
    padding-top: 14px;
    background: var(--card-bg);
}

.hero-banner {
    width: 100%;
    height: 420px;
    border-radius: 10px;
    overflow: hidden;
    background: #e8f0fb;
}

.hero-banner img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.featured-section {
    margin-top: 22px;
}

.section-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 14px;
}

.section-heading h2 {
    margin: 0;
    font-size: 30px;
    line-height: 1.25;
    font-weight: 800;
    color: var(--text-color);
}

.section-heading a {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 16px;
}

.section-heading a:hover {
    color: var(--primary-hover);
}

.product-slider-wrap {
    position: relative;
}

.product-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 24px;
}

.product-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 4;
    border: none;
    background: var(--card-bg);
    color: var(--text-color);
    width: 48px;
    height: 48px;
    border-radius: 50%;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.1);
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.product-nav:hover {
    color: var(--primary-color);
}

.product-nav-left {
    left: -24px;
}

.product-nav-right {
    right: -24px;
}

.promo-section {
    margin-top: 20px;
}

.promo-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.promo-placeholder-card {
    display: block;
    height: 180px;
    border-radius: 10px;
    overflow: hidden;
    background: #e8f0fb;
}

.promo-placeholder-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

@media (max-width: 1200px) {
    .hero-banner {
        height: 340px;
    }

    .product-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

@media (max-width: 900px) {
    .promo-grid {
        grid-template-columns: 1fr;
    }

    .hero-banner {
        height: 260px;
    }

    .product-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }

    .promo-placeholder-card {
        height: 150px;
    }

    .product-nav-left {
        left: -10px;
    }

    .product-nav-right {
        right: -10px;
    }
}

@media (max-width: 576px) {
    .hero-banner {
        height: 210px;
    }

    .product-grid {
        grid-template-columns: 1fr;
    }

    .section-heading h2 {
        font-size: 24px;
    }
}
</style>