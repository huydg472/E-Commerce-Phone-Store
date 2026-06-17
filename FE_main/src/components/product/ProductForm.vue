<script setup>
import {computed} from 'vue'

const props = defineProps({
  form: {
    type: Object,
    required: true,
  },
  brands: {
    type: Array,
    default: () => [],
  },
  categories: {
    type: Array,
    default: () => [],
  },
  fieldErrors: {
    type: Object,
    default: () => ({}),
  },
  saving: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  submitLabel: {
    type: String,
    default: 'Lưu thay đổi',
  },
  cancelTo: {
    type: [String, Object],
    default: '/admin/products',
  },
})

defineEmits(['submit', 'thumbnail-change'])

const isSelected = (item, field) => {
  return String(item?.id ?? '') === String(props.form?.[field] ?? '')
}

const isInactive = (item) => String(item?.status ?? '').toLowerCase() === 'inactive'

const visibleBrands = computed(() => {
  return props.brands.filter((brand) => !isInactive(brand) || isSelected(brand, 'brand_id'))
})

const visibleCategories = computed(() => {
  return props.categories.filter((category) => !isInactive(category) || isSelected(category, 'category_id'))
})
</script>

<template>
  <div class="form-layout">
    <form class="form-card" @submit.prevent="$emit('submit')">
      <div class="form-section">
        <div class="section-head">
          <div>
            <h2>Thông tin cơ bản</h2>
            <p>Tên, slug và liên kết phân loại sản phẩm.</p>
          </div>
        </div>

        <div class="grid-2">
          <div class="field">
            <label>Thương hiệu</label>
            <select v-model="form.brand_id" class="control" :class="{ invalid: fieldErrors.brand_id }" required>
              <option value="">Chọn thương hiệu</option>
              <option
                  v-for="brand in visibleBrands"
                  :key="brand.id"
                  :value="String(brand.id)"
                  :disabled="isInactive(brand)"
              >
                {{ brand.name }}{{ isInactive(brand) ? ' (Tạm ẩn)' : '' }}
              </option>
            </select>
            <small v-if="fieldErrors.brand_id" class="field-error">{{ fieldErrors.brand_id }}</small>
          </div>

          <div class="field">
            <label>Danh mục</label>
            <select v-model="form.category_id" class="control" :class="{ invalid: fieldErrors.category_id }" required>
              <option value="">Chọn danh mục</option>
              <option
                  v-for="category in visibleCategories"
                  :key="category.id"
                  :value="String(category.id)"
                  :disabled="isInactive(category)"
              >
                {{ category.name }}{{ isInactive(category) ? ' (Tạm ẩn)' : '' }}
              </option>
            </select>
            <small v-if="fieldErrors.category_id" class="field-error">{{ fieldErrors.category_id }}</small>
          </div>
        </div>

        <div class="field">
          <label>Tên sản phẩm</label>
          <input
              v-model="form.name"
              type="text"
              class="control readonly-control"
              :class="{ invalid: fieldErrors.name }"
              placeholder="Ví dụ: Samsung Galaxy A36 5G"
              required
          />
          <small v-if="fieldErrors.name" class="field-error">{{ fieldErrors.name }}</small>
        </div>

        <div class="field">
          <label>Slug</label>
          <input
              v-model="form.slug"
              type="text"
              class="control"
              :class="{ invalid: fieldErrors.slug }"
              placeholder="slug-san-pham"
              required
              readonly
          />
          <small class="field-hint">Slug tu sinh theo ten san pham.</small>
          <small v-if="fieldErrors.slug" class="field-error">{{ fieldErrors.slug }}</small>
        </div>
      </div>

      <div class="form-section">
        <div class="section-head">
          <div>
            <h2>Hình ảnh & mô tả</h2>
            <p>Tùy chỉnh ảnh, mô tả ngắn và nội dung chi tiết.</p>
          </div>
        </div>

        <div class="field">
          <label>Ảnh đại diện</label>
          <input
              type="file"
              accept="image/png,image/jpeg,image/webp"
              class="control file-control"
              :class="{ invalid: fieldErrors.thumbnail_file }"
              @change="$emit('thumbnail-change', $event)"
          />
          <small class="field-hint">Chon anh tu may tinh de luu vao project.</small>
          <small v-if="fieldErrors.thumbnail_file" class="field-error">{{ fieldErrors.thumbnail_file }}</small>
          <small v-if="fieldErrors.thumbnail_url" class="field-error">{{ fieldErrors.thumbnail_url }}</small>
        </div>

        <div class="grid-2">
          <div class="field">
            <label>Mô tả ngắn</label>
            <textarea
                v-model="form.short_description"
                class="control textarea"
                :class="{ invalid: fieldErrors.short_description }"
                rows="4"
            />
            <small v-if="fieldErrors.short_description" class="field-error">{{ fieldErrors.short_description }}</small>
          </div>

          <div class="field">
            <label>Mô tả chi tiết</label>
            <textarea
                v-model="form.description"
                class="control textarea"
                :class="{ invalid: fieldErrors.description }"
                rows="4"
            />
            <small v-if="fieldErrors.description" class="field-error">{{ fieldErrors.description }}</small>
          </div>
        </div>
      </div>

      <div class="form-section publish-grid">
        <div class="field">
          <label>Trạng thái</label>
          <select v-model="form.status" class="control" :class="{ invalid: fieldErrors.status }">
            <option value="active">Đang hoạt động</option>
            <option value="inactive">Tạm ẩn</option>
          </select>
          <small v-if="fieldErrors.status" class="field-error">{{ fieldErrors.status }}</small>
        </div>
      </div>

      <div class="form-actions">
        <RouterLink :to="cancelTo" class="secondary-action">
          Hủy
        </RouterLink>
        <button type="submit" class="primary-action" :disabled="saving || loading">
          <i class="bi bi-check2"></i>
          {{ submitLabel }}
        </button>
      </div>
    </form>

    <aside class="preview-card">
      <div class="preview-image">
        <img :src="form.thumbnail_preview_url || form.thumbnail_url || '/images/default-product.png'"
             :alt="form.name || 'Xem trước sản phẩm'">
      </div>

      <div class="preview-body">
        <p class="preview-kicker">Xem trước</p>
        <h3>{{ form.name || 'Tên sản phẩm' }}</h3>
        <p class="preview-slug">{{ form.slug || 'slug-san-pham' }}</p>

        <div class="preview-meta">
          <span>
            <i class="bi bi-tag"></i>
            {{ brands.find((brand) => String(brand.id) === String(form.brand_id))?.name || 'Chưa chọn thương hiệu' }}
          </span>
          <span>
            <i class="bi bi-grid-3x3-gap"></i>
            {{
              categories.find((category) => String(category.id) === String(form.category_id))?.name || 'Chưa chọn danh mục'
            }}
          </span>
          <span>
            <i class="bi bi-dot"></i>
            {{ form.status === 'active' ? 'Đang hoạt động' : 'Tạm ẩn' }}
          </span>
        </div>
      </div>
    </aside>
  </div>
