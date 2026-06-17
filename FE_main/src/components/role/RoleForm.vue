<script setup>
import {computed} from 'vue'
import PermissionCheckboxGroup from '@/components/role/PermissionCheckboxGroup.vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  form: {
    type: Object,
    required: true,
  },
  permissions: {
    type: Array,
    default: () => [],
  },
  selectedPermissionIds: {
    type: Array,
    default: () => [],
  },
  errorMessage: {
    type: String,
    default: '',
  },
  submitting: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Tạo vai trò mới',
  },
  submitLabel: {
    type: String,
    default: 'Lưu',
  },
})

const emit = defineEmits(['close', 'submit', 'update:selectedPermissionIds'])

const selectedIds = computed({
  get: () => props.selectedPermissionIds,
  set: (value) => emit('update:selectedPermissionIds', value),
})
</script>

<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-backdrop" @click.self="emit('close')">
      <div class="modal-card" role="dialog" aria-modal="true">
        <div class="modal-head">
          <div>
            <p class="eyebrow">Quản lý vai trò</p>
            <h3>{{ title }}</h3>
          </div>

          <button type="button" class="icon-close" @click="emit('close')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div v-if="errorMessage" class="alert alert-danger mb-3">
          {{ errorMessage }}
        </div>

        <form class="modal-body" @submit.prevent="emit('submit')">
          <div class="form-grid">
            <div class="form-group">
              <label>Mã vai trò</label>
              <input v-model.trim="form.name" type="text" class="form-control" placeholder="VD: manager" required/>
            </div>

            <div class="form-group">
              <label>Tên hiển thị</label>
              <input v-model.trim="form.display_name" type="text" class="form-control" placeholder="VD: Quản lý"
                     required/>
            </div>

            <div class="form-group">
              <label>Trạng thái</label>
              <select v-model="form.status" class="form-select">
                <option value="active">Hoạt động</option>
                <option value="inactive">Không hoạt động</option>
              </select>
            </div>

            <div class="form-group full">
              <label>Mô tả</label>
              <textarea
                  v-model.trim="form.description"
                  class="form-control form-textarea"
                  rows="4"
                  placeholder="Mô tả ngắn về vai trò..."
              ></textarea>
            </div>
          </div>

          <div class="permission-head">
            <div>
              <h4>Phân quyền</h4>
              <p>Chọn các quyền mà vai trò này được phép sử dụng.</p>
            </div>
            <span>{{ selectedIds.length }} quyền đã chọn</span>
          </div>

          <PermissionCheckboxGroup
              v-model="selectedIds"
              :permissions="permissions"
          />

          <div class="form-actions">
            <button type="button" class="secondary-action" @click="emit('close')">Hủy</button>
            <button type="submit" class="primary-action" :disabled="submitting">
              <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
              {{ submitLabel }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
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
  width: min(980px, 100%);
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

.eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
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
  margin-bottom: 18px;
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

.permission-head {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 12px;
}

.permission-head h4 {
  margin: 0 0 4px;
  color: #0f172a;
  font-size: 16px;
  font-weight: 850;
}

.permission-head p {
  margin: 0;
  color: #64748b;
  font-size: 13px;
}

.permission-head span {
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 18px;
}

.secondary-action,
.primary-action {
  min-height: 42px;
  padding: 0 16px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  border: none;
}

.secondary-action {
  background: #ffffff;
  border: 1px solid #dbe3ef;
  color: #334155;
}

.primary-action {
  background: linear-gradient(135deg, #2563eb, #0ea5e9);
  color: #ffffff;
  box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
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

  .permission-head {
    flex-direction: column;
    align-items: flex-start;
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
