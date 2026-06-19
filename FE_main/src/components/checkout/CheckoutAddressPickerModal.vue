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
  addresses: {
    type: Array,
    default: () => [],
  },
  selectedShippingAddressId: {
    type: [String, Number],
    default: '',
  },
})

defineEmits(['close', 'choose', 'add-new'])
</script>

<template>
  <teleport to="body">
    <div v-if="open" class="address-modal-overlay" @click.self="$emit('close')">
      <div class="address-modal">
        <div class="address-modal__header">
          <div>
            <h3>{{ title }}</h3>
            <p v-if="hasSavedAddresses">
              Chọn một địa chỉ khác đang có trong Sổ địa chỉ để dùng cho đơn này.
            </p>
            <p v-else>
              Bạn chưa có địa chỉ nào trong Sổ địa chỉ.
            </p>
          </div>
          <button type="button" class="address-modal__close" @click="$emit('close')">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div v-if="hasSavedAddresses" class="saved-address-list">
          <button
              v-for="address in addresses"
              :key="address.id"
              type="button"
              class="saved-address-item"
              :class="{ active: String(address.id) === String(selectedShippingAddressId) }"
              @click="$emit('choose', address)"
          >
            <div class="saved-address-item__icon">
              <i class="bi bi-geo-alt-fill"></i>
            </div>
            <div class="saved-address-item__content">
              <div class="saved-address-item__header">
                <strong>{{ address.receiver_name }}</strong>
                <span v-if="address.is_default" class="saved-address-item__badge">Mặc định</span>
              </div>
              <p>
                {{
                  [
                    address.address_detail,
                    address.ward,
                    address.district,
                    address.province,
                  ].filter(Boolean).join(', ')
                }}
              </p>
              <small>
                <i class="bi bi-telephone"></i>
                {{ address.receiver_phone || 'Chưa có số điện thoại' }}
              </small>
            </div>
          </button>
        </div>

        <div v-else class="address-modal__empty">
          <i class="bi bi-geo-alt"></i>
          <p>Chưa có địa chỉ nào trong Sổ địa chỉ.</p>
          <button type="button" class="btn btn-primary" @click="$emit('add-new')">
            Thêm địa chỉ mới
          </button>
        </div>

        <div v-if="hasSavedAddresses" class="address-modal__footer">
          <button type="button" class="btn btn-outline-secondary" @click="$emit('add-new')">
            Thêm địa chỉ mới
          </button>
        </div>
      </div>
    </div>
  </teleport>
</template>