</template>

<style scoped>
.form-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.4fr) minmax(290px, 0.9fr);
  gap: 18px;
}

.form-card,
.preview-card {
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.06);
}

.form-card {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-section {
  padding: 18px;
  border: 1px solid #edf2f7;
  border-radius: 18px;
  background: #fbfdff;
}

.section-head h2 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
}

.section-head p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 13px;
}

.grid-2,
.publish-grid {
  display: grid;
  gap: 14px;
}

.grid-2 {
  grid-template-columns: repeat(2, minmax(0, 1fr));
}

.publish-grid {
  grid-template-columns: 1fr;
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

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
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

.preview-card {
  overflow: hidden;
}

.preview-image {
  aspect-ratio: 1 / 1;
  background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%);
}

.preview-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.preview-body {
  padding: 18px;
}

.preview-kicker {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.preview-body h3 {
  margin: 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
  line-height: 1.2;
}

.preview-slug {
  margin: 8px 0 0;
  color: #64748b;
  font-size: 13px;
}

.preview-meta {
  margin-top: 16px;
  display: grid;
  gap: 10px;
}

.preview-meta span {
  min-height: 40px;
  padding: 0 12px;
  border-radius: 12px;
  background: #f8fafc;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #334155;
  font-size: 13px;
  font-weight: 700;
}

.preview-meta i {
  color: #2563eb;
}

@media (max-width: 1200px) {
  .form-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .form-card {
    padding: 16px;
  }

  .grid-2,
  .publish-grid {
    grid-template-columns: 1fr;
  }

  .form-actions {
    flex-direction: column-reverse;
  }

  .secondary-action,
  .primary-action {
    justify-content: center;
  }
}
</style>
