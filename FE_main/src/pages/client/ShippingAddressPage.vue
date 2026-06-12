<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import {shippingAddressService} from '@/services/shippingAddressService'

const noticeMessage = ref('')
const loading = ref(false)
const saving = ref(false)
const modalOpen = ref(false)
const mode = ref('create')
const editingId = ref(null)
const addresses = ref([])

const form = reactive({
  receiver_name: '',
  receiver_phone: '',
  province: '',
  district: '',
  ward: '',
  address_detail: '',
  note: '',
  is_default: false,
})

const unwrapAddressList = (response) => {
  const candidates = [
    response?.data?.data,
    response?.data,
    response?.data?.data?.data,
  ]

  for (const candidate of candidates) {
    if (Array.isArray(candidate)) {
      return candidate
    }

    if (Array.isArray(candidate?.data)) {
      return candidate.data
    }

    if (Array.isArray(candidate?.items)) {
      return candidate.items
    }
  }

  return []
}

const formatAddressLine = (address) => {
  return [
    address?.address_detail,
    address?.ward,
    address?.district,
    address?.province,
  ]
      .filter(Boolean)
      .join(', ')
}

const normalizeAddress = (address) => ({
  id: address.id,
  receiver_name: address.receiver_name || '',
  receiver_phone: address.receiver_phone || '',
  province: address.province || '',
  district: address.district || '',
  ward: address.ward || '',
  address_detail: address.address_detail || '',
  note: address.note || '',
  isDefault: Boolean(address.is_default),
  addressLine: formatAddressLine(address),
})

const resetForm = () => {
  form.receiver_name = ''
  form.receiver_phone = ''
  form.province = ''
  form.district = ''
  form.ward = ''
  form.address_detail = ''
  form.note = ''
  form.is_default = false
  editingId.value = null
  mode.value = 'create'
}

const closeModal = () => {
  modalOpen.value = false
  resetForm()
}

const openCreateModal = () => {
  resetForm()
  noticeMessage.value = ''
  modalOpen.value = true
}

const openEditModal = (address) => {
  mode.value = 'edit'
  editingId.value = address.id
  form.receiver_name = address.receiver_name
  form.receiver_phone = address.receiver_phone
  form.province = address.province
  form.district = address.district
  form.ward = address.ward
  form.address_detail = address.address_detail
  form.note = address.note
  form.is_default = address.isDefault
  noticeMessage.value = ''
  modalOpen.value = true
}

const loadAddresses = async () => {
  loading.value = true

  try {
    const response = await shippingAddressService.getAll()
    addresses.value = unwrapAddressList(response).map(normalizeAddress)
  } catch {
    addresses.value = []
  } finally {
    loading.value = false
  }
}

const defaultAddress = computed(() => addresses.value.find((item) => item.isDefault) || null)

const validateForm = () => {
  return Boolean(
      form.receiver_name &&
      form.receiver_phone &&
      form.province &&
      form.district &&
      form.ward &&
      form.address_detail
  )
}

const submitForm = async () => {
  if (!validateForm()) {
    noticeMessage.value = 'Vui lòng nhập đầy đủ thông tin bắt buộc.'
    return
  }

  saving.value = true
  noticeMessage.value = ''

  try {
    const payload = {
      receiver_name: form.receiver_name,
      receiver_phone: form.receiver_phone,
      province: form.province,
      district: form.district,
      ward: form.ward,
      address_detail: form.address_detail,
      note: form.note,
      is_default: form.is_default,
    }

    if (mode.value === 'edit' && editingId.value) {
      await shippingAddressService.update(editingId.value, payload)
      noticeMessage.value = 'Đã cập nhật địa chỉ.'
    } else {
      await shippingAddressService.create(payload)
      noticeMessage.value = 'Đã thêm địa chỉ mới.'
    }

    await loadAddresses()
    closeModal()
  } catch (error) {
    noticeMessage.value = error.response?.data?.message || 'Không thể lưu địa chỉ.'
  } finally {
    saving.value = false
  }
}

const setDefault = async (id) => {
  const target = addresses.value.find((item) => item.id === id)

  if (!target || target.isDefault) {
    return
  }

  try {
    noticeMessage.value = ''

    await shippingAddressService.update(id, {
      receiver_name: target.receiver_name,
      receiver_phone: target.receiver_phone,
      province: target.province,
      district: target.district,
      ward: target.ward,
      address_detail: target.address_detail,
      note: target.note,
      is_default: true,
    })

    await loadAddresses()
    noticeMessage.value = 'Đã cập nhật địa chỉ mặc định.'
  } catch {
    noticeMessage.value = 'Không thể cập nhật địa chỉ mặc định.'
  }
}

const removeAddress = async (id) => {
  try {
    noticeMessage.value = ''
    await shippingAddressService.delete(id)
    await loadAddresses()
    noticeMessage.value = 'Đã xóa địa chỉ.'
  } catch {
    noticeMessage.value = 'Không thể xóa địa chỉ.'
  }
}

