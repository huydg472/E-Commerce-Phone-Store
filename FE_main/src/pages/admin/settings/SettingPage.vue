<script setup>
import {computed, onMounted, reactive, ref} from 'vue'
import {storeToRefs} from 'pinia'
import {useAuthStore} from '@/stores/authStore'
import {useSettingsStore} from '@/stores/settingsStore'
import {PERMISSIONS} from '@/constants/permissions'

const authStore = useAuthStore()
const settingsStore = useSettingsStore()
const {item: settingsItem, loading, saving} = storeToRefs(settingsStore)

const errorMessage = ref('')
const successMessage = ref('')

const form = reactive({
  site_name: 'ZinMobile',
  brand_name: 'ZinMobile',
  slogan: '',
  logo_url: '',
  favicon_url: '',
  support_phone: '',
  support_email: '',
  contact_email: '',
  address: '',
  footer_description: '',
  facebook_url: '',
  instagram_url: '',
  tiktok_url: '',
  youtube_url: '',
  zalo_url: '',
  shipping_fee_standard: 0,
  shipping_fee_express: 40000,
  cash_on_delivery_note: '',
  bank_name: '',
  bank_account_number: '',
  bank_account_name: '',
  bank_transfer_note: '',
  maintenance_mode: false,
})

const canUpdate = computed(() => authStore.can(PERMISSIONS.SETTINGS.UPDATE))

const hydrateForm = (data) => {
  const source = data || {}

  form.site_name = source.site_name ?? 'ZinMobile'
  form.brand_name = source.brand_name ?? 'ZinMobile'
  form.slogan = source.slogan ?? ''
  form.logo_url = source.logo_url ?? ''
  form.favicon_url = source.favicon_url ?? ''
  form.support_phone = source.support_phone ?? ''
  form.support_email = source.support_email ?? ''
  form.contact_email = source.contact_email ?? ''
  form.address = source.address ?? ''
  form.footer_description = source.footer_description ?? ''
  form.facebook_url = source.facebook_url ?? ''
  form.instagram_url = source.instagram_url ?? ''
  form.tiktok_url = source.tiktok_url ?? ''
  form.youtube_url = source.youtube_url ?? ''
  form.zalo_url = source.zalo_url ?? ''
  form.shipping_fee_standard = Number(source.shipping_fee_standard ?? 0)
  form.shipping_fee_express = Number(source.shipping_fee_express ?? 40000)
  form.cash_on_delivery_note = source.cash_on_delivery_note ?? ''
  form.bank_name = source.bank_name ?? ''
  form.bank_account_number = source.bank_account_number ?? ''
  form.bank_account_name = source.bank_account_name ?? ''
  form.bank_transfer_note = source.bank_transfer_note ?? ''
  form.maintenance_mode = Boolean(source.maintenance_mode)
}

const loadSettings = async () => {
  errorMessage.value = ''

  try {
    await settingsStore.fetchAdmin(true)
    hydrateForm(settingsItem.value)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không tải được cài đặt hệ thống.'
  }
}

const resetForm = () => {
  hydrateForm(settingsItem.value)
  errorMessage.value = ''
  successMessage.value = ''
}

const handleSubmit = async () => {
  if (!canUpdate.value) {
    return
  }

  errorMessage.value = ''
  successMessage.value = ''

  try {
    await settingsStore.update({
      ...form,
      shipping_fee_standard: Number(form.shipping_fee_standard || 0),
      shipping_fee_express: Number(form.shipping_fee_express || 0),
      maintenance_mode: Boolean(form.maintenance_mode),
    })

    successMessage.value = 'Đã lưu cấu hình hệ thống.'
    hydrateForm(settingsItem.value)
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Không lưu được cấu hình.'
  }
}

onMounted(loadSettings)
</script>

