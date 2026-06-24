<script setup>
import {reactive, ref, watch} from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  category: {
    type: Object,
    default: null,
  },
  saving: {
    type: Boolean,
    default: false,
  },
  formError: {
    type: String,
    default: '',
  },
  fieldErrors: {
    type: Object,
    default: () => ({}),
  },
})

const emit = defineEmits(['close', 'submit'])

const manualSlug = ref(false)
const form = reactive({
  name: '',
  slug: '',
  description: '',
  status: 'active',
})

const slugify = (value) =>
    String(value ?? '')
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')

const resetForm = () => {
  form.name = ''
  form.slug = ''
  form.description = ''
  form.status = 'active'
  manualSlug.value = false
}

const syncForm = () => {
  const category = props.category

  if (!category) {
    resetForm()
    return
  }

  form.name = category?.name ?? ''
  form.slug = category?.slug ?? ''
  form.description = category?.description ?? ''
  form.status = category?.status ?? 'active'
  manualSlug.value = Boolean(category?.slug) && category.slug !== slugify(category?.name ?? '')
}

watch(
    () => props.visible,
    (visible) => {
      if (visible) {
        syncForm()
      } else {
        resetForm()
      }
    },
    {immediate: true}
)

watch(
    () => props.category?.id,
    () => {
      if (props.visible) {
        syncForm()
      }
    }
)

watch(
    () => form.name,
    (value) => {
      if (!manualSlug.value) {
        form.slug = slugify(value)
      }
    }
)

const handleSubmit = () => {
  emit('submit', {
    name: form.name.trim(),
    slug: form.slug.trim(),
    description: form.description.trim() || null,
    status: form.status,
  })
}
</script>

<template>
  <teleport to="body">
    <div v-if="visible" class="modal-backdrop" @click.self="$emit('close')">
      <div class="modal-card">
        <div class="modal-header">
          <div>
            <p class="modal-kicker">{{ category ? 'Chỉnh sửa danh mục' : 'Tạo danh mục' }}</p>
            <h3>{{ category ? 'Cập nhật danh mục' : 'Thêm danh mục mới' }}</h3>
          </div>

          <button type="button" class="modal-close" @click="$emit('close')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div v-if="formError" class="modal-alert">{{ formError }}</div>

        <form class="modal-form" @submit.prevent="handleSubmit">
          <div class="field">
            <label>Tên danh mục</label>
            <input v-model.trim="form.name" class="control" :class="{ invalid: fieldErrors.name }" type="text"
                   required/>
            <small v-if="fieldErrors.name" class="field-error">{{ fieldErrors.name }}</small>
          </div>

          <div class="field">
            <label>Slug</label>
            <input
                v-model.trim="form.slug"
                class="control"
                :class="{ invalid: fieldErrors.slug }"
                type="text"
                required
                @input="manualSlug = true"
            />
            <small class="field-hint">Slug tự sinh từ tên nếu bạn chưa chỉnh tay.</small>
            <small v-if="fieldErrors.slug" class="field-error">{{ fieldErrors.slug }}</small>
          </div>

          <div class="field">
            <label>Mô tả</label>
            <textarea
                v-model="form.description"
                class="control textarea"
                :class="{ invalid: fieldErrors.description }"
                rows="4"
            />
            <small v-if="fieldErrors.description" class="field-error">{{ fieldErrors.description }}</small>
          </div>

          <div class="field">
            <label>Trạng thái</label>
            <select v-model="form.status" class="control">
              <option value="active">Đang hoạt động</option>
              <option value="inactive">Tạm ẩn</option>
            </select>
          </div>

          <div class="modal-actions">
            <button type="button" class="secondary-action" @click="$emit('close')">Hủy</button>
            <button type="submit" class="primary-action" :disabled="saving">
              <i class="bi bi-check2"></i>
              {{ saving ? 'Đang lưu...' : 'Lưu danh mục' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </teleport>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1050;
  display: grid;
  place-items: center;
  padding: 20px;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(4px);
}

.modal-card {
  width: min(100%, 720px);
  border-radius: 22px;
  background: #ffffff;
  overflow: hidden;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
  padding: 20px 22px 16px;
  border-bottom: 1px solid #edf0f5;
}

.modal-kicker {
  margin: 0 0 4px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.modal-header h3 {
  margin: 0;
  color: #0f172a;
  font-size: 24px;
  font-weight: 850;
}

.modal-close {
  width: 38px;
  height: 38px;
  display: inline-grid;
  place-items: center;
  border: 0;
  border-radius: 12px;
  background: #f1f5f9;
  color: #475569;
}

.modal-alert {
  padding: 14px 22px 0;
  color: #dc2626;
}

.modal-form {
  padding: 20px 22px 22px;
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
  color: #334155;
  font-size: 14px;
  font-weight: 700;
}

.control {
  width: 100%;
  min-height: 44px;
  padding: 11px 14px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  color: #0f172a;
  outline: none;
}

.textarea {
  min-height: 118px;
  resize: vertical;
}

.control:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.control.invalid {
  border-color: #ef4444;
}

.field-hint {
  color: #64748b;
  font-size: 12px;
}

.field-error {
  color: #dc2626;
  font-size: 12px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  padding-top: 4px;
}

.primary-action,
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
  border: 1px solid transparent;
}

.primary-action {
  color: #ffffff;
  background: #2563eb;
}

.secondary-action {
  color: #334155;
  background: #ffffff;
  border-color: #dbe3ef;
}

@media (max-width: 767.98px) {
  .modal-card {
    max-height: 92vh;
    overflow: auto;
  }

  .modal-header {
    padding: 18px;
  }

  .modal-form {
    padding: 18px;
  }

  .modal-actions {
    flex-direction: column-reverse;
  }
}
</style>
