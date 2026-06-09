<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { usePermissionStore } from '@/stores/permissionStore'
import { useRoleStore } from '@/stores/roleStore'
import RoleForm from '@/components/role/RoleForm.vue'

const router = useRouter()
const roleStore = useRoleStore()
const permissionStore = usePermissionStore()

const pageLoading = ref(true)
const errorMessage = ref('')
const saving = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')
const showForm = ref(false)
const editingRoleId = ref(null)
const selectedPermissionIds = ref([])

const form = reactive({
  name: '',
  display_name: '',
  description: '',
  status: 'active',
})

const roles = computed(() => (Array.isArray(roleStore.items) ? roleStore.items : []))
const permissions = computed(() => (Array.isArray(permissionStore.items) ? permissionStore.items : []))

const filteredRoles = computed(() => {
  const query = searchQuery.value.trim().toLowerCase()

  return roles.value.filter((role) => {
    const matchesQuery = !query
      || [role.name, role.display_name, role.description]
          .filter(Boolean)
          .some((value) => String(value).toLowerCase().includes(query))

    const matchesStatus = statusFilter.value === 'all' || role.status === statusFilter.value

    return matchesQuery && matchesStatus
  })
})

const stats = computed(() => {
  const total = roles.value.length
  const active = roles.value.filter((item) => item.status === 'active').length
  const inactive = total - active
  const totalPermissions = roles.value.reduce((sum, item) => {
    const relation = item.permissions || []
    return sum + relation.length
  }, 0)

  return [
    {label: 'Tổng vai trò', value: total, icon: 'bi-shield-lock-fill', color: 'blue'},
    {label: 'Hoạt động', value: active, icon: 'bi-check-circle-fill', color: 'green'},
    {label: 'Không hoạt động', value: inactive, icon: 'bi-dash-circle-fill', color: 'slate'},
    {label: 'Quyền gán', value: totalPermissions, icon: 'bi-key-fill', color: 'orange'},
  ]
})

const modulePermissions = computed(() => permissions.value)

const loadPage = async () => {
  pageLoading.value = true
  errorMessage.value = ''

  try {
    await Promise.all([
      roleStore.fetchAll(),
      permissionStore.fetchAll(),
    ])
  } catch (error) {
    if (error.response?.status === 401) {
      await router.replace({name: 'login'})
      return
    }

    if (error.response?.status === 403) {
      await router.replace({name: 'forbidden'})
      return
    }

    errorMessage.value = error.response?.data?.message || 'Không tải được dữ liệu vai trò.'
  } finally {
    pageLoading.value = false
  }
}

const resetForm = () => {
  form.name = ''
  form.display_name = ''
  form.description = ''
  form.status = 'active'
  selectedPermissionIds.value = []
  editingRoleId.value = null
}

const openCreate = () => {
  resetForm()
  showForm.value = true
}

const openEdit = (role) => {
  form.name = role.name || ''
  form.display_name = role.display_name || ''
  form.description = role.description || ''
  form.status = role.status || 'active'
  selectedPermissionIds.value = Array.isArray(role.permissions)
    ? role.permissions.map((permission) => permission.id)
    : []
  editingRoleId.value = role.id
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
  description: form.description.trim() || null,
  status: form.status,
  permission_ids: selectedPermissionIds.value,
})

