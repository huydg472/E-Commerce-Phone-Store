<script setup>
import {formatDate} from '@/utils/formatDate'

defineProps({
  users: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['toggle-status'])

const isActive = (status) => status === 'active'

const statusClass = (status) => {
  return isActive(status) ? 'is-active' : 'is-inactive'
}

const roleLabel = (user) => {
  return user?.role?.display_name || user?.role?.name || 'Không xác định'
}
</script>

<template>
  <section class="table-card">
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
          <th>Thao tác</th>
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
                class="status-pill"
                :class="statusClass(user.status)"
                :disabled="loading"
                @click="emit('toggle-status', user)"
                :aria-label="isActive(user.status) ? 'Tắt trạng thái hoạt động' : 'Bật trạng thái hoạt động'"
                :title="isActive(user.status) ? 'Đang hoạt động' : 'Tạm ẩn'"
            >
              <i :class="isActive(user.status) ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
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
  </section>
</template>

<style scoped>
.table-card {
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  min-width: 1080px;
}

.admin-table thead th {
  padding: 16px 20px;
  border-bottom: 1px solid #e5eaf3;
  color: #64748b;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
  text-align: left;
}

.admin-table tbody td {
  padding: 16px 20px;
  border-bottom: 1px solid #eef2f7;
  color: #0f172a;
  font-size: 14px;
  vertical-align: middle;
  text-align: left;
}

.user-name-cell {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.user-name-cell small {
  color: #64748b;
}

.status-pill {
  width: 42px;
  height: 34px;
  padding: 0;
  border: 1px solid transparent;
  border-radius: 999px;
  position: relative;
  overflow: hidden;
  flex: 0 0 auto;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  font-weight: 800;
  transition: 0.2s ease;
}

.status-pill i {
  position: absolute;
  inset: 0;
  display: grid;
  place-items: center;
  font-size: 16px;
  line-height: 1;
}

.status-pill.is-active {
  background: #ecfdf5;
  color: #15803d;
  border-color: #bbf7d0;
}

.status-pill.is-inactive {
  background: #fff7ed;
  color: #c2410c;
  border-color: #fed7aa;
}

.action-group {
  display: flex;
  justify-content: flex-start;
  gap: 8px;
}

.action-btn {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  border: 0;
  text-decoration: none;
  line-height: 1;
}

.action-view {
  color: #2563eb;
  background: #eff6ff;
}

.action-edit {
  color: #7c3aed;
  background: #f3e8ff;
}

.empty-state {
  min-height: 180px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  color: #64748b;
  text-align: center;
}
</style>
