<script setup>
defineProps({
  open: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  hasSavedAddresses: {
    type: Boolean,
    default: false,
  },
  addressForm: {
    type: Object,
    required: true,
  },
  saving: {
    type: Boolean,
    default: false,
  },
  successMessage: {
    type: String,
    default: '',
  },
})

defineEmits(['close', 'save'])
</script>

<template>
  <teleport to="body">
    <div v-if="open" class="address-modal-overlay" @click.self="$emit('close')">
      <div class="address-modal">
        <div class="address-modal__header">
          <div>
            <h3>{{ title }}</h3>
            <p>
              {{
                hasSavedAddresses
                    ? 'Lưu địa chỉ mới vào Sổ địa chỉ, sau đó chọn ngay cho đơn hàng.'
                    : 'Bạn chưa có địa chỉ nào. Hãy nhập địa chỉ để lưu và dùng cho đơn hàng này.'
              }}
            </p>
          </div>
          <button type="button" class="address-modal__close" @click="$emit('close')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div v-if="successMessage" class="address-modal__success">
          <i class="bi bi-check-circle-fill"></i>
          <span>{{ successMessage }}</span>
        </div>

        <div class="form-grid">
          <div class="form-group">
            <label>Họ và tên <span>*</span></label>
            <input v-model.trim="addressForm.receiver_name" type="text" class="form-control"
                   placeholder="Nhập họ và tên">
          </div>

          <div class="form-group">
            <label>Số điện thoại <span>*</span></label>
            <input v-model.trim="addressForm.receiver_phone" type="text" class="form-control"
                   placeholder="Nhập số điện thoại">
          </div>

          <div class="form-group">
            <label>Tỉnh/Thành phố <span>*</span></label>
            <input v-model.trim="addressForm.province" type="text" class="form-control"
                   placeholder="Nhập tỉnh/thành phố">
          </div>

          <div class="form-group">
            <label>Quận/Huyện <span>*</span></label>
            <input v-model.trim="addressForm.district" type="text" class="form-control" placeholder="Nhập quận/huyện">
          </div>

          <div class="form-group">
            <label>Phường/Xã <span>*</span></label>
            <input v-model.trim="addressForm.ward" type="text" class="form-control" placeholder="Nhập phường/xã">
          </div>

          <div class="form-group">
            <label>Địa chỉ cụ thể <span>*</span></label>
            <input v-model.trim="addressForm.address_detail" type="text" class="form-control"
                   placeholder="Số nhà, tên đường, tòa nhà, căn hộ...">
          </div>
        </div>

        <div class="form-group form-group-full note-group">
          <label>Ghi chú</label>
          <textarea
              v-model.trim="addressForm.note"
              class="form-control"
              rows="3"
              placeholder="Ví dụ: Giao hàng giờ hành chính, gọi trước khi giao..."
          ></textarea>
        </div>

        <div class="address-modal__footer">
          <button type="button" class="btn btn-outline-secondary" @click="$emit('close')">
            Hủy
          </button>
          <button type="button" class="btn btn-primary" :disabled="saving" @click="$emit('save')">
            {{ saving ? 'Đang lưu...' : 'Lưu địa chỉ' }}
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

