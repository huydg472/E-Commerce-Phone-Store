<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import { useClientPagination } from '@/composables/useClientPagination.js'
import { usePermissionStore } from '@/stores/permissionStore'

const router = useRouter()
const permissionStore = usePermissionStore()

const pageLoading = ref(true)
const errorMessage = ref('')
const saving = ref(false)
const searchQuery = ref('')
const moduleFilter = ref('all')
const showForm = ref(false)
const editingPermissionId = ref(null)

const form = reactive({
  name: '',
  display_name: '',
  module: '',
  action: '',
  description: '',
})

const permissions = computed(() => (Array.isArray(permissionStore.items) ? permissionStore.items : []))

const moduleOptions = computed(() => {
  const modules = new Set(permissions.value.map((permission) => permission.module).filter(Boolean))
  return Array.from(modules).sort((a, b) => a.localeCompare(b))
})

const actionOptions = computed(() => {
  const actions = new Set(permissions.value.map((permission) => permission.action).filter(Boolean))
  return Array.from(actions).sort((a, b) => a.localeCompare(b))
})

const filteredPermissions = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return permissions.value.filter((permission) => {
    const matchesQuery = !query
      || [permission.name, permission.display_name, permission.module, permission.action, permission.description]
          .filter(Boolean)
          .some((value) => String(value).toLowerCase().includes(query))

    const matchesModule = moduleFilter.value === 'all' || permission.module === moduleFilter.value

    return matchesQuery && matchesModule
  })
})

const {
  currentPage,
  pageSize,
  totalPages,
  paginatedItems: paginatedPermissions,
  pageStart,
  pageEnd,
} = useClientPagination(filteredPermissions, {
  defaultPageSize: 5,
  pageSizeOptions: [5, 10],
})

const stats = computed(() => {
  const total = permissions.value.length
  const moduleCount = moduleOptions.value.length
  const actionCount = actionOptions.value.length
  const described = permissions.value.filter((item) => Boolean(item.description)).length

  return [
    {label: 'Tổng quyền', value: total, icon: 'bi-key-fill', color: 'blue'},
    {label: 'Module', value: moduleCount, icon: 'bi-grid-3x3-gap-fill', color: 'green'},
    {label: 'Hành động', value: actionCount, icon: 'bi-lightning-charge-fill', color: 'orange'},
    {label: 'Có mô tả', value: described, icon: 'bi-chat-square-text-fill', color: 'slate'},
  ]
})

const loadPage = async () => {
  pageLoading.value = true
  errorMessage.value = ''

  try {
    await permissionStore.fetchAll()
  } catch (error) {
    if (error.response?.status === 401) {
      await router.replace({name: 'login'})
      return
    }

    if (error.response?.status === 403) {
      await router.replace({name: 'forbidden'})
      return
    }

    errorMessage.value = error.response?.data?.message || 'Không tải được dữ liệu quyền.'
  } finally {
    pageLoading.value = false
  }
}

const resetForm = () => {
  form.name = ''
  form.display_name = ''
  form.module = ''
  form.action = ''
  form.description = ''
  editingPermissionId.value = null
}

const openCreate = () => {
  resetForm()
  showForm.value = true
}

const openEdit = (permission) => {
  form.name = permission.name || ''
  form.display_name = permission.display_name || ''
  form.module = permission.module || ''
  form.action = permission.action || ''
  form.description = permission.description || ''
  editingPermissionId.value = permission.id
  showForm.value = true
}

const closeForm = () => {
  if (saving.value) {
    return
  }

  showForm.value = false
  errorMessage.value = ''
}

const payload = () => ({
  name: form.name.trim(),
  display_name: form.display_name.trim(),
  module: form.module.trim(),
  action: form.action.trim(),
  description: form.description.trim() || null,
})