<template>
  <div class="settings-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Cấu hình hệ thống</p>
        <h1>Cài đặt cửa hàng</h1>
        <p class="subtitle">
          Toàn bộ thông tin nhận diện, liên hệ, vận chuyển và thanh toán của hệ thống sẽ được quản lý tại đây.
        </p>
      </div>

      <div class="hero-preview">
        <div class="brand-pill">
          <strong>{{ form.brand_name || 'ZinMobile' }}</strong>
          <span>{{ form.slogan || 'Hệ thống bán lẻ điện thoại chính hãng' }}</span>
        </div>

        <div class="preview-stats">
          <article>
            <span>Tiêu chuẩn</span>
            <strong>{{ new Intl.NumberFormat('vi-VN').format(Number(form.shipping_fee_standard || 0)) }} đ</strong>
          </article>
          <article>
            <span>Nhanh</span>
            <strong>{{ new Intl.NumberFormat('vi-VN').format(Number(form.shipping_fee_express || 0)) }} đ</strong>
          </article>
        </div>
      </div>
    </section>

    <div v-if="loading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải cấu hình...</p>
    </div>

    <template v-else>
      <section class="grid-layout">
        <article class="settings-panel">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Thông tin chung</p>
              <h2>Nhận diện thương hiệu</h2>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Tên hệ thống</label>
              <input v-model.trim="form.site_name" type="text" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Tên thương hiệu</label>
              <input v-model.trim="form.brand_name" type="text" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Khẩu hiệu</label>
              <input v-model.trim="form.slogan" type="text" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Logo URL</label>
              <input v-model.trim="form.logo_url" type="url" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Favicon URL</label>
              <input v-model.trim="form.favicon_url" type="url" class="form-control"/>
            </div>
            <div class="form-group form-group--switch">
              <label>Chế độ bảo trì</label>
              <label class="switch">
                <input v-model="form.maintenance_mode" type="checkbox"/>
                <span>Đang bật</span>
              </label>
            </div>
          </div>
        </article>

        <article class="settings-panel">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Liên hệ</p>
              <h2>Thông tin hỗ trợ</h2>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Số điện thoại hỗ trợ</label>
              <input v-model.trim="form.support_phone" type="text" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Email hỗ trợ</label>
              <input v-model.trim="form.support_email" type="email" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Email liên hệ</label>
              <input v-model.trim="form.contact_email" type="email" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Địa chỉ</label>
              <input v-model.trim="form.address" type="text" class="form-control"/>
            </div>
            <div class="form-group form-group--full">
              <label>Mô tả footer</label>
              <textarea v-model.trim="form.footer_description" rows="4" class="form-control"></textarea>
            </div>
          </div>
        </article>

        <article class="settings-panel">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Kênh xã hội</p>
              <h2>Liên kết ngoài</h2>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Facebook</label>
              <input v-model.trim="form.facebook_url" type="url" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Instagram</label>
              <input v-model.trim="form.instagram_url" type="url" class="form-control"/>
            </div>
            <div class="form-group">
              <label>TikTok</label>
              <input v-model.trim="form.tiktok_url" type="url" class="form-control"/>
            </div>
            <div class="form-group">
              <label>YouTube</label>
              <input v-model.trim="form.youtube_url" type="url" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Zalo</label>
              <input v-model.trim="form.zalo_url" type="url" class="form-control"/>
            </div>
          </div>
        </article>

        <article class="settings-panel">
          <div class="panel-head">
            <div>
              <p class="panel-kicker">Vận chuyển & thanh toán</p>
              <h2>Thông số đơn hàng</h2>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Phí tiêu chuẩn</label>
              <input v-model.number="form.shipping_fee_standard" type="number" min="0" step="1000"
                     class="form-control"/>
            </div>
            <div class="form-group">
              <label>Phí giao nhanh</label>
              <input v-model.number="form.shipping_fee_express" type="number" min="0" step="1000" class="form-control"/>
            </div>
            <div class="form-group form-group--full">
              <label>Ghi chú COD</label>
              <textarea v-model.trim="form.cash_on_delivery_note" rows="3" class="form-control"></textarea>
            </div>
            <div class="form-group">
              <label>Ngân hàng</label>
              <input v-model.trim="form.bank_name" type="text" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Số tài khoản</label>
              <input v-model.trim="form.bank_account_number" type="text" class="form-control"/>
            </div>
            <div class="form-group">
              <label>Chủ tài khoản</label>
              <input v-model.trim="form.bank_account_name" type="text" class="form-control"/>
            </div>
            <div class="form-group form-group--full">
              <label>Ghi chú chuyển khoản</label>
              <textarea v-model.trim="form.bank_transfer_note" rows="3" class="form-control"></textarea>
            </div>
          </div>
        </article>
      </section>

      <div class="action-bar">
        <p v-if="errorMessage" class="status-text status-text--error">{{ errorMessage }}</p>
        <p v-else-if="successMessage" class="status-text status-text--success">{{ successMessage }}</p>

        <div class="action-buttons">
          <button type="button" class="secondary-action" @click="resetForm">
            <i class="bi bi-arrow-counterclockwise"></i>
            Khôi phục
          </button>

          <button
              type="button"
              class="primary-action"
              :disabled="saving || !canUpdate"
              @click="handleSubmit"
          >
            <span v-if="saving" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            <i v-else class="bi bi-save"></i>
            Lưu cài đặt
          </button>
        </div>
      </div>
    </template>
  </div>