onMounted(() => {
  void loadAddresses()
})
</script>

<template>
  <section class="account-page">
    <div class="page-head">
      <div>
        <h1 class="page-title mb-1">Sổ địa chỉ</h1>
        <p class="page-subtitle mb-0">Quản lý địa chỉ giao hàng, thêm mới hoặc chỉnh sửa địa chỉ cũ.</p>
      </div>

      <button type="button" class="btn btn-primary btn-sm" @click="openCreateModal">
        Thêm địa chỉ mới
      </button>
    </div>

    <div v-if="noticeMessage" class="success-banner mb-3">
      <i class="bi bi-info-circle"></i>
      <span>{{ noticeMessage }}</span>
    </div>

    <section class="account-card">
      <div class="section-top">
        <h5 class="section-title mb-0">Danh sách địa chỉ</h5>
        <span class="count-pill">{{ addresses.length }} địa chỉ</span>
      </div>

      <div v-if="loading" class="empty-state">
        <i class="bi bi-arrow-repeat"></i>
        <p>Đang tải danh sách địa chỉ...</p>
      </div>

      <div v-else>
        <div v-if="defaultAddress" class="default-preview">
          <span>Địa chỉ mặc định</span>
          <strong>{{ defaultAddress.receiver_name || 'Chưa có tên người nhận' }}</strong>
          <p>{{ defaultAddress.addressLine || 'Chưa có địa chỉ chi tiết' }}</p>
        </div>

        <div class="address-list">
          <article v-for="item in addresses" :key="item.id" class="address-item">
            <div class="address-item-head">
              <div>
                <h6>{{ item.receiver_name || 'Địa chỉ' }}</h6>
                <p>
                  {{ item.receiver_name }}{{ item.receiver_phone ? ` - ${item.receiver_phone}` : '' }}
                </p>
              </div>

              <span v-if="item.isDefault" class="badge-pill">Mặc định</span>
            </div>

            <p class="address-line">{{ item.addressLine }}</p>

            <div class="item-actions">
              <button type="button" class="text-action" @click="openEditModal(item)">
                Sửa
              </button>
              <button type="button" class="text-action" :disabled="item.isDefault" @click="setDefault(item.id)">
                Đặt mặc định
              </button>
              <button type="button" class="text-action danger" @click="removeAddress(item.id)">
                Xóa
              </button>
            </div>
          </article>

          <div v-if="addresses.length === 0" class="empty-state">
            <i class="bi bi-geo-alt"></i>
            <p>Bạn chưa có địa chỉ nào.</p>
          </div>
        </div>
      </div>
    </section>

    <teleport to="body">
      <div v-if="modalOpen" class="modal-overlay" @click.self="closeModal">
        <div class="address-modal" role="dialog" aria-modal="true" aria-labelledby="address-modal-title">
          <div class="modal-header">
            <div>
              <h5 id="address-modal-title" class="modal-title">
                {{ mode === 'edit' ? 'Sửa địa chỉ' : 'Thêm địa chỉ mới' }}
              </h5>
              <p class="modal-subtitle mb-0">
                Nhập thông tin địa chỉ giao hàng để lưu vào sổ địa chỉ.
              </p>
            </div>

            <button type="button" class="modal-close" aria-label="Đóng" @click="closeModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <form class="address-form" @submit.prevent="submitForm">
            <div class="form-grid">
              <div class="form-group">
                <label>Họ và tên <span>*</span></label>
                <input v-model.trim="form.receiver_name" type="text" class="form-control" placeholder="Nhập họ và tên">
              </div>

              <div class="form-group">
                <label>Số điện thoại <span>*</span></label>
                <input v-model.trim="form.receiver_phone" type="text" class="form-control" placeholder="Nhập số điện thoại">
              </div>

              <div class="form-group">
                <label>Tỉnh/Thành phố <span>*</span></label>
                <input v-model.trim="form.province" type="text" class="form-control" placeholder="Nhập tỉnh/thành phố">
              </div>

              <div class="form-group">
                <label>Quận/Huyện <span>*</span></label>
                <input v-model.trim="form.district" type="text" class="form-control" placeholder="Nhập quận/huyện">
              </div>

              <div class="form-group">
                <label>Phường/Xã <span>*</span></label>
                <input v-model.trim="form.ward" type="text" class="form-control" placeholder="Nhập phường/xã">
              </div>

              <div class="form-group">
                <label>Địa chỉ chi tiết <span>*</span></label>
                <input v-model.trim="form.address_detail" type="text" class="form-control" placeholder="Số nhà, tên đường, tòa nhà...">
              </div>
            </div>

            <div class="form-group form-group-full">
              <label>Ghi chú</label>
              <textarea
                  v-model.trim="form.note"
                  class="form-control"
                  rows="3"
                  placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi giao..."
              ></textarea>
            </div>

            <div class="form-footer">
              <label class="default-switch">
                <input v-model="form.is_default" type="checkbox">
                <span>Đặt làm địa chỉ mặc định</span>
              </label>

              <div class="form-actions">
                <button type="button" class="btn btn-outline-secondary" @click="closeModal">
                  Hủy
                </button>
                <button type="submit" class="btn btn-primary" :disabled="saving">
                  {{ saving ? 'Đang lưu...' : (mode === 'edit' ? 'Cập nhật địa chỉ' : 'Thêm địa chỉ') }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </teleport>
  </section>
</template>

<style scoped>
.account-page {
  color: #0f172a;
  font-size: 14px;
}

.page-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.page-title {
  font-size: 28px;
  font-weight: 750;
  color: #111827;
}

.page-subtitle {
  color: #64748b;
}

.success-banner {
  padding: 12px 14px;
  border: 1px solid #bfdbfe;
  border-radius: 12px;
  background: #eff6ff;
  color: #1d4ed8;
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
}

.account-card {
  padding: 18px 22px;
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
}

.section-title {
  font-size: 17px;
  font-weight: 750;
  color: #111827;
}

.section-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
}