const handleSubmit = async () => {
  if (saving.value) {
    return
  }

  saving.value = true
  errorMessage.value = ''

  try {
    if (editingPermissionId.value) {
      await permissionStore.update(editingPermissionId.value, payload())
    } else {
      await permissionStore.create(payload())
    }

    showForm.value = false
    resetForm()
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data?.errors
      if (errors) {
        const firstKey = Object.keys(errors)[0]
        errorMessage.value = errors[firstKey]?.[0] || 'Dữ liệu không hợp lệ.'
        return
      }
    }

    errorMessage.value = error.response?.data?.message || 'Lưu quyền thất bại.'
  } finally {
    saving.value = false
  }
}

const removePermission = async (permission) => {
  if (!window.confirm(`Xóa quyền "${permission.display_name || permission.name}"?`)) {
    return
  }

  try {
    await permissionStore.remove(permission.id)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không xóa được quyền.'
  }
}

onMounted(loadPage)
</script>

<template>
  <div class="admin-page">
    <div class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Quản lý quyền</p>
        <h1>Danh sách quyền</h1>
        <p class="subtitle">Quản lý các quyền chức năng dùng cho vai trò và phân quyền hệ thống.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="openCreate">
            <i class="bi bi-plus-lg"></i>
            Thêm quyền
          </button>
          <button type="button" class="secondary-action" @click="loadPage">
            <i class="bi bi-arrow-clockwise"></i>
            Tải lại
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <div v-for="stat in stats" :key="stat.label" class="stat-card">
          <div class="stat-icon" :class="stat.color">
            <i :class="`bi ${stat.icon}`"></i>
          </div>
          <div class="stat-content">
            <strong>{{ stat.value }}</strong>
            <span>{{ stat.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <div class="toolbar-card">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model.trim="searchQuery" type="text" placeholder="Tìm theo tên, module, hành động..." />
      </div>

      <select v-model="moduleFilter" class="filter-select">
        <option value="all">Tất cả module</option>
        <option v-for="moduleName in moduleOptions" :key="moduleName" :value="moduleName">
          {{ moduleName }}
        </option>
      </select>

      <div class="result-chip">
        <i class="bi bi-funnel"></i>
        {{ filteredPermissions.length }} kết quả
      </div>
    </div>

    <p v-if="errorMessage" class="error-banner">{{ errorMessage }}</p>

    <div v-if="pageLoading" class="state-card">
      <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
      <p>Đang tải dữ liệu quyền...</p>
    </div>

    <div v-else class="table-card">
      <div v-if="!filteredPermissions.length" class="empty-state">
        <i class="bi bi-shield-lock"></i>
        <p>Chưa có quyền nào phù hợp bộ lọc.</p>
      </div>

      <div v-else class="table-wrap">
        <table class="data-table">
          <colgroup>
            <col class="col-name" />
            <col class="col-display" />
            <col class="col-module" />
            <col class="col-action" />
            <col class="col-desc" />
            <col class="col-actions" />
          </colgroup>

          <thead>
            <tr>
              <th>Mã quyền</th>
              <th>Tên hiển thị</th>
              <th>Module</th>
              <th>Hành động</th>
              <th>Mô tả</th>
              <th>Thao tác</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="permission in paginatedPermissions" :key="permission.id">
              <td>
                <strong>{{ permission.name }}</strong>
              </td>
              <td>
                <span>{{ permission.display_name }}</span>
              </td>
              <td>
                <span class="module-pill">{{ permission.module }}</span>
              </td>
              <td>
                <span class="action-pill">{{ permission.action }}</span>
              </td>
              <td>
                <span class="description">{{ permission.description || 'Không có mô tả' }}</span>
              </td>
              <td>
                <div class="action-group">
                  <button type="button" class="action-btn view" @click="openEdit(permission)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button type="button" class="action-btn danger" @click="removePermission(permission)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <ListPaginationControls
        v-if="!pageLoading && !errorMessage"
        :current-page="currentPage"
        :total-pages="totalPages"
        :page-size="pageSize"
        :total-items="filteredPermissions.length"
        :page-start="pageStart"
        :page-end="pageEnd"
        item-label="quyền"
        @update:currentPage="currentPage = $event"
        @update:pageSize="pageSize = $event"
    />

    <Teleport to="body">
      <div v-if="showForm" class="modal-backdrop" @click.self="closeForm">
        <div class="modal-card" role="dialog" aria-modal="true">
          <div class="modal-head">
            <div>
              <p class="eyebrow">Quản lý quyền</p>
              <h3>{{ editingPermissionId ? 'Chỉnh sửa quyền' : 'Tạo quyền mới' }}</h3>
            </div>

            <button type="button" class="icon-close" @click="closeForm">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div v-if="errorMessage" class="alert alert-danger mx-4 mt-3 mb-0">
            {{ errorMessage }}
          </div>

          <form class="modal-body" @submit.prevent="handleSubmit">
            <div class="form-grid">
              <div class="form-group">
                <label>Mã quyền</label>
                <input v-model.trim="form.name" type="text" class="form-control" placeholder="VD: product_create" required />
              </div>

              <div class="form-group">
                <label>Tên hiển thị</label>
                <input v-model.trim="form.display_name" type="text" class="form-control" placeholder="VD: Tạo sản phẩm" required />
              </div>

              <div class="form-group">
                <label>Module</label>
                <input v-model.trim="form.module" type="text" class="form-control" placeholder="VD: product" required />
              </div>

              <div class="form-group">
                <label>Hành động</label>
                <input v-model.trim="form.action" type="text" class="form-control" placeholder="VD: create" required />
              </div>

              <div class="form-group full">
                <label>Mô tả</label>
                <textarea
                  v-model.trim="form.description"
                  class="form-control form-textarea"
                  rows="4"
                  placeholder="Mô tả ngắn về quyền..."
                ></textarea>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" class="secondary-action" @click="closeForm">Hủy</button>
              <button type="submit" class="primary-action" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-2"></span>
                {{ editingPermissionId ? 'Lưu thay đổi' : 'Tạo mới' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<style scoped>
.admin-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-card,
.toolbar-card,
.table-card {
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.hero-card {
  padding: 24px;
  display: grid;
  grid-template-columns: minmax(0, 1.25fr) minmax(0, 1fr);
  gap: 18px;
  background: linear-gradient(135deg, #ffffff, #f4f8ff);
}

.eyebrow {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 34px;
  font-weight: 900;
}

.subtitle {
  margin: 10px 0 0;
  color: #64748b;
  font-size: 15px;
  line-height: 1.6;
  max-width: 720px;
}

.hero-actions {
  margin-top: 18px;
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
}

.primary-action,
.secondary-action {
  min-height: 44px;
  padding: 0 18px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  border: none;
}

.primary-action {
  color: #ffffff;
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  box-shadow: 0 12px 26px rgba(37, 99, 235, 0.2);
}

.secondary-action {
  color: #334155;
  background: #ffffff;
  border: 1px solid #dbe3ef;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  align-content: start;
}

.stat-card {
  min-height: 104px;
  padding: 18px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 14px;
}

.stat-icon {
  width: 52px;
  height: 52px;
  border-radius: 16px;
  display: grid;
  place-items: center;
  color: #ffffff;
  font-size: 22px;
}

.stat-icon.blue { background: linear-gradient(135deg, #2563eb, #3b82f6); }
.stat-icon.green { background: linear-gradient(135deg, #16a34a, #22c55e); }
.stat-icon.orange { background: linear-gradient(135deg, #f59e0b, #fb923c); }
.stat-icon.slate { background: linear-gradient(135deg, #475569, #64748b); }

.stat-content strong {
  display: block;
  color: #0f172a;
  font-size: 26px;
  font-weight: 900;
  line-height: 1;
}

.stat-content span {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 14px;
  font-weight: 700;
}

.toolbar-card {
  padding: 14px;
  display: grid;
  grid-template-columns: minmax(0, 1fr) 240px auto;
  gap: 12px;
  align-items: center;
}

.search-box {
  min-height: 48px;
  padding: 0 16px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 10px;
}

.search-box i {
  color: #64748b;
  font-size: 18px;
}

.search-box input {
  width: 100%;
  border: 0;
  outline: none;
  color: #0f172a;
  font-size: 14px;
  font-weight: 600;
  background: transparent;
}

.filter-select {
  width: 100%;
  min-height: 48px;
  padding: 0 16px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
  outline: none;
}

.result-chip {
  min-height: 48px;
  padding: 0 16px;
  border-radius: 999px;
  background: #eef4ff;
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  white-space: nowrap;
}

.error-banner {
  margin: 0;
  padding: 14px 16px;
  border-radius: 14px;
  border: 1px solid #fecaca;
  background: #fef2f2;
  color: #b91c1c;
  font-weight: 700;
}

.state-card {
  min-height: 220px;
  border: 1px solid #e5eaf3;
  border-radius: 18px;
  background: #ffffff;
  display: grid;
  place-items: center;
  gap: 12px;
  color: #64748b;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.05);
}

.table-card {
  overflow: hidden;
}

.table-wrap {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  min-width: 1080px;
  border-collapse: collapse;
  table-layout: fixed;
}

.data-table th,
.data-table td {
  padding: 14px 16px;
  border-bottom: 1px solid #edf2f7;
  text-align: left;
  vertical-align: middle;
}

.data-table th {
  color: #64748b;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}

.data-table td {
  color: #0f172a;
  font-size: 14px;
  font-weight: 600;
}

.description {
  color: #64748b;
  font-weight: 500;
}

.module-pill,
.action-pill {
  display: inline-flex;
  align-items: center;
  min-height: 32px;
  padding: 0 12px;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 800;
}

.module-pill {
  background: #eff6ff;
  color: #2563eb;
}

.action-pill {
  background: #ecfdf5;
  color: #15803d;
}

.action-group {
  display: flex;
  gap: 8px;
}

.action-btn {
  width: 36px;
  height: 36px;
  border: 0;
  border-radius: 12px;
  display: inline-grid;
  place-items: center;
}

.action-btn.view {
  background: #eef4ff;
  color: #2563eb;
}

.action-btn.danger {
  background: #fff1f2;
  color: #dc2626;
}

.empty-state {
  min-height: 240px;
  display: grid;
  place-items: center;
  text-align: center;
  color: #64748b;
}

.empty-state i {
  margin-bottom: 10px;
  font-size: 34px;
  color: #2563eb;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1060;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  background: rgba(15, 23, 42, 0.45);
  backdrop-filter: blur(2px);
}

.modal-card {
  width: min(920px, 100%);
  max-height: calc(100vh - 48px);
  overflow: auto;
  border-radius: 20px;
  border: 1px solid #e5eaf3;
  background: #ffffff;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
}

.modal-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
  padding: 20px 22px 12px;
  border-bottom: 1px solid #edf1f7;
}

.modal-head h3 {
  margin: 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

.icon-close {
  width: 38px;
  height: 38px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #475569;
}

.modal-body {
  padding: 18px 22px 22px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group.full {
  grid-column: 1 / -1;
}

.form-group label {
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
}

.form-control,
.form-select {
  min-height: 44px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  box-shadow: none;
}

.form-textarea {
  min-height: 110px;
  resize: vertical;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 18px;
}

@media (max-width: 1199.98px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .toolbar-card {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .modal-backdrop {
    padding: 12px;
  }

  .modal-body,
  .modal-head {
    padding-left: 14px;
    padding-right: 14px;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column;
  }

  .secondary-action,
  .primary-action {
    width: 100%;
  }
}
</style>