const handleSubmit = async () => {
  if (saving.value) {
    return
  }

  saving.value = true
  errorMessage.value = ''

  try {
    if (editingRoleId.value) {
      await roleStore.update(editingRoleId.value, payload())
    } else {
      await roleStore.create(payload())
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

    errorMessage.value = error.response?.data?.message || 'Lưu vai trò thất bại.'
  } finally {
    saving.value = false
  }
}

const toggleStatus = async (role) => {
  errorMessage.value = ''

  try {
    await roleStore.update(role.id, {
      status: role.status === 'active' ? 'inactive' : 'active',
    })
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không đổi được trạng thái.'
  }
}

const removeRole = async (role) => {
  if (!window.confirm(`Xóa vai trò "${role.display_name || role.name}"?`)) {
    return
  }

  try {
    await roleStore.remove(role.id)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không xóa được vai trò.'
  }
}

onMounted(loadPage)
</script>

<template>
  <div class="admin-page">
    <div class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Quản lý vai trò</p>
        <h1>Danh sách vai trò</h1>
        <p class="subtitle">Quản lý vai trò và phân quyền truy cập trong admin dashboard.</p>

        <div class="hero-actions">
          <button type="button" class="primary-action" @click="openCreate">
            <i class="bi bi-plus-lg"></i>
            Thêm vai trò
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
        <input v-model.trim="searchQuery" type="text" placeholder="Tìm theo tên, mô tả..." />
      </div>

      <select v-model="statusFilter" class="filter-select">
        <option value="all">Tất cả trạng thái</option>
        <option value="active">Hoạt động</option>
        <option value="inactive">Không hoạt động</option>
      </select>

      <div class="result-chip">
        <i class="bi bi-funnel"></i>
        {{ filteredRoles.length }} kết quả
      </div>
    </div>

    <p v-if="errorMessage" class="error-banner">{{ errorMessage }}</p>

    <div v-if="pageLoading" class="state-card">
      <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
      <p>Đang tải dữ liệu vai trò...</p>
    </div>

    <div v-else class="table-card">
      <div v-if="!filteredRoles.length" class="empty-state">
        <i class="bi bi-inboxes"></i>
        <p>Chưa có vai trò nào phù hợp bộ lọc.</p>
      </div>

      <div v-else class="table-wrap">
        <table class="data-table">
          <colgroup>
            <col class="col-name" />
            <col class="col-display" />
            <col class="col-desc" />
            <col class="col-count" />
            <col class="col-status" />
            <col class="col-actions" />
          </colgroup>

          <thead>
            <tr>
              <th>Mã vai trò</th>
              <th>Tên hiển thị</th>
              <th>Mô tả</th>
              <th>Quyền</th>
              <th>Trạng thái</th>
              <th>Thao tác</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="role in filteredRoles" :key="role.id">
              <td>
                <strong>{{ role.name }}</strong>
              </td>
              <td>
                <span>{{ role.display_name }}</span>
              </td>
              <td>
                <span class="description">{{ role.description || 'Không có mô tả' }}</span>
              </td>
              <td>
                <span class="count-pill">{{ role.permissions?.length || role.permissions_count || 0 }} quyền</span>
              </td>
              <td>
                <button
                  type="button"
                  class="status-pill"
                  :class="role.status === 'active' ? 'active' : 'inactive'"
                  @click="toggleStatus(role)"
                >
                  <i :class="role.status === 'active' ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
                  {{ role.status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                </button>
              </td>
              <td>
                <div class="action-group">
                  <button type="button" class="action-btn view" @click="openEdit(role)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button type="button" class="action-btn danger" @click="removeRole(role)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <RoleForm
      :visible="showForm"
      :form="form"
      :permissions="modulePermissions"
      v-model:selected-permission-ids="selectedPermissionIds"
      :error-message="errorMessage"
      :submitting="saving"
      :title="editingRoleId ? 'Chỉnh sửa vai trò' : 'Tạo vai trò mới'"
      :submit-label="editingRoleId ? 'Lưu thay đổi' : 'Tạo mới'"
      @close="closeForm"
      @submit="handleSubmit"
    />
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
  min-width: 1060px;
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

.count-pill {
  display: inline-flex;
  align-items: center;
  min-height: 32px;
  padding: 0 12px;
  border-radius: 999px;
  background: #ecfdf5;
  color: #15803d;
  font-size: 13px;
  font-weight: 800;
}

.status-pill {
  min-height: 34px;
  padding: 0 12px;
  border-radius: 999px;
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 800;
}

.status-pill.active {
  color: #15803d;
  background: #ecfdf5;
  border-color: #bbf7d0;
}

.status-pill.inactive {
  color: #c2410c;
  background: #fff7ed;
  border-color: #fed7aa;
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

.col-name { width: 18%; }
.col-display { width: 20%; }
.col-desc { width: 28%; }
.col-count { width: 10%; }
.col-status { width: 14%; }
.col-actions { width: 10%; }

@media (max-width: 1199.98px) {
  .hero-card {
    grid-template-columns: 1fr;
  }

  .toolbar-card {
    grid-template-columns: 1fr;
  }
}
</style>
