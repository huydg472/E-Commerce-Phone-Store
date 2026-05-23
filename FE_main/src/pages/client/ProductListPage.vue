<script setup>
import ProductCard from '@/components/product/ProductCard.vue'
import ProductFilter from '@/components/product/ProductFilter.vue'
import BasePagination from '@/components/common/BasePagination.vue'

const products = [
  {
    id: 1,
    name: 'iPhone 15 Pro Max 256GB',
    image: '/images/products/iphone-15-pro-max.png',
    storage: '256GB',
    price: '34.990.000đ',
    oldPrice: '',
    discount: '',
  },
  {
    id: 2,
    name: 'iPhone 15 128GB',
    image: '/images/products/iphone-15.png',
    storage: '128GB',
    price: '21.990.000đ',
    oldPrice: '',
    discount: '',
  },
  {
    id: 3,
    name: 'Samsung Galaxy S24 Ultra 5G 256GB',
    image: '/images/products/samsung-galaxy-s24-ultra.png',
    storage: '256GB',
    price: '25.990.000đ',
    oldPrice: '28.990.000đ',
    discount: '10%',
  },
  {
    id: 4,
    name: 'Samsung Galaxy S24 256GB',
    image: '/images/products/samsung-galaxy-s24.png',
    storage: '256GB',
    price: '18.990.000đ',
    oldPrice: '',
    discount: '',
  },
  {
    id: 5,
    name: 'Xiaomi 14 256GB',
    image: '/images/products/xiaomi-14.png',
    storage: '256GB',
    price: '17.990.000đ',
    oldPrice: '19.990.000đ',
    discount: '10%',
  },
  {
    id: 6,
    name: 'OPPO Reno11 F 5G 256GB',
    image: '/images/products/oppo-reno11-f.png',
    storage: '256GB',
    price: '9.990.000đ',
    oldPrice: '11.990.000đ',
    discount: '17%',
  },
  {
    id: 7,
    name: 'realme C67 256GB',
    image: '/images/products/realme-c67.png',
    storage: '256GB',
    price: '6.490.000đ',
    oldPrice: '7.490.000đ',
    discount: '13%',
  },
  {
    id: 8,
    name: 'vivo V30e 256GB',
    image: '/images/products/vivo-v30e.png',
    storage: '256GB',
    price: '9.490.000đ',
    oldPrice: '10.990.000đ',
    discount: '14%',
  },
  {
    id: 9,
    name: 'POCO X6 Pro 5G 256GB',
    image: '/images/products/poco-x6-pro.png',
    storage: '256GB',
    price: '8.990.000đ',
    oldPrice: '9.990.000đ',
    discount: '10%',
  },
  {
    id: 10,
    name: 'Samsung Galaxy A55 5G 256GB',
    image: '/images/products/samsung-galaxy-a55.png',
    storage: '256GB',
    price: '10.490.000đ',
    oldPrice: '11.990.000đ',
    discount: '13%',
  },
  {
    id: 11,
    name: 'iPhone 14 128GB',
    image: '/images/products/iphone-14.png',
    storage: '128GB',
    price: '16.990.000đ',
    oldPrice: '18.990.000đ',
    discount: '11%',
  },
  {
    id: 12,
    name: 'Infinix Note 40 Pro 256GB',
    image: '/images/products/infinix-note-40-pro.png',
    storage: '256GB',
    price: '6.990.000đ',
    oldPrice: '7.990.000đ',
    discount: '13%',
  },
]
</script>

<template>
  <section class="product-list-page">
    <div class="container">
      <div class="breadcrumb-area">
        <RouterLink to="/">Trang chủ</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <span>Sản phẩm</span>
      </div>

      <div class="page-heading">
        <div>
          <h1>Tất cả sản phẩm</h1>
          <p>Hiển thị 1 - 12 sản phẩm</p>
        </div>

        <div class="heading-action">
          <select class="form-select">
            <option>Sắp xếp: Mới nhất</option>
            <option>Giá tăng dần</option>
            <option>Giá giảm dần</option>
            <option>Tên A-Z</option>
          </select>

          <select class="form-select show-select">
            <option>Hiển thị: 12</option>
            <option>Hiển thị: 24</option>
            <option>Hiển thị: 36</option>
          </select>
        </div>
      </div>

      <div class="product-layout">
        <ProductFilter/>

        <div class="product-main">
          <div class="product-grid">
            <ProductCard
                v-for="product in products"
                :key="product.id"
                :name="product.name"
                :image="product.image"
                :storage="product.storage"
                :price="product.price"
                :old-price="product.oldPrice"
                :discount="product.discount"
                :to="`/products/${product.id}`"
            />
          </div>

          <BasePagination/>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.product-list-page {
  padding: 26px 0 56px;
  background: #ffffff;
}

.breadcrumb-area {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
  margin-bottom: 12px;
}

.breadcrumb-area a {
  color: #64748b;
  text-decoration: none;
}

.breadcrumb-area a:hover,
.breadcrumb-area span {
  color: #0d6efd;
}

.breadcrumb-area i {
  font-size: 11px;
}

.page-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 20px;
  margin-bottom: 22px;
}

.page-heading h1 {
  margin: 0 0 8px;
  color: #111827;
  font-size: 30px;
  font-weight: 800;
}

.page-heading p {
  margin: 0;
  color: #64748b;
  font-size: 15px;
  font-weight: 500;
}

.heading-action {
  display: flex;
  align-items: center;
  gap: 12px;
}

.heading-action .form-select {
  min-width: 180px;
  height: 40px;
  border-color: #dbe3ef;
  border-radius: 8px;
  color: #334155;
  font-size: 14px;
  font-weight: 600;
  box-shadow: none;
}

.heading-action .show-select {
  min-width: 130px;
}

.product-layout {
  display: grid;
  grid-template-columns: 280px minmax(0, 1fr);
  gap: 24px;
  align-items: flex-start;
}

.product-main {
  min-width: 0;
}

.product-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
}

@media (max-width: 1200px) {
  .product-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
  }
}

@media (max-width: 992px) {
  .page-heading {
    align-items: flex-start;
    flex-direction: column;
  }

  .heading-action {
    width: 100%;
  }

  .heading-action .form-select {
    width: 100%;
  }

  .product-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .page-heading h1 {
    font-size: 25px;
  }

  .heading-action {
    flex-direction: column;
  }

  .product-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }
}

@media (max-width: 480px) {
  .product-grid {
    grid-template-columns: 1fr;
  }
}
</style>