</template>

<style scoped>
.settings-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-card {
  padding: 24px;
  border-radius: 22px;
  border: 1px solid #e5edf8;
  background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.15), transparent 30%),
  linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.9fr);
  gap: 18px;
}

.eyebrow,
.panel-kicker {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 32px;
  font-weight: 900;
  line-height: 1.1;
}

.subtitle {
  margin: 10px 0 0;
  color: #64748b;
  line-height: 1.7;
}

.hero-preview {
  display: grid;
  gap: 14px;
  align-content: start;
}

.brand-pill {
  padding: 18px;
  border-radius: 18px;
  background: #0f172a;
  color: #fff;
}

.brand-pill strong {
  display: block;
  font-size: 22px;
  font-weight: 900;
}

.brand-pill span {
  display: block;
  margin-top: 6px;
  color: rgba(255, 255, 255, 0.78);
  line-height: 1.6;
}

.preview-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.preview-stats article {
  padding: 14px;
  border-radius: 16px;
  background: #fff;
  border: 1px solid #e8eef7;
}

.preview-stats span {
  display: block;
  margin-bottom: 6px;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.preview-stats strong {
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
}

.state-card {
  min-height: 220px;
  border: 1px solid #e5e9f1;
  border-radius: 18px;
  background: #ffffff;
  display: grid;
  place-items: center;
  gap: 12px;
  color: #64748b;
  box-shadow: 0 12px 26px rgba(15, 23, 42, 0.05);
}

.grid-layout {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 16px;
}

.settings-panel {
  padding: 20px;
  border-radius: 20px;
  background: #ffffff;
  border: 1px solid #e5eaf3;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
}

.panel-head {
  margin-bottom: 16px;
}

.panel-head h2 {
  margin: 0;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px 14px;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group--full {
  grid-column: 1 / -1;
}

.form-group--switch {
  justify-content: end;
}

.form-group label {
  margin-bottom: 6px;
  color: #0f172a;
  font-size: 14px;
  font-weight: 700;
}

.form-control {
  min-height: 42px;
  border-radius: 10px;
  border: 1px solid #dbe3ef;
  box-shadow: none;
}

textarea.form-control {
  min-height: 96px;
  resize: vertical;
}

.switch {
  min-height: 42px;
  padding: 0 14px;
  border-radius: 10px;
  border: 1px solid #dbe3ef;
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.switch input {
  width: 18px;
  height: 18px;
}

.action-bar {
  padding: 18px 0 8px;
}

.action-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  flex-wrap: wrap;
}

.primary-action,
.secondary-action {
  min-height: 44px;
  padding: 0 16px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  border: 1px solid transparent;
}

.primary-action {
  border: none;
  color: #ffffff;
  background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
}

.primary-action:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.secondary-action {
  color: #334155;
  background: #ffffff;
  border-color: #dbe3ef;
}

.status-text {
  margin: 0 0 12px;
  font-weight: 700;
}

.status-text--error {
  color: #dc2626;
}

.status-text--success {
  color: #15803d;
}

@media (max-width: 1200px) {
  .hero-card,
  .grid-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .hero-card {
    padding: 20px;
  }

  .hero-copy h1 {
    font-size: 26px;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .action-buttons {
    justify-content: stretch;
  }

  .action-buttons .primary-action,
  .action-buttons .secondary-action {
    width: 100%;
    justify-content: center;
  }
}
</style>
