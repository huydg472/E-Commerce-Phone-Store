<script setup>
import {reactive, ref, watch} from 'vue'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  brand: {
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
  type: 'phone',
  logo_url: '',
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
  form.type = 'phone'
  form.logo_url = ''
  form.description = ''
  form.status = 'active'
  manualSlug.value = false
}

const syncForm = () => {
  const brand = props.brand

  if (!brand) {
    resetForm()
    return
  }

  form.name = brand?.name ?? ''
  form.slug = brand?.slug ?? ''
  form.type = brand?.type ?? 'phone'
  form.logo_url = brand?.logo_url ?? ''
  form.description = brand?.description ?? ''
  form.status = brand?.status ?? 'active'
  manualSlug.value = true
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
    () => props.brand?.id,
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

const handleClose = () => {
  emit('close')
}

const handleSubmit = () => {
  emit('submit', {
    name: form.name.trim(),
    slug: form.slug.trim(),
    type: form.type,
    logo_url: form.logo_url.trim() || null,
    description: form.description.trim() || null,
    status: form.status,
  })
}
</script>

<template>
  <teleport to="body">
    <div v-if="visible" class="modal-backdrop" @click.self="handleClose">
      <div class="modal-card">
        <div class="modal-head">
          <div>
            <p>{{ brand ? 'Chỉnh sửa thương hiệu' : 'Thêm thương hiệu' }}</p>
            <h3>{{ form.name || 'Xem trước thương hiệu' }}</h3>
          </div>

          <button type="button" class="icon-close" @click="handleClose">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div v-if="formError" class="alert alert-danger mb-0">
          {{ formError }}
        </div>

        <div class="modal-body">
          <form class="modal-form" @submit.prevent="handleSubmit">
            <div class="form-row">
              <label>
                <span>Tên thương hiệu</span>
                <input
                    v-model.trim="form.name"
                    type="text"
                    class="control"
                    :class="{ invalid: fieldErrors.name }"
                />
                <small v-if="fieldErrors.name" class="field-error">{{ fieldErrors.name }}</small>
              </label>

              <label>
                <span>Slug</span>
                <input
                    v-model.trim="form.slug"
                    type="text"
                    class="control"
                    :class="{ invalid: fieldErrors.slug }"
                    @input="manualSlug = true"
                />
                <small v-if="fieldErrors.slug" class="field-error">{{ fieldErrors.slug }}</small>
              </label>
            </div>

            <div class="form-row">
              <label>
                <span>Loại thương hiệu</span>
                <select v-model="form.type" class="control" :class="{ invalid: fieldErrors.type }">
                  <option value="phone">Điện thoại</option>
                  <option value="accessory">Phụ kiện</option>
                </select>
                <small v-if="fieldErrors.type" class="field-error">{{ fieldErrors.type }}</small>
              </label>

              <label>
                <span>Trạng thái</span>
                <select v-model="form.status" class="control" :class="{ invalid: fieldErrors.status }">
                  <option value="active">Hoạt động</option>
                  <option value="inactive">Tạm ẩn</option>
                </select>
                <small v-if="fieldErrors.status" class="field-error">{{ fieldErrors.status }}</small>
              </label>
            </div>

            <label class="full-width">
              <span>Logo URL</span>
              <input
                  v-model.trim="form.logo_url"
                  type="url"
                  class="control"
                  :class="{ invalid: fieldErrors.logo_url }"
              />
              <small v-if="fieldErrors.logo_url" class="field-error">{{ fieldErrors.logo_url }}</small>
            </label>

            <label class="full-width">
              <span>Mô tả</span>
              <textarea
                  v-model.trim="form.description"
                  rows="4"
                  class="control"
                  :class="{ invalid: fieldErrors.description }"
                  placeholder="Mô tả ngắn về thương hiệu..."
              ></textarea>
              <small v-if="fieldErrors.description" class="field-error">{{ fieldErrors.description }}</small>
            </label>

            <div class="form-actions">
              <button type="button" class="secondary-action" @click="handleClose">Hủy</button>
              <button type="submit" class="primary-action" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                <span>{{ brand ? 'Lưu thay đổi' : 'Tạo thương hiệu' }}</span>
              </button>
            </div>
          </form>

          <aside class="preview-card">
            <div class="preview-logo" :class="{ empty: !form.logo_url }">
              <img v-if="form.logo_url" :src="form.logo_url" :alt="form.name || 'Brand preview'"/>
              <i v-else class="bi bi-award"></i>
            </div>

            <div class="preview-meta">
              <h4>{{ form.name || 'Tên thương hiệu' }}</h4>
              <p>{{ form.slug || 'slug-thuong-hieu' }}</p>
              <span class="preview-type" :class="form.type === 'accessory' ? 'is-accessory' : 'is-phone'">
                {{ form.type === 'accessory' ? 'Phụ kiện' : 'Điện thoại' }}
              </span>
              <span class="preview-status" :class="form.status === 'active' ? 'is-active' : 'is-inactive'">
                {{ form.status === 'active' ? 'Hoạt động' : 'Tạm ẩn' }}
              </span>
            </div>

            <p class="preview-description">
              {{ form.description || 'Phần mô tả thương hiệu sẽ hiển thị ở đây.' }}
            </p>
          </aside>
        </div>
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
  width: min(100%, 1040px);
  border-radius: 22px;
  background: #ffffff;
  overflow: hidden;
  box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
}

