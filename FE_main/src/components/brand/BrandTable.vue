<script setup>
import {formatDate} from '@/utils/formatDate'

defineProps({
  brands: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  deletingId: {
    type: [Number, String, null],
    default: null,
  },
})

const emit = defineEmits(['edit', 'delete', 'toggle'])
</script>

<template>
  <div class="table-card">
    <div class="table-responsive">
      <table class="table align-middle admin-table mb-0">
        <thead>
        <tr>
          <th>Thương hiệu</th>
          <th>Slug</th>
          <th>Trạng thái</th>
          <th>Cập nhật</th>
          <th>Thao tác</th>
        </tr>
        </thead>

        <tbody>
        <tr v-for="brand in brands" :key="brand.id">
          <td>
            <div class="brand-cell">
              <div class="brand-logo" :class="{ placeholder: !brand.logo_url }">
                <img v-if="brand.logo_url" :src="brand.logo_url" :alt="brand.name"/>
                <i v-else class="bi bi-award"></i>
              </div>

              <div class="brand-text">
                <strong>{{ brand.name }}</strong>
                <small>{{ brand.description || 'Chưa có mô tả' }}</small>
              </div>
            </div>
          </td>
          <td>
            <code class="slug-chip">{{ brand.slug }}</code>
          </td>
          <td>
            <button
                type="button"
                class="status-pill"
                :class="brand.status === 'active' ? 'is-active' : 'is-inactive'"
                :disabled="loading"
                @click="emit('toggle', brand)"
                :title="brand.status === 'active' ? 'Hoạt động' : 'Tạm ẩn'"
                :aria-label="brand.status === 'active' ? 'Hoạt động' : 'Tạm ẩn'"
            >
              <i :class="brand.status === 'active' ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
            </button>
          </td>
          <td>{{ formatDate(brand.updated_at || brand.created_at) }}</td>
          <td>
            <div class="action-group">
              <button type="button" class="action-btn action-edit" @click="emit('edit', brand)" title="Chỉnh sửa">
                <i class="bi bi-pencil"></i>
              </button>
              <button
                  type="button"
                  class="action-btn action-delete"
                  :disabled="deletingId === brand.id"
                  @click="emit('delete', brand)"
                  title="Xóa"
              >
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="!brands.length">
          <td colspan="5">
            <div class="empty-state">
              <i class="bi bi-award"></i>
              <p>Không có thương hiệu phù hợp.</p>
            </div>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.table-card {
  border: 1px solid #e5e9f1;
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.05);
  overflow: hidden;
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  min-width: 980px;
}

.admin-table thead th {
  height: 56px;
  color: #0f172a;
  background: #f8fbff;
  border-bottom: 1px solid #edf0f5;
  font-size: 14px;
  font-weight: 800;
  white-space: nowrap;
}

.admin-table tbody td {
  height: 78px;
  color: #0f172a;
  border-bottom: 1px solid #edf0f5;
  font-size: 15px;
  white-space: nowrap;
  vertical-align: middle;
}

.admin-table tbody tr:last-child td {
  border-bottom: 0;
}

.admin-table th:first-child,
.admin-table td:first-child {
  padding-left: 22px;
}

.admin-table th:last-child,
.admin-table td:last-child {
  padding-right: 22px;
}

.brand-cell {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-logo {
  width: 52px;
  height: 52px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: #f1f5f9;
  overflow: hidden;
  color: #64748b;
}

.brand-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.brand-logo.placeholder {
  color: #64748b;
}

.brand-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.brand-text strong {
  font-weight: 800;
}

.brand-text small {
  color: #64748b;
}

.slug-chip {
  display: inline-flex;
  padding: 6px 10px;
  border-radius: 999px;
  background: #eef4ff;
  color: #1d4ed8;
  font-size: 13px;
  font-weight: 700;
}

.status-pill {
  width: 42px;
  height: 42px;
  display: inline-grid;
  place-items: center;
  border: 0;
  border-radius: 999px;
  font-size: 24px;
  line-height: 1;
}

.status-pill.is-active {
  color: #15803d;
  background: #ecfdf5;
}

.status-pill.is-inactive {
  color: #b45309;
  background: #fff7ed;
}

.action-group {
  display: flex;
  justify-content: flex-start;
  gap: 8px;
}

.action-btn {
  width: 40px;
  height: 40px;
  display: inline-grid;
  place-items: center;
  border-radius: 12px;
  border: 0;
  text-decoration: none;
}

.action-edit {
  color: #7c3aed;
  background: #f3e8ff;
}

.action-delete {
  color: #dc2626;
  background: #fee2e2;
}

.action-delete:disabled {
  opacity: 0.6;
  cursor: wait;
}

.empty-state {
  min-height: 180px;
  display: grid;
  place-items: center;
  gap: 10px;
  color: #64748b;
  text-align: center;
}

.empty-state i {
  font-size: 28px;
  color: #2563eb;
}
</style>
