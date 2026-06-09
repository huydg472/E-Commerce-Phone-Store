<script setup>
defineProps({
  form: {
    type: Object,
    required: true,
  },
  roles: {
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
  submitLabel: {
    type: String,
    default: 'Lưu thay đổi',
  },
  cancelTo: {
    type: String,
    default: '',
  },
  showPasswordFields: {
    type: Boolean,
    default: true,
  },
  showEmailVerifiedAt: {
    type: Boolean,
    default: false,
  },
  passwordRequired: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['submit'])
</script>

<template>
  <div class="form-card">
    <div v-if="errorMessage" class="alert alert-danger mb-3">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="$emit('submit')">
      <div class="form-grid">
        <div class="form-group">
          <label>Vai trò</label>
          <select v-model="form.role_id" class="form-select" required>
            <option value="">Chọn vai trò</option>
            <option v-for="role in roles" :key="role.id" :value="String(role.id)">
              {{ role.display_name || role.name }}
            </option>
          </select>
        </div>

        <div class="form-group">
          <label>Trạng thái</label>
          <select v-model="form.status" class="form-select" required>
            <option value="active">Hoạt động</option>
            <option value="inactive">Không hoạt động</option>
          </select>
        </div>

        <div class="form-group">
          <label>Họ tên</label>
          <input v-model.trim="form.name" type="text" class="form-control" required/>
        </div>

        <div class="form-group">
          <label>Email</label>
          <input v-model.trim="form.email" type="email" class="form-control" required/>
        </div>

        <div class="form-group">
          <label>Số điện thoại</label>
          <input v-model.trim="form.phone" type="text" class="form-control" required/>
        </div>

        <div class="form-group">
          <label>Username</label>
          <input v-model.trim="form.username" type="text" class="form-control" required/>
        </div>

        <template v-if="showPasswordFields">
          <div class="form-group">
            <label>{{ passwordRequired ? 'Mật khẩu' : 'Mật khẩu mới' }}</label>
            <input
              v-model="form.password"
              type="password"
              class="form-control"
              :required="passwordRequired"
              :placeholder="passwordRequired ? '' : 'Để trống nếu không đổi'"
            />
          </div>

          <div class="form-group">
            <label>{{ passwordRequired ? 'Xác nhận mật khẩu' : 'Xác nhận mật khẩu mới' }}</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              class="form-control"
              :required="passwordRequired"
              :placeholder="passwordRequired ? '' : 'Nhập lại mật khẩu'"
            />
          </div>
        </template>

        <div v-if="showEmailVerifiedAt" class="form-group">
          <label>Email verified at</label>
          <input
            v-model="form.email_verified_at"
            type="text"
            class="form-control"
            placeholder="YYYY-MM-DD HH:mm:ss"
          />
        </div>
      </div>

      <div class="form-actions">
        <RouterLink v-if="cancelTo" :to="cancelTo" class="secondary-action">Hủy</RouterLink>
        <button v-else type="button" class="secondary-action" @click="$router.back()">Hủy</button>

        <button type="submit" class="primary-action" :disabled="submitting">
          <span v-if="submitting" class="spinner-border spinner-border-sm me-2"></span>
          {{ submitLabel }}
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
.form-card {
  padding: 22px;
  border: 1px solid #e5eaf3;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  margin-bottom: 6px;
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
}

.form-control,
.form-select {
  height: 44px;
  border: 1px solid #dbe3ef;
  border-radius: 10px;
  box-shadow: none;
}

.form-actions {
  margin-top: 18px;
  display: flex;
  justify-content: flex-end;
  gap: 10px;
}

.secondary-action,
.primary-action {
  min-height: 42px;
  padding: 0 14px;
  border-radius: 10px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
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
  background: #2563eb;
  color: #ffffff;
}

@media (max-width: 992px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}
</style>