.modal-head {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  align-items: flex-start;
  padding: 20px 22px 16px;
  border-bottom: 1px solid #edf0f5;
}

.modal-head p {
  margin: 0 0 4px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.modal-head h3 {
  margin: 0;
  color: #0f172a;
  font-size: 24px;
  font-weight: 850;
}

.icon-close {
  width: 38px;
  height: 38px;
  display: inline-grid;
  place-items: center;
  border: 0;
  border-radius: 12px;
  background: #f1f5f9;
  color: #475569;
}

.modal-body {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) 320px;
}

.modal-form {
  padding: 20px 22px 22px;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.full-width {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.modal-form label {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.modal-form label span {
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

textarea.control {
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

.field-error {
  color: #dc2626;
  font-size: 12px;
}

.form-actions {
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
  border: none;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
  color: #ffffff;
}

.secondary-action {
  border: 1px solid #dbe3ef;
  color: #334155;
  background: #ffffff;
}

.preview-card {
  padding: 22px;
  border-left: 1px solid #edf0f5;
  background: linear-gradient(180deg, #f8fbff, #ffffff);
}

.preview-logo {
  width: 100%;
  height: 180px;
  border-radius: 18px;
  background: linear-gradient(135deg, #eff6ff, #ffffff);
  display: grid;
  place-items: center;
  font-size: 42px;
  color: #2563eb;
  overflow: hidden;
}

.preview-logo.empty {
  color: #64748b;
}

.preview-logo img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-meta {
  margin-top: 16px;
}

.preview-meta h4 {
  margin: 0 0 6px;
  color: #0f172a;
  font-size: 20px;
  font-weight: 850;
}

.preview-meta p {
  margin: 0 0 10px;
  color: #64748b;
}

.preview-type,
.preview-status {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 800;
  margin-right: 8px;
}

.preview-type.is-phone {
  color: #1d4ed8;
  background: #eff6ff;
}

.preview-type.is-accessory {
  color: #0f766e;
  background: #f0fdfa;
}

.preview-status.is-active {
  color: #15803d;
  background: #ecfdf5;
}

.preview-status.is-inactive {
  color: #b45309;
  background: #fff7ed;
}

.preview-description {
  margin: 16px 0 0;
  color: #475569;
  line-height: 1.6;
}

@media (max-width: 1199.98px) {
  .modal-body {
    grid-template-columns: 1fr;
  }

  .preview-card {
    border-left: 0;
    border-top: 1px solid #edf0f5;
  }
}

@media (max-width: 767.98px) {
  .modal-card {
    max-height: 92vh;
    overflow: auto;
  }

  .modal-head {
    padding: 18px;
  }

  .modal-form,
  .preview-card {
    padding: 18px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column-reverse;
  }
}
</style>
