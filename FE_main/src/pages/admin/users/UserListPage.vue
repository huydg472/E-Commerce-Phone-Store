<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import BasePagination from '@/components/common/BasePagination.vue'
import { userService } from '@/services/userService'
import { useUserStore } from '@/stores/userStore'
import { formatDate } from '@/utils/formatDate'

const userStore = useUserStore()

const search = ref('')
const currentPage = ref(1)
const pageSize = 10
const loadingError = ref('')
let searchTimer = null

const users = computed(() => (Array.isArray(userStore.items) ? userStore.items : []))

const pagination = computed(() => {
  return userStore.pagination || {
    current_page: 1,
    last_page: 1,
    total: 0,
  }
})

const pageStart = computed(() => {
  if (!users.value.length) return 0
  return (pagination.value.current_page - 1) * pageSize + 1
})

const pageEnd = computed(() => {
  return Math.min(pageStart.value + users.value.length - 1, pagination.value.total)
})

const isActive = (status) => status === 'active'

const statusClass = (status) => {
  return isActive(status) ? 'is-active' : 'is-inactive'
}

const roleLabel = (user) => {
  return user?.role?.display_name || user?.role?.name || 'Không xác định'
}

const fetchUsers = async (page = currentPage.value) => {
  loadingError.value = ''

  try {
    const response = await userStore.fetchAll({
      page,
      per_page: pageSize,
      q: search.value.trim() || undefined,
    })

    const meta = response.data?.meta || response.data?.data?.meta || userStore.pagination
    currentPage.value = meta?.current_page || page
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được danh sách người dùng.'
  }
}

const handleSearchInput = () => {
  currentPage.value = 1

  if (searchTimer) {
    clearTimeout(searchTimer)
  }

  searchTimer = setTimeout(() => {
    fetchUsers(1)
  }, 250)
}

const handlePageChange = (page) => {
  currentPage.value = page
  fetchUsers(page)
}

const handleStatusToggle = async (user) => {
  loadingError.value = ''
  const nextStatus = isActive(user.status) ? 'inactive' : 'active'
  const previousStatus = user.status
  const previousUpdatedAt = user.updated_at

  user.status = nextStatus

  try {
    const response = await userService.update(user.id, {
      status: nextStatus,
    })

    const updatedUser = response.data?.data ?? response.data ?? null
    if (updatedUser?.updated_at) {
      user.updated_at = updatedUser.updated_at
    }
  } catch (error) {
    user.status = previousStatus
    user.updated_at = previousUpdatedAt
    loadingError.value = error.response?.data?.message || 'Không cập nhật được trạng thái người dùng.'
  }
}

onMounted(() => {
  fetchUsers(1)
})

watch(search, () => {
  handleSearchInput()
})
</script>

<template>
  <div class="admin-page">
    <div class="page-head">
      <div>
        <p class="eyebrow">Quản lý người dùng</p>
        <h1>Danh sách người dùng</h1>
        <p class="subtitle">Xem, tạo, sửa và xóa tài khoản quản trị viên, nhân viên, khách hàng.</p>
      </div>

      <RouterLink to="/admin/users/create" class="primary-action">
        <i class="bi bi-plus-lg"></i>
        Tạo người dùng
      </RouterLink>
    </div>

    <div class="toolbar">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input
          v-model.trim="search"
          type="search"
          placeholder="Tìm theo tên, username, email, vai trò..."
        />
      </div>

      <div class="meta-chip">
        <i class="bi bi-people"></i>
        <span>{{ pagination.total }} người dùng</span>
      </div>
    </div>

    <div v-if="userStore.loading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải danh sách người dùng...</p>
    </div>

    <div v-else-if="loadingError" class="state-card error-state">
      <i class="bi bi-exclamation-triangle"></i>
      <p>{{ loadingError }}</p>
      <button type="button" class="secondary-action" @click="fetchUsers(currentPage)">Thử lại</button>
    </div>

    <div v-else class="table-card">
      <div class="table-responsive">
        <table class="table align-middle admin-table mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Họ tên</th>
              <th>Username</th>
              <th>Vai trò</th>
              <th>Trạng thái</th>
              <th>Email</th>
              <th>Cập nhật</th>
              <th class="text-end">Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>#{{ user.id }}</td>
              <td>
                <div class="user-name-cell">
                  <strong>{{ user.name }}</strong>
                  <small>{{ user.phone }}</small>
                </div>
              </td>
              <td>{{ user.username }}</td>
              <td>{{ roleLabel(user) }}</td>
              <td>
                <button
                  type="button"
                  class="status-toggle"
                  :class="statusClass(user.status)"
                  :disabled="userStore.loading"
                  @click="handleStatusToggle(user)"
                  :aria-label="isActive(user.status) ? 'Tắt trạng thái hoạt động' : 'Bật trạng thái hoạt động'"
                >
                  <span class="status-toggle-track">
                    <span class="status-toggle-thumb"></span>
                  </span>
                </button>
              </td>
              <td>{{ user.email }}</td>
              <td>{{ formatDate(user.updated_at || user.created_at) }}</td>
              <td>
                <div class="action-group">
                  <RouterLink :to="`/admin/users/${user.id}`" class="action-btn action-view" title="Xem chi tiết">
                    <i class="bi bi-eye"></i>
                  </RouterLink>
                  <RouterLink :to="`/admin/users/${user.id}/edit`" class="action-btn action-edit" title="Chỉnh sửa">
                    <i class="bi bi-pencil"></i>
                  </RouterLink>
                </div>
              </td>
            </tr>

            <tr v-if="!users.length">
              <td colspan="8">
                <div class="empty-state">
                  <i class="bi bi-person-x"></i>
                  <p>Không có người dùng phù hợp.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="table-footer">
        <div class="table-summary">
          Hiển thị {{ pageStart }}-{{ pageEnd }} trong tổng số {{ pagination.total }} người dùng
        </div>

        <BasePagination
          :current-page="currentPage"
          :total-pages="pagination.last_page"
          @update:currentPage="handlePageChange"
        />
      </div>
    </div>
  </div>
