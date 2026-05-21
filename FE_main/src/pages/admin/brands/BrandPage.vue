<script setup>
import { reactive, ref, onMounted } from 'vue'
import BrandForm from '@/components/brand/BrandForm.vue'
import BrandTable from '@/components/brand/BrandTable.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { useBrandStore } from '@/stores/brandStore'

const brandStore = useBrandStore()
const showModal = ref(false)
const editingId = ref(null)

const form = reactive({
  name: '',
  logo_url: '',
  description: '',
  status: 'active',
})

function resetForm() {
  editingId.value = null
  Object.assign(form, {
    name: '',
    logo_url: '',
    description: '',
    status: 'active',
  })
}

function openCreateModal() {
  resetForm()
  showModal.value = true
}

function openEditModal(brand) {
  editingId.value = brand.id
  Object.assign(form, brand)
  showModal.value = true
}

async function submitForm() {
  if (editingId.value) {
    await brandStore.update(editingId.value, form)
  } else {
    await brandStore.create(form)
  }

  showModal.value = false
  resetForm()
}

async function deleteBrand(id) {
  if (!confirm('Bạn có chắc muốn xoá thương hiệu này không?')) return
  await brandStore.remove(id)
}

onMounted(() => {
  brandStore.fetchAll()
})
</script>

<template>
  <section>
    <div class="admin-page-header">
      <h1>Quản lý thương hiệu</h1>

      <button class="btn btn-primary" @click="openCreateModal">
        Thêm thương hiệu
      </button>
    </div>

    <div class="admin-card">
      <BrandTable
        :brands="brandStore.items"
        @edit="openEditModal"
        @delete="deleteBrand"
      />
    </div>

    <BaseModal
      :show="showModal"
      :title="editingId ? 'Sửa thương hiệu' : 'Thêm thương hiệu'"
      @close="showModal = false"
    >
      <BrandForm
        :form="form"
        :button-text="editingId ? 'Cập nhật' : 'Thêm mới'"
        @submit="submitForm"
      />
    </BaseModal>
  </section>
</template>