.count-pill {
  padding: 6px 12px;
  border-radius: 999px;
  background: #eef4ff;
  color: #2563eb;
  font-weight: 700;
  font-size: 12px;
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

.form-group label span {
  color: #ef4444;
}

.form-group .form-control {
  height: 42px;
  border: 1px solid #dbe3ef;
  border-radius: 9px;
  box-shadow: none;
  color: #334155;
  font-size: 14px;
  font-weight: 500;
}

.form-group textarea.form-control {
  height: auto;
  min-height: 72px;
  resize: vertical;
  padding-top: 10px;
}

.default-preview {
  padding: 14px 16px;
  margin-bottom: 14px;
  border: 1px solid #dbeafe;
  border-radius: 12px;
  background: #eff6ff;
}

.default-preview span {
  display: block;
  margin-bottom: 6px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.default-preview strong {
  display: block;
  margin-bottom: 6px;
  color: #111827;
  font-size: 15px;
}

.default-preview p {
  margin: 0;
  color: #334155;
  line-height: 1.5;
}

.address-list {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.address-item {
  padding: 14px 16px;
  border: 1px solid #eef2f7;
  border-radius: 12px;
  background: #fafcff;
}

.address-item-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.address-item h6 {
  margin: 0 0 6px;
  color: #111827;
  font-size: 15px;
  font-weight: 750;
}

.address-item p {
  margin: 0;
  color: #64748b;
  line-height: 1.5;
}

.address-line {
  margin-top: 10px !important;
}

.badge-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 5px 10px;
  border-radius: 999px;
  background: #dbeafe;
  color: #2563eb;
  font-size: 12px;
  font-weight: 700;
  white-space: nowrap;
}

.item-actions {
  display: flex;
  gap: 14px;
  margin-top: 12px;
  flex-wrap: wrap;
}

.text-action {
  border: 0;
  padding: 0;
  background: transparent;
  color: #2563eb;
  font-weight: 700;
}

.text-action.danger {
  color: #dc2626;
}

.text-action:disabled {
  color: #94a3b8;
  cursor: not-allowed;
}

.empty-state {
  padding: 28px 20px;
  text-align: center;
  color: #64748b;
}

.empty-state i {
  font-size: 34px;
  color: #2563eb;
}

.empty-state p {
  margin: 8px 0 0;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1060;
  background: rgba(15, 23, 42, 0.55);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 18px;
}

.address-modal {
  width: min(100%, 760px);
  max-height: min(90vh, 900px);
  overflow: auto;
  background: #ffffff;
  border-radius: 18px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 24px 80px rgba(15, 23, 42, 0.28);
  padding: 20px;
}

.modal-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 18px;
}

.modal-title {
  margin: 0 0 6px;
  color: #111827;
  font-size: 20px;
  font-weight: 800;
}

.modal-subtitle {
  color: #64748b;
}

.modal-close {
  width: 36px;
  height: 36px;
  border: 0;
  border-radius: 999px;
  background: #f1f5f9;
  color: #0f172a;
  display: grid;
  place-items: center;
}

.address-form {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px 16px;
}

.form-group-full {
  margin-top: 0;
}

.form-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  flex-wrap: wrap;
}

.default-switch {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  color: #334155;
  font-weight: 600;
}

.default-switch input {
  width: 16px;
  height: 16px;
}

.form-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.form-actions .btn {
  min-width: 132px;
}

@media (max-width: 768px) {
  .page-title {
    font-size: 24px;
  }

  .page-head,
  .section-top,
  .address-item-head,
  .modal-header,
  .form-footer {
    flex-direction: column;
    align-items: flex-start;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .item-actions {
    flex-direction: column;
    gap: 8px;
  }

  .modal-overlay {
    padding: 12px;
  }

  .address-modal {
    padding: 16px;
    border-radius: 14px;
  }
}
</style>