</template>

<style scoped>
.admin-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.page-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
}

.eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.page-head h1 {
  margin: 0;
  color: #0f172a;
  font-size: 30px;
  font-weight: 900;
}

.subtitle {
  margin: 8px 0 0;
  color: #64748b;
  font-size: 14px;
}

.primary-action,
.secondary-action {
  min-height: 42px;
  padding: 0 14px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
}

.primary-action {
  border: none;
  background: #2563eb;
  color: #ffffff;
}

.secondary-action {
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}

.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.search-box {
  min-width: min(100%, 420px);
  height: 46px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 10px;
}

.search-box i {
  color: #64748b;
}

.search-box input {
  width: 100%;
  border: none;
  outline: none;
  font-size: 14px;
}

.meta-chip {
  min-height: 42px;
  padding: 0 14px;
  border-radius: 999px;
  background: #eff6ff;
  color: #2563eb;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  font-weight: 800;
}

.state-card,
.table-card {
  border: 1px solid #e5eaf3;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.state-card {
  min-height: 240px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  color: #475569;
}

.state-card.error-state {
  color: #dc2626;
}

.state-card i {
  font-size: 28px;
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  min-width: 1120px;
}

.admin-table thead th {
  padding: 16px;
  border-bottom: 1px solid #e5eaf3;
  color: #64748b;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}

.admin-table tbody td {
  padding: 16px;
  border-bottom: 1px solid #eef2f7;
  color: #0f172a;
  font-size: 14px;
}

.user-name-cell strong {
  display: block;
  font-size: 14px;
  font-weight: 800;
}

.user-name-cell small {
  display: block;
  margin-top: 4px;
  color: #64748b;
  font-size: 12px;
}

.status-toggle {
  min-width: 58px;
  height: 32px;
  padding: 0;
  border: none;
  border-radius: 999px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: transparent;
  position: relative;
  transition: transform 0.2s ease;
  cursor: pointer;
  user-select: none;
}

.status-toggle:hover {
  transform: scale(1.02);
}

.status-toggle:disabled {
  opacity: 0.75;
  cursor: not-allowed;
}

.status-toggle-track {
  width: 52px;
  height: 28px;
  border-radius: 999px;
  padding: 3px;
  display: inline-flex;
  align-items: center;
  position: relative;
  flex: 0 0 auto;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.35);
  transition: background 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.status-toggle-thumb {
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: #ffffff;
  box-shadow: 0 4px 10px rgba(15, 23, 42, 0.18);
  transform: translateX(0);
  transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}

.status-toggle.is-active {
  color: #166534;
}

.status-toggle.is-active .status-toggle-track {
  background: linear-gradient(135deg, #34d399 0%, #22c55e 100%);
}

.status-toggle.is-active .status-toggle-thumb {
  transform: translateX(22px);
}

.status-toggle.is-inactive {
  color: #92400e;
}

.status-toggle.is-inactive .status-toggle-track {
  background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
}

.action-group {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  text-decoration: none;
}

.action-view {
  background: #eff6ff;
  color: #2563eb;
}

.action-edit {
  background: #f5f3ff;
  color: #7c3aed;
}

.empty-state {
  padding: 42px 16px;
  text-align: center;
  color: #64748b;
}

.empty-state i {
  font-size: 34px;
  color: #94a3b8;
}

.empty-state p {
  margin: 10px 0 0;
  font-size: 14px;
  font-weight: 600;
}

.table-footer {
  padding: 18px 20px 22px;
  border-top: 1px solid #eef2f7;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.table-summary {
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
}

@media (max-width: 992px) {
  .page-head {
    flex-direction: column;
  }

  .primary-action {
    width: fit-content;
  }
}
</style>
