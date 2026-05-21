<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useProductStore } from '@/stores/productStore'
import { formatCurrency } from '@/utils/formatCurrency'

const router = useRouter()
const productStore = useProductStore()

onMounted(() => {
  productStore.fetchAll()
})

async function deleteProduct(id) {
  if (!confirm('Bạn có chắc muốn xoá sản phẩm này không?')) return
  await productStore.remove(id)
}
</script>

<template>
  <section>
    <div class="admin-page-header">
      <h1>Quản lý sản phẩm</h1>

      <button class="btn btn-primary" @click="router.push('/admin/products/create')">
        Thêm sản phẩm
      </button>
    </div>

    <div class="admin-card">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Tên sản phẩm</th>
            <th>Thương hiệu</th>
            <th>Danh mục</th>
            <th>Giá</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="product in productStore.items" :key="product.id">
            <td>{{ product.id }}</td>
            <td>{{ product.name }}</td>
            <td>{{ product.brand?.name || product.brand_id }}</td>
            <td>{{ product.category?.name || product.category_id }}</td>
            <td>{{ formatCurrency(product.price || 0) }}</td>
            <td>{{ product.status }}</td>
            <td class="actions">
              <button @click="router.push(`/admin/products/${product.id}`)">Xem</button>
              <button @click="router.push(`/admin/products/${product.id}/edit`)">Sửa</button>
              <button @click="router.push(`/admin/products/${product.id}/variants`)">Biến thể</button>
              <button @click="deleteProduct(product.id)">Xoá</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>
