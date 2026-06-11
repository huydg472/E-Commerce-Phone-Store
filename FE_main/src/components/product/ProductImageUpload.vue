<script setup>
defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Thêm ảnh',
  },
  productName: {
    type: String,
    default: '',
  },
  form: {
    type: Object,
    required: true,
  },
  variants: {
    type: Array,
    default: () => [],
  },
  fieldErrors: {
    type: Object,
    default: () => ({}),
  },
  formError: {
    type: String,
    default: '',
  },
  saving: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['close', 'submit', 'image-change'])
</script>

<template>
  <Teleport to="body">
    <div v-if="visible" class="modal-backdrop" @click.self="$emit('close')">
      <div class="modal-card">
        <div class="modal-header">
          <div>
            <p class="modal-kicker">{{ title }}</p>
            <h3>{{ productName }}</h3>
          </div>
          <button type="button" class="modal-close" @click="$emit('close')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div v-if="formError" class="modal-alert">{{ formError }}</div>

        <form class="modal-form" @submit.prevent="$emit('submit')">
          <div class="field">
            <label>Biến thể</label>
            <select v-model="form.product_variant_id" class="control" :class="{ invalid: fieldErrors.product_variant_id }">
              <option value="">-- Chọn biến thể --</option>
              <option v-for="variant in variants" :key="variant.id" :value="String(variant.id)">
                {{ variant.color }} · {{ variant.storage }} · {{ variant.ram }}
              </option>
            </select>
            <small v-if="fieldErrors.product_variant_id" class="field-error">{{ fieldErrors.product_variant_id }}</small>
          </div>

          <div class="field">
            <label>Đường dẫn ảnh</label>
            <input
                type="file"
                accept="image/png,image/jpeg,image/webp"
                class="control file-control"
                :class="{ invalid: fieldErrors.image_file }"
                @change="$emit('image-change', $event)"
            >
            <small class="field-hint">Chon anh tu may tinh de luu vao project.</small>
            <small v-if="fieldErrors.image_file" class="field-error">{{ fieldErrors.image_file }}</small>
            <div v-if="form.image_preview_url || form.image_url" class="image-preview">
              <img :src="form.image_preview_url || form.image_url" :alt="form.alt_text || 'Xem truoc anh'">
            </div>
            <small v-if="fieldErrors.image_url" class="field-error">{{ fieldErrors.image_url }}</small>
          </div>

          <div class="field">
            <label>Mô tả ảnh</label>
            <input v-model="form.alt_text" type="text" class="control" :class="{ invalid: fieldErrors.alt_text }">
            <small v-if="fieldErrors.alt_text" class="field-error">{{ fieldErrors.alt_text }}</small>
          </div>

          <div class="field">
            <label>Thứ tự hiển thị</label>
            <input v-model="form.sort_order" type="number" min="0" class="control" :class="{ invalid: fieldErrors.sort_order }">
            <small v-if="fieldErrors.sort_order" class="field-error">{{ fieldErrors.sort_order }}</small>
          </div>

          <div class="modal-actions">
            <button type="button" class="secondary-action" @click="$emit('close')">Hủy</button>
            <button type="submit" class="primary-action" :disabled="saving">
              <i class="bi bi-check2"></i>
              {{ saving ? 'Đang lưu...' : 'Lưu ảnh' }}
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
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 20px;
  z-index: 1050;
}

.modal-card {
  width: min(100%, 720px);
  max-height: calc(100vh - 40px);
  overflow: auto;
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
}

.modal-header {
  padding: 18px 20px;
  border-bottom: 1px solid #edf2f7;
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.modal-header h3 {
  margin: 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

.modal-kicker {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.modal-close {
  width: 36px;
  height: 36px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #334155;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.modal-alert {
  margin: 16px 20px 0;
  padding: 12px 14px;
  border: 1px solid #fecaca;
  border-radius: 14px;
  background: #fff7f7;
  color: #b91c1c;
  font-size: 13px;
  font-weight: 700;
}

.modal-form {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.field label {
  color: #0f172a;
  font-size: 13px;
  font-weight: 800;
}

.control {
  min-height: 44px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #0f172a;
  font-size: 14px;
  outline: none;
}

.file-control {
  padding: 9px 14px;
}

.field-hint {
  color: #64748b;
  font-size: 12px;
}

.image-preview {
  width: 120px;
  height: 120px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  overflow: hidden;
  background: #f8fafc;
}

.image-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.control.invalid {
  border-color: #fca5a5;
  box-shadow: 0 0 0 3px rgba(252, 165, 165, 0.12);
}

.field-error {
  color: #dc2626;
  font-size: 12px;
  font-weight: 700;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 6px;
}

.secondary-action,
.primary-action {
  min-height: 44px;
  padding: 0 16px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
}

.secondary-action {
  border: 1px solid #dbe3ef;
  background: #ffffff;
  color: #334155;
}

.primary-action {
  border: none;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
}

@media (max-width: 768px) {
  .modal-actions {
    flex-direction: column-reverse;
  }

  .secondary-action,
  .primary-action {
    justify-content: center;
  }
}
</style>
