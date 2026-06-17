<script setup>
defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Thêm biến thể',
  },
  productName: {
    type: String,
    default: '',
  },
  form: {
    type: Object,
    required: true,
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

defineEmits(['close', 'submit'])
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
          <div class="grid-2">
            <div class="field">
              <label>Màu sắc</label>
              <input v-model="form.color" type="text" class="control" :class="{ invalid: fieldErrors.color }">
              <small v-if="fieldErrors.color" class="field-error">{{ fieldErrors.color }}</small>
            </div>
            <div class="field">
              <label>Dung lượng</label>
              <input v-model="form.storage" type="text" class="control" :class="{ invalid: fieldErrors.storage }" placeholder="128GB">
              <small v-if="fieldErrors.storage" class="field-error">{{ fieldErrors.storage }}</small>
            </div>
          </div>

          <div class="grid-2">
            <div class="field">
              <label>RAM</label>
              <input v-model="form.ram" type="text" class="control" :class="{ invalid: fieldErrors.ram }" placeholder="8GB">
              <small v-if="fieldErrors.ram" class="field-error">{{ fieldErrors.ram }}</small>
            </div>
            <div class="field">
              <label>SKU</label>
              <input v-model="form.sku" type="text" class="control readonly-control" :class="{ invalid: fieldErrors.sku }" readonly>
              <small class="field-hint">SKU tu sinh theo san pham, mau sac, dung luong va RAM.</small>
              <small v-if="fieldErrors.sku" class="field-error">{{ fieldErrors.sku }}</small>
            </div>
          </div>

          <div class="grid-3">
            <div class="field">
              <label>Giá nhập</label>
              <input v-model="form.import_price" type="number" min="0" class="control" :class="{ invalid: fieldErrors.import_price }">
              <small v-if="fieldErrors.import_price" class="field-error">{{ fieldErrors.import_price }}</small>
            </div>
            <div class="field">
              <label>Giá bán</label>
              <input v-model="form.price" type="number" min="0" class="control" :class="{ invalid: fieldErrors.price }" required>
              <small v-if="fieldErrors.price" class="field-error">{{ fieldErrors.price }}</small>
            </div>
            <div class="field">
              <label>Giá sale</label>
              <input v-model="form.sale_price" type="number" min="0" class="control" :class="{ invalid: fieldErrors.sale_price }">
              <small v-if="fieldErrors.sale_price" class="field-error">{{ fieldErrors.sale_price }}</small>
            </div>
          </div>

          <div class="grid-2">
            <div class="field">
              <label>Số lượng</label>
              <input v-model="form.quantity" type="number" min="0" class="control" :class="{ invalid: fieldErrors.quantity }">
              <small v-if="fieldErrors.quantity" class="field-error">{{ fieldErrors.quantity }}</small>
            </div>
            <div class="field">
              <label>Trạng thái</label>
              <select v-model="form.status" class="control" :class="{ invalid: fieldErrors.status }">
                <option value="active">Đang hoạt động</option>
                <option value="inactive">Tạm ẩn</option>
              </select>
              <small v-if="fieldErrors.status" class="field-error">{{ fieldErrors.status }}</small>
            </div>
          </div>

          <label class="featured-toggle">
            <input v-model="form.is_featured" type="checkbox">
            <span class="featured-toggle-box">
              <strong>Nổi bật</strong>
              <small>Ưu tiên biến thể này ở khu vực sản phẩm nổi bật.</small>
            </span>
          </label>

          <div class="field">
            <label>Mô tả</label>
            <textarea v-model="form.description" class="control textarea" rows="4"></textarea>
          </div>

          <div class="modal-actions">
            <button type="button" class="secondary-action" @click="$emit('close')">Hủy</button>
            <button type="submit" class="primary-action" :disabled="saving">
              <i class="bi bi-check2"></i>
              {{ saving ? 'Đang lưu...' : 'Lưu biến thể' }}
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
  width: min(100%, 860px);
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

.grid-2,
.grid-3 {
  display: grid;
  gap: 14px;
}

.grid-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.grid-3 {
  grid-template-columns: repeat(3, minmax(0, 1fr));
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

.textarea {
  min-height: 110px;
  padding-top: 12px;
  resize: vertical;
}

.control.invalid {
  border-color: #fca5a5;
  box-shadow: 0 0 0 3px rgba(252, 165, 165, 0.12);
}

.readonly-control {
  background: #f8fafc;
  color: #475569;
}

.field-hint {
  color: #64748b;
  font-size: 12px;
}

.field-error {
  color: #dc2626;
  font-size: 12px;
  font-weight: 700;
}

.featured-toggle {
  padding: 14px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #f8fbff;
  display: flex;
  align-items: flex-start;
  gap: 12px;
}

.featured-toggle input {
  width: 18px;
  height: 18px;
  margin-top: 2px;
  accent-color: #2563eb;
  flex: 0 0 auto;
}

.featured-toggle-box {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.featured-toggle-box strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
}

.featured-toggle-box small {
  color: #64748b;
  font-size: 12px;
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
  .grid-2,
  .grid-3 {
    grid-template-columns: 1fr;
  }

  .modal-actions {
    flex-direction: column-reverse;
  }

  .secondary-action,
  .primary-action {
    justify-content: center;
  }
}
</style>
