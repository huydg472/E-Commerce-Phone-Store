<script setup>
import {formatDate} from '@/utils/formatDate'

defineProps({
  categories: {
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
  <section class="table-card">
    <div class="table-header">
      <div>
        <h2>Danh sách danh mục</h2>
        <p>Quản lý tên, slug và trạng thái của từng danh mục.</p>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table align-middle admin-table mb-0">
        <thead>
        <tr>
          <th>Danh mục</th>
          <th>Slug</th>
          <th>Trạng thái</th>
          <th>Cập nhật</th>
          <th>Thao tác</th>
        </tr>
        </thead>

        <tbody>
        <tr v-for="category in categories" :key="category.id">
          <td>
            <div class="category-cell">
              <div class="category-icon">
                <i class="bi bi-grid-3x3-gap"></i>
              </div>
              <div>
                <strong>{{ category.name }}</strong>
                <span>{{ category.description || 'Chưa có mô tả' }}</span>
              </div>
            </div>
          </td>
          <td>
            <span class="slug-pill">{{ category.slug }}</span>
          </td>
          <td>
            <button
                type="button"
                class="status-pill"
                :class="category.status === 'active' ? 'is-active' : 'is-inactive'"
                :disabled="loading"
                @click="emit('toggle', category)"
                :title="category.status === 'active' ? 'Hoạt động' : 'Tạm ẩn'"
                :aria-label="category.status === 'active' ? 'Hoạt động' : 'Tạm ẩn'"
            >
              <i :class="category.status === 'active' ? 'bi bi-toggle-on' : 'bi bi-toggle-off'"></i>
            </button>
          </td>
          <td>{{ formatDate(category.updated_at || category.created_at) }}</td>
          <td>
            <div class="action-group">
              <button type="button" class="action-btn action-edit" title="Chỉnh sửa" @click="emit('edit', category)">
                <i class="bi bi-pencil"></i>
              </button>
              <button
                  type="button"
                  class="action-btn action-delete"
                  title="Xóa"
                  :disabled="deletingId === category.id"
                  @click="emit('delete', category)"
              >
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </td>
        </tr>

        <tr v-if="!categories.length">
          <td colspan="5">
            <div class="empty-state">
              <i class="bi bi-folder2-open"></i>
              <p>Không có danh mục phù hợp với từ khóa tìm kiếm.</p>
              <button type="button" class="secondary-action" @click="$emit('edit', null)">Tạo danh mục mới</button>
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

.table-header {
  padding: 18px 22px 14px;
  border-bottom: 1px solid #edf0f5;
}

.table-header h2 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
}

.table-header p {
  margin: 6px 0 0;
  color: #64748b;
  font-size: 14px;
}

.table-responsive {
  overflow-x: auto;
}

.admin-table {
  min-width: 980px;
}

.admin-table thead th {
  height: 56px;
  color: #64748b;
  background: #f8fbff;
  border-bottom: 1px solid #edf0f5;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
}

.admin-table tbody td {
  height: 78px;
  color: #0f172a;
  border-bottom: 1px solid #edf0f5;
  font-size: 14px;
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

.category-cell {
  display: flex;
  align-items: center;
  gap: 14px;
}

.category-icon {
  width: 48px;
  height: 48px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  color: #2563eb;
  background: #eff6ff;
}

.category-cell strong {
  display: block;
  font-weight: 800;
}

.category-cell span {
  display: block;
  margin-top: 4px;
  color: #64748b;
}

.slug-pill {
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

.secondary-action {
  min-height: 44px;
  padding: 0 16px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}
</style>
