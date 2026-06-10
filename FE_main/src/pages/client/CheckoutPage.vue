<script setup>
import {computed, onMounted, reactive, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import PaymentMethod from '@/components/payment/PaymentMethod.vue'
import OrderSummary from '@/components/order/OrderSummary.vue'
import {useAuthStore} from '@/stores/authStore'
import {useCartStore} from '@/stores/cartStore'
import {useOrderStore} from '@/stores/orderStore'
import {shippingAddressService} from '@/services/shippingAddressService'
import {formatCurrency} from '@/utils/formatCurrency'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const cartStore = useCartStore()
const orderStore = useOrderStore()

const pageLoading = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')
const addresses = ref([])
const selectedShippingAddressId = ref('')
const shippingMethod = ref('standard')
const selectedPaymentMethod = ref('cod')
const selectedCartItemIds = computed(() => {
  const rawValue = route.query.item_ids
  const values = Array.isArray(rawValue) ? rawValue.join(',') : String(rawValue ?? '')

  return values
      .split(',')
      .map((value) => Number(value))
      .filter((value) => Number.isInteger(value) && value > 0)
})

const form = reactive({
  receiver_name: '',
  receiver_phone: '',
  province: '',
  district: '',
  ward: '',
  address_detail: '',
  note: '',
})

const shippingMethods = [
  {
    id: 'standard',
    title: 'Giao hàng tiêu chuẩn',
    fee: 0,
    feeLabel: 'Miễn phí',
    icon: 'bi-truck',
  },
  {
    id: 'express',
    title: 'Giao hàng nhanh',
    fee: 40000,
    feeLabel: '+40.000đ',
    icon: 'bi-truck-flatbed',
  },
]

const rawCartItems = computed(() => {
  const source = cartStore.item?.items ?? cartStore.items ?? []
  return Array.isArray(source) ? source : []
})

const checkoutCartItems = computed(() => {
  if (!selectedCartItemIds.value.length) {
    return rawCartItems.value
  }

  const selectedIds = new Set(selectedCartItemIds.value)
  return rawCartItems.value.filter((item) => selectedIds.has(Number(item?.id)))
})

const toNumber = (value) => {
  const numericValue = Number(value)
  return Number.isFinite(numericValue) ? numericValue : 0
}

const resolveVariant = (item) => {
  return (
      item?.productVariant ??
      item?.product_variant ??
      item?.variant ??
      item?.product_variants?.[0] ??
      null
  )
}

const resolveProduct = (item, variant) => {
  return (
      variant?.product ??
      item?.product ??
      item?.products ??
      null
  )
}

const resolveVariantName = (variant) => {
  const parts = [
    variant?.color,
    variant?.storage,
    variant?.ram,
  ].filter(Boolean)

  if (parts.length > 0) {
    return parts.join(' - ')
  }

  return variant?.sku || 'Phiên bản mặc định'
}

const resolveImage = (product, variant) => {
  const firstVariantImage =
      variant?.productVariantImages?.[0] ??
      variant?.product_variant_images?.[0] ??
      variant?.images?.[0] ??
      null

  return (
      firstVariantImage?.image_url ??
      firstVariantImage?.imageUrl ??
      firstVariantImage?.url ??
      firstVariantImage?.path ??
      firstVariantImage?.image ??
      firstVariantImage?.image_path ??
      variant?.thumbnail_url ??
      variant?.thumbnailUrl ??
      variant?.image ??
      product?.thumbnail_url ??
      product?.thumbnailUrl ??
      product?.image ??
      product?.image_url ??
      product?.imageUrl ??
      '/images/default-product.png'
  )
}

const orderItems = computed(() => {
  return checkoutCartItems.value.map((item) => {
    const variant = resolveVariant(item)
    const product = resolveProduct(item, variant)
    const quantity = Math.max(toNumber(item?.quantity ?? 1), 1)
    const unitPrice = toNumber(
        item?.price ??
        variant?.sale_price ??
        variant?.salePrice ??
        variant?.price ??
        variant?.display_price ??
        variant?.displayPrice ??
        product?.display_price ??
        product?.sale_price ??
        product?.price
    )
    const totalPrice = unitPrice * quantity

    return {
      product_variant_id: Number(variant?.id ?? item?.product_variant_id ?? 0) || null,
      product_name: product?.name ?? 'Sản phẩm',
      variant_name: resolveVariantName(variant),
      sku: variant?.sku ?? null,
      unit_price: unitPrice,
      quantity,
      total_price: totalPrice,
    }
  })
})

const summaryItems = computed(() => {
  return checkoutCartItems.value.map((item) => {
    const variant = resolveVariant(item)
    const product = resolveProduct(item, variant)
    const quantity = Math.max(toNumber(item?.quantity ?? 1), 1)
    const unitPrice = toNumber(
        item?.price ??
        variant?.sale_price ??
        variant?.salePrice ??
        variant?.price ??
        variant?.display_price ??
        variant?.displayPrice ??
        product?.display_price ??
        product?.sale_price ??
        product?.price
    )
    const totalPrice = unitPrice * quantity

    return {
      id: item?.id,
      name: product?.name ?? 'Sản phẩm',
      image: resolveImage(product, variant),
      color: variant?.color ?? variant?.color_name ?? '',
      version: resolveVariantName(variant),
      quantity,
      price: formatCurrency(unitPrice),
      total: formatCurrency(totalPrice),
    }
  })
})

const subtotalValue = computed(() => {
  return orderItems.value.reduce((sum, item) => sum + toNumber(item.total_price), 0)
})

const shippingFeeValue = computed(() => {
  const method = shippingMethods.find((item) => item.id === shippingMethod.value)
  return method?.fee ?? 0
})

const discountValue = computed(() => 0)

const totalValue = computed(() => {
  return Math.max(subtotalValue.value + shippingFeeValue.value - discountValue.value, 0)
})

const selectedShippingAddress = computed(() => {
  return addresses.value.find((address) => String(address.id) === String(selectedShippingAddressId.value)) || null
})

const shippingAddressText = computed(() => {
  const selectedAddress = selectedShippingAddress.value

  if (selectedAddress) {
    return [
      selectedAddress.address_detail,
      selectedAddress.ward,
      selectedAddress.district,
      selectedAddress.province,
    ]
        .filter(Boolean)
        .join(', ')
  }

  return [
    form.address_detail,
    form.ward,
    form.district,
    form.province,
  ]
      .filter(Boolean)
      .join(', ')
})

const selectedShippingMethod = computed(() => {
  return shippingMethods.find((item) => item.id === shippingMethod.value) ?? shippingMethods[0]
})

const syncAddressForm = (address) => {
  if (!address) {
    return
  }

  form.receiver_name = address.receiver_name || form.receiver_name || authStore.user?.name || ''
  form.receiver_phone = address.receiver_phone || form.receiver_phone || authStore.user?.phone || ''
  form.province = address.province || ''
  form.district = address.district || ''
  form.ward = address.ward || ''
  form.address_detail = address.address_detail || ''
  form.note = address.note || form.note || ''
}

const handleShippingAddressChange = () => {
  const address = selectedShippingAddress.value

  if (!address) {
    form.receiver_name = authStore.user?.name || ''
    form.receiver_phone = authStore.user?.phone || ''
    form.province = ''
    form.district = ''
    form.ward = ''
    form.address_detail = ''
    return
  }

  syncAddressForm(address)
}

const loadInitialData = async () => {
  pageLoading.value = true

  try {
    if (!authStore.user) {
      await authStore.fetchMe().catch(() => {
      })
    }

    await Promise.allSettled([
      cartStore.fetchAll(),
      shippingAddressService.getAll().then((response) => {
        addresses.value = response.data?.data ?? response.data ?? []
      }).catch(() => {
        addresses.value = []
      }),
    ])

    if (!authStore.isLoggedIn) {
      await router.replace({name: 'login'})
      return
    }

    const defaultAddress = addresses.value.find((address) => address.is_default) ?? addresses.value[0] ?? null

    if (selectedCartItemIds.value.length && !checkoutCartItems.value.length) {
      errorMessage.value = 'Không tìm thấy sản phẩm đã chọn trong giỏ hàng.'
      return
    }

    if (defaultAddress) {
      selectedShippingAddressId.value = String(defaultAddress.id)
      syncAddressForm(defaultAddress)
    } else {
      form.receiver_name = authStore.user?.name || ''
      form.receiver_phone = authStore.user?.phone || ''
    }
  } finally {
    pageLoading.value = false
  }
}

const handleSubmitOrder = async () => {
  errorMessage.value = ''

  if (!orderItems.value.length) {
    errorMessage.value = 'Giỏ hàng đang trống.'
    return
  }

  if (!form.receiver_name || !form.receiver_phone || !shippingAddressText.value) {
    errorMessage.value = 'Vui lòng nhập đầy đủ thông tin nhận hàng.'
    return
  }

  try {
    isSubmitting.value = true

    const response = await orderStore.create({
      shipping_address_id: selectedShippingAddress.value?.id ?? null,
      receiver_name: form.receiver_name,
      receiver_phone: form.receiver_phone,
      shipping_address_text: shippingAddressText.value,
      shipping_fee: shippingFeeValue.value,
      discount_amount: discountValue.value,
      payment_method: selectedPaymentMethod.value,
      note: form.note,
      items: orderItems.value,
    })

    const createdOrder = response.data?.data ?? response.data ?? null

    await Promise.allSettled(checkoutCartItems.value.map((item) => cartStore.remove(item.id)))
    await cartStore.fetchAll().catch(() => {
    })

    const nextRoute = ['vnpay', 'momo'].includes(selectedPaymentMethod.value)
      ? {
          name: 'payment.demo',
          query: {
            gateway: selectedPaymentMethod.value,
            order_id: createdOrder?.id ?? '',
            amount: String(totalValue.value),
          },
        }
      : {
          name: 'order.success',
          query: {
            order_id: createdOrder?.id ?? '',
          },
        }

    await router.push(nextRoute)
  } catch (error) {
    if (error.response?.status === 422) {
      const errors = error.response.data?.errors
      if (errors) {
        const firstKey = Object.keys(errors)[0]
        errorMessage.value = errors[firstKey]?.[0] || 'Dữ liệu không hợp lệ.'
        return
      }
    }

    errorMessage.value = error.response?.data?.message || 'Tạo đơn hàng thất bại. Vui lòng thử lại.'
  } finally {
    isSubmitting.value = false
  }
}

watch(selectedShippingAddressId, handleShippingAddressChange)
watch(
    () => authStore.user,
    (user) => {
      if (!selectedShippingAddress.value) {
        form.receiver_name = user?.name || form.receiver_name
        form.receiver_phone = user?.phone || form.receiver_phone
      }
    }
)

onMounted(loadInitialData)
</script>

<template>
  <section class="checkout-page">
    <div class="checkout-container">
      <div class="breadcrumb-area">
        <RouterLink to="/">Trang chủ</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <RouterLink to="/cart">Giỏ hàng</RouterLink>
        <i class="bi bi-chevron-right"></i>
        <span>Thanh toán</span>
      </div>

      <h1 class="page-title">Thanh toán</h1>

      <div v-if="pageLoading" class="checkout-loading">
        <div class="checkout-loading-card">
          <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
          <p>Đang tải dữ liệu thanh toán...</p>
        </div>
      </div>

      <div v-else class="checkout-layout">
        <div class="checkout-left">
          <div class="checkout-card">
            <div class="section-title">
              <i class="bi bi-geo-alt"></i>
              <h2>Địa chỉ giao hàng</h2>
            </div>

            <div class="form-group form-group-full">
              <label>Chọn địa chỉ đã lưu</label>
              <select v-model="selectedShippingAddressId" class="form-select">
                <option value="">Nhập địa chỉ mới</option>
                <option
                    v-for="address in addresses"
                    :key="address.id"
                    :value="String(address.id)"
                >
                  {{ address.receiver_name }} - {{ address.address_detail }}
                </option>
              </select>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label>Họ và tên <span>*</span></label>
                <input v-model.trim="form.receiver_name" type="text" class="form-control" placeholder="Nhập họ và tên"/>
              </div>

              <div class="form-group">
                <label>Số điện thoại <span>*</span></label>
                <input v-model.trim="form.receiver_phone" type="text" class="form-control"
                       placeholder="Nhập số điện thoại"/>
              </div>

              <div class="form-group">
                <label>Tỉnh/Thành phố <span>*</span></label>
                <input v-model.trim="form.province" type="text" class="form-control" placeholder="Nhập tỉnh/thành phố"/>
              </div>

              <div class="form-group">
                <label>Quận/Huyện <span>*</span></label>
                <input v-model.trim="form.district" type="text" class="form-control" placeholder="Nhập quận/huyện"/>
              </div>

              <div class="form-group">
                <label>Phường/Xã <span>*</span></label>
                <input v-model.trim="form.ward" type="text" class="form-control" placeholder="Nhập phường/xã"/>
              </div>

              <div class="form-group">
                <label>Địa chỉ cụ thể <span>*</span></label>
                <input v-model.trim="form.address_detail" type="text" class="form-control"
                       placeholder="Số nhà, tên đường, tòa nhà, căn hộ..."/>
              </div>
            </div>

            <div class="form-group form-group-full note-group">
              <label>Ghi chú đơn hàng</label>
              <textarea
                  v-model.trim="form.note"
                  class="form-control"
                  rows="3"
                  placeholder="Ví dụ: Giao hàng giờ hành chính, gọi trước khi giao..."
              ></textarea>
            </div>
          </div>

          <div class="checkout-card">
            <div class="section-title">
              <i class="bi bi-truck"></i>
              <h2>Phương thức giao hàng</h2>
            </div>

            <div class="shipping-methods">
              <label
                  v-for="method in shippingMethods"
                  :key="method.id"
                  class="shipping-card"
                  :class="{ active: shippingMethod === method.id }"
              >
                <input
                    v-model="shippingMethod"
                    class="form-check-input"
                    type="radio"
                    name="shipping_method"
                    :value="method.id"
                />

                <div class="shipping-icon">
                  <i :class="`bi ${method.icon}`"></i>
                </div>

                <div class="shipping-content">
                  <h3>{{ method.title }}</h3>
                  <p>{{ method.feeLabel }}</p>
                </div>
              </label>
            </div>
          </div>

          <PaymentMethod v-model="selectedPaymentMethod"/>
        </div>

        <div class="checkout-right">
          <OrderSummary
              :items="summaryItems"
              :item-count="summaryItems.length"
              :subtotal="subtotalValue"
              :discount="discountValue"
              :shipping="shippingFeeValue"
              :total="totalValue"
              :loading="isSubmitting"
              button-label="Đặt hàng"
              @submit-order="handleSubmitOrder"
          />
        </div>
      </div>

      <p v-if="errorMessage" class="checkout-error">
        {{ errorMessage }}
      </p>
    </div>
  </section>
</template>

<style scoped>
.checkout-page {
  padding: 18px 0 40px;
  background: #ffffff;
}

.checkout-container {
  width: min(100% - 36px, 1560px);
  margin: 0 auto;
}

.breadcrumb-area {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 10px;
  color: #64748b;
  font-size: 14px;
  font-weight: 600;
}

.breadcrumb-area a {
  color: #64748b;
  text-decoration: none;
}

.breadcrumb-area a:hover,
.breadcrumb-area span {
  color: #0d6efd;
}

.breadcrumb-area i {
  font-size: 11px;
}

.page-title {
  margin: 0 0 14px;
  color: #0f172a;
  font-size: 34px;
  font-weight: 900;
}

.checkout-loading {
  min-height: 280px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.checkout-loading-card {
  min-width: 240px;
  padding: 28px 24px;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  background: #ffffff;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
}

.checkout-loading-card p {
  margin: 0;
  color: #475569;
  font-size: 14px;
  font-weight: 700;
}

.checkout-layout {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 500px;
  gap: 24px;
  align-items: start;
}

.checkout-left,
.checkout-right {
  min-width: 0;
}

.checkout-left {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.checkout-card {
  padding: 18px 20px 16px;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  background: #ffffff;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 16px;
}

.section-title i {
  color: #0d6efd;
  font-size: 20px;
}

.section-title h2 {
  margin: 0;
  color: #0f172a;
  font-size: 18px;
  font-weight: 800;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px 16px;
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

.form-group .form-control,
.form-group .form-select {
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
  min-height: 56px;
  resize: none;
  padding-top: 10px;
}

.form-group-full {
  margin-top: 12px;
}

.note-group {
  margin-top: 12px;
}

.shipping-methods {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.shipping-card {
  padding: 12px 14px;
  border: 1px solid #dbe3ef;
  border-radius: 10px;
  background: #ffffff;
  display: grid;
  grid-template-columns: 18px 34px 1fr;
  gap: 12px;
  align-items: center;
  cursor: pointer;
}

.shipping-card.active {
  border-color: #0d6efd;
  box-shadow: inset 0 0 0 1px #0d6efd;
}

.shipping-card .form-check-input {
  box-shadow: none;
}

.shipping-card .form-check-input:checked {
  background-color: #0d6efd;
  border-color: #0d6efd;
}

.shipping-icon {
  width: 34px;
  height: 34px;
  color: #334155;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
}

.shipping-content h3 {
  margin: 0 0 2px;
  color: #0f172a;
  font-size: 15px;
  font-weight: 800;
}

.shipping-content p {
  margin: 0;
  color: #0d6efd;
  font-size: 14px;
  font-weight: 800;
}

.checkout-error {
  margin: 14px 0 0;
  color: #dc2626;
  font-weight: 600;
}

@media (max-width: 1200px) {
  .checkout-layout {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .checkout-container {
    width: min(100% - 24px, 1560px);
  }

  .page-title {
    font-size: 28px;
  }

  .form-grid,
  .shipping-methods {
    grid-template-columns: 1fr;
  }

  .checkout-card {
    padding: 16px;
  }
}
</style>
