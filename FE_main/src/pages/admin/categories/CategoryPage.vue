<script setup>
import { reactive, ref, onMounted } from 'vue'
import CategoryForm from '@/components/category/CategoryForm.vue'
import CategoryTable from '@/components/category/CategoryTable.vue'
import BaseModal from '@/components/common/BaseModal.vue'
import { useCategoryStore } from '@/stores/categoryStore'

const categoryStore = useCategoryStore()
const showModal = ref(false)
const editingId = ref(null)

const form = reactive({
  name: '',
  description: '',
  status: 'active',
})

function resetForm() {
  editingId.value = null
  Object.assign(form, {
    name: '',
    description: '',
    status: 'active',
  })
}

function openCreateModal() {
  resetForm()
  showModal.value = true
}

function openEditModal(category) {
  editingId.value = category.id
  Object.assign(form, category)
  showModal.value = true
}

async function submitForm() {
  if (editingId.value) {
    await categoryStore.update(editingId.value, form)
  } else {
    await categoryStore.create(form)
  }

  showModal.value = false
  resetForm()
}

async function deleteCategory(id) {
  if (!confirm('Bạn có chắc muốn xoá danh mục này không?')) return
  await categoryStore.remove(id)
}

onMounted(() => {
  categoryStore.fetchAll()
})
</script>

<template>
  <section>
    <div class="admin-page-header">
      <h1>Quản lý danh mục</h1>

      <button class="btn btn-primary" @click="openCreateModal">
        Thêm danh mục
      </button>
    </div>

    <div class="admin-card">
      <CategoryTable
        :categories="categoryStore.items"
        @edit="openEditModal"
        @delete="deleteCategory"
      />
    </div>

    <BaseModal
      :show="showModal"
      :title="editingId ? 'Sửa danh mục' : 'Thêm danh mục'"
      @close="showModal = false"
    >
      <CategoryForm
        :form="form"
        :button-text="editingId ? 'Cập nhật' : 'Thêm mới'"
        @submit="submitForm"
      />
    </BaseModal>
  </section>
</template>
