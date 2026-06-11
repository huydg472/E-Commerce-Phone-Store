<script setup>
import {computed, onMounted, reactive, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import PaymentMethod from '@/components/payment/PaymentMethod.vue'
import OrderSummary from '@/components/order/OrderSummary.vue'
import {useAuthStore} from '@/stores/authStore'
import {useCartStore} from '@/stores/cartStore'
import {useOrderStore} from '@/stores/orderStore'
import {paymentService} from '@/services/paymentService'
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
const addressPickerOpen = ref(false)
const addressModalOpen = ref(false)
const addressModalSaving = ref(false)
const addressModalSuccess = ref('')
const addresses = ref([])
const selectedShippingAddressId = ref('')
const shippingMethod = ref('standard')
const selectedPaymentMethod = ref('cod')
const hasSavedAddresses = computed(() => addresses.value.length > 0)
const selectedCartItemIds = computed(() => {
  const rawValue = route.query.item_ids
  const values = Array.isArray(rawValue) ? rawValue.join(',') : String(rawValue ?? '')

  return values
      .split(',')
      .map((value) => Number(value))
      .filter((value) => Number.isInteger(value) && value > 0)
})

const isDirectBuy = computed(() => String(route.query.direct_buy ?? '') === '1')

const directBuyItem = computed(() => {
  if (!isDirectBuy.value) {
    return null
  }

  const productVariantId = Number(route.query.product_variant_id)
  const quantity = Math.max(Number(route.query.quantity ?? 1) || 1, 1)
  const unitPrice = Math.max(Number(route.query.unit_price ?? 0) || 0, 0)
  const productName = String(route.query.product_name ?? 'Sản phẩm').trim() || 'Sản phẩm'
  const variantName = String(route.query.variant_name ?? 'Phiên bản mặc định').trim() || 'Phiên bản mặc định'
  const sku = String(route.query.sku ?? variantName).trim() || variantName
  const image = String(route.query.image ?? '').trim() || '/images/default-product.png'

  if (!Number.isInteger(productVariantId) || productVariantId <= 0) {
    return null
  }

  return {
    id: `buy-now-${productVariantId}`,
    product_variant_id: productVariantId,
    quantity,
    price: unitPrice,
    productVariant: {
      id: productVariantId,
      sku,
      quantity,
      sale_price: unitPrice,
      price: unitPrice,
      product: {
        name: productName,
        thumbnail_url: image,
        thumbnailUrl: image,
        image_url: image,
        imageUrl: image,
      },
    },
  }
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

const addressForm = reactive({
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
  if (directBuyItem.value) {
    return [directBuyItem.value]
  }

  if (!selectedCartItemIds.value.length) {
    return rawCartItems.value
  }

  const selectedIds = new Set(selectedCartItemIds.value)
  return rawCartItems.value.filter((item) => selectedIds.has(Number(item?.id)))
})

const checkoutCartItemIds = computed(() =>
    checkoutCartItems.value
        .map((item) => Number(item?.id))
        .filter((value) => Number.isInteger(value) && value > 0)
)

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

const isUsingSavedAddress = computed(() => Boolean(selectedShippingAddress.value))

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

const selectedAddressLine = computed(() => formatAddressLine(selectedShippingAddress.value))

const selectedAddressPickerTitle = computed(() => (
  hasSavedAddresses.value ? 'Chọn địa chỉ khác' : 'Sổ địa chỉ đang trống'
))

const normalizeAddress = (address) => ({
  id: address?.id,
  receiver_name: address?.receiver_name || '',
  receiver_phone: address?.receiver_phone || '',
  province: address?.province || '',
  district: address?.district || '',
  ward: address?.ward || '',
  address_detail: address?.address_detail || '',
  note: address?.note || '',
  is_default: Boolean(address?.is_default),
})

const unwrapAddress = (response) => {
  return response?.data?.data ?? response?.data ?? null
}

const shippingAddressText = computed(() => {
  const selectedAddress = selectedShippingAddress.value

  if (selectedAddress) {
    return selectedAddressLine.value
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

const closeAddressModal = () => {
  addressModalOpen.value = false
  addressModalSaving.value = false
  addressModalSuccess.value = ''
}

const closeAddressPicker = () => {
  addressPickerOpen.value = false
}

const openAddressPicker = () => {
  addressPickerOpen.value = true
}

const chooseSavedAddress = (address) => {
  if (!address?.id) {
    return
  }

  selectedShippingAddressId.value = String(address.id)
  syncAddressForm(address)
  closeAddressPicker()
}

const resetAddressForm = () => {
  addressForm.receiver_name = authStore.user?.name || ''
  addressForm.receiver_phone = authStore.user?.phone || ''
  addressForm.province = ''
  addressForm.district = ''
  addressForm.ward = ''
  addressForm.address_detail = ''
  addressForm.note = ''
}

const openNewAddressModal = () => {
  resetAddressForm()
  addressModalSuccess.value = ''
  addressModalOpen.value = true
}

const openNewAddressModalFromPicker = () => {
  closeAddressPicker()
  openNewAddressModal()
}

const saveNewAddressFromModal = async () => {
  if (!addressForm.receiver_name || !addressForm.receiver_phone || !addressForm.province || !addressForm.district || !addressForm.ward || !addressForm.address_detail) {
    errorMessage.value = 'Vui lòng nhập đầy đủ thông tin địa chỉ.'
    return
  }

  addressModalSaving.value = true
  addressModalSuccess.value = ''
  errorMessage.value = ''

  try {
    const createdAddressResponse = await shippingAddressService.create({
      receiver_name: addressForm.receiver_name,
      receiver_phone: addressForm.receiver_phone,
      province: addressForm.province,
      district: addressForm.district,
      ward: addressForm.ward,
      address_detail: addressForm.address_detail,
      note: addressForm.note,
      is_default: !hasSavedAddresses.value,
    })

    const createdAddress = normalizeAddress(unwrapAddress(createdAddressResponse))

    if (!createdAddress.id) {
      throw new Error('Không lưu được địa chỉ mới.')
    }

    addresses.value = [createdAddress, ...addresses.value.filter((item) => item.id !== createdAddress.id)]
    selectedShippingAddressId.value = String(createdAddress.id)
    syncAddressForm(createdAddress)

    addressModalSuccess.value = 'Đã thêm địa chỉ mới và chọn cho đơn hàng.'

    window.setTimeout(() => {
      closeAddressModal()
    }, 1000)
  } catch (error) {
    errorMessage.value = error?.response?.data?.message || error?.message || 'Không thể lưu địa chỉ.'
  } finally {
    addressModalSaving.value = false
  }
}

const submitButtonLabel = computed(() => {
  switch (selectedPaymentMethod.value) {
    case 'vnpay':
      return 'Thanh toán VNPay'
    case 'momo':
      return 'Thanh toán MoMo'
    default:
      return 'Đặt hàng'
  }
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
      isDirectBuy.value ? Promise.resolve() : cartStore.fetchAll(),
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

    if ((isDirectBuy.value || selectedCartItemIds.value.length) && !checkoutCartItems.value.length) {
      errorMessage.value = isDirectBuy.value
        ? 'Không tìm thấy sản phẩm để thanh toán.'
        : 'Không tìm thấy sản phẩm đã chọn trong giỏ hàng.'
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

  if (!selectedShippingAddress.value) {
    errorMessage.value = 'Vui lòng chọn một địa chỉ đã lưu hoặc thêm địa chỉ mới.'
    return
  }

  if (!shippingAddressText.value) {
    errorMessage.value = 'Địa chỉ giao hàng không hợp lệ.'
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

    if (selectedPaymentMethod.value === 'vnpay') {
      const paymentId = createdOrder?.payment?.id

      if (!paymentId) {
        throw new Error('Không tìm thấy bản ghi thanh toán.')
      }

      const paymentResponse = await paymentService.createVnpayUrl(paymentId)
      const paymentUrl = paymentResponse.data?.data?.payment_url

      if (!paymentUrl) {
        throw new Error('Không tạo được link VNPay.')
      }

      localStorage.setItem(
          'pending_payment_cart_item_ids',
          JSON.stringify(checkoutCartItemIds.value),
      )

      window.location.href = paymentUrl
      return
    }

    if (selectedPaymentMethod.value === 'cod' && checkoutCartItemIds.value.length) {
      await Promise.allSettled(checkoutCartItems.value.map((item) => cartStore.remove(item.id)))
      await cartStore.fetchAll().catch(() => {
      })
    }

    const nextRoute = selectedPaymentMethod.value === 'momo'
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

            <div v-if="selectedShippingAddress" class="selected-address-card">
              <div class="selected-address-card__main">
                <div class="selected-address-card__icon">
                  <i class="bi bi-geo-alt-fill"></i>
                </div>
                <div class="selected-address-card__content">
                  <div class="selected-address-card__header">
                    <strong>{{ selectedShippingAddress.receiver_name }}</strong>
                  </div>
                  <p>{{ selectedAddressLine || 'Chưa có địa chỉ chi tiết' }}</p>
                  <small>
                    <i class="bi bi-telephone"></i>
                    {{ selectedShippingAddress.receiver_phone || 'Chưa có số điện thoại' }}
                  </small>
                </div>
              </div>
              <button type="button" class="link-btn" @click="openAddressPicker">
                Chọn địa chỉ khác
              </button>
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
              :button-label="submitButtonLabel"
              @submit-order="handleSubmitOrder"
          />
        </div>
      </div>

      <p v-if="errorMessage" class="checkout-error">
        {{ errorMessage }}
      </p>
    </div>

    <teleport to="body">
      <div v-if="addressPickerOpen" class="address-modal-overlay" @click.self="closeAddressPicker">
        <div class="address-modal">
          <div class="address-modal__header">
            <div>
              <h3>{{ selectedAddressPickerTitle }}</h3>
              <p v-if="hasSavedAddresses">
                Chọn một địa chỉ khác đang có trong Sổ địa chỉ để dùng cho đơn này.
              </p>
              <p v-else>
                Bạn chưa có địa chỉ nào trong Sổ địa chỉ.
              </p>
            </div>
            <button type="button" class="address-modal__close" @click="closeAddressPicker">
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
                @click="chooseSavedAddress(address)"
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
            <button type="button" class="btn btn-primary" @click="openNewAddressModalFromPicker">
              Thêm địa chỉ mới
            </button>
          </div>

          <div v-if="hasSavedAddresses" class="address-modal__footer">
            <button type="button" class="btn btn-outline-secondary" @click="openNewAddressModalFromPicker">
              Thêm địa chỉ mới
            </button>
          </div>
        </div>
      </div>

      <div v-if="addressModalOpen" class="address-modal-overlay" @click.self="closeAddressModal">
        <div class="address-modal">
          <div class="address-modal__header">
            <div>
              <h3>{{ hasSavedAddresses ? 'Thêm địa chỉ mới' : 'Thêm địa chỉ giao hàng' }}</h3>
              <p>
                {{ hasSavedAddresses
                  ? 'Lưu địa chỉ mới vào Sổ địa chỉ, sau đó chọn ngay cho đơn hàng.'
                  : 'Bạn chưa có địa chỉ nào. Hãy nhập địa chỉ để lưu và dùng cho đơn hàng này.' }}
              </p>
            </div>
            <button type="button" class="address-modal__close" @click="closeAddressModal">
              <i class="bi bi-x-lg"></i>
            </button>
          </div>

          <div v-if="addressModalSuccess" class="address-modal__success">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ addressModalSuccess }}</span>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label>Họ và tên <span>*</span></label>
              <input v-model.trim="addressForm.receiver_name" type="text" class="form-control" placeholder="Nhập họ và tên">
            </div>

            <div class="form-group">
              <label>Số điện thoại <span>*</span></label>
              <input v-model.trim="addressForm.receiver_phone" type="text" class="form-control" placeholder="Nhập số điện thoại">
            </div>

            <div class="form-group">
              <label>Tỉnh/Thành phố <span>*</span></label>
              <input v-model.trim="addressForm.province" type="text" class="form-control" placeholder="Nhập tỉnh/thành phố">
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
              <input v-model.trim="addressForm.address_detail" type="text" class="form-control" placeholder="Số nhà, tên đường, tòa nhà, căn hộ...">
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
            <button type="button" class="btn btn-outline-secondary" @click="closeAddressModal">
              Hủy
            </button>
            <button type="button" class="btn btn-primary" :disabled="addressModalSaving" @click="saveNewAddressFromModal">
              {{ addressModalSaving ? 'Đang lưu...' : 'Lưu địa chỉ' }}
            </button>
          </div>
        </div>
      </div>
    </teleport>
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

.form-control--readonly {
  background: #f8fafc;
  color: #334155;
  cursor: not-allowed;
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

.selected-address-card {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 12px;
  padding: 12px 14px;
  border: 1px solid #dbeafe;
  border-radius: 12px;
  background: #f8fbff;
}

.selected-address-card__main {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  min-width: 0;
}

.selected-address-card__icon {
  width: 38px;
  height: 38px;
  flex: 0 0 38px;
  border-radius: 12px;
  background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
  color: #1d4ed8;
  display: grid;
  place-items: center;
  font-size: 18px;
}

.selected-address-card__content {
  min-width: 0;
}

.selected-address-card__header {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 4px;
}

.selected-address-card strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
}

.selected-address-card p {
  margin: 0 0 6px;
  color: #475569;
  font-size: 13px;
  line-height: 1.6;
}

.selected-address-card small {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #64748b;
  font-size: 12px;
}

.link-btn {
  border: 0;
  background: transparent;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  white-space: nowrap;
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

.saved-address-list {
  display: grid;
  gap: 10px;
}

.saved-address-item {
  width: 100%;
  padding: 14px;
  border: 1px solid #dbeafe;
  border-radius: 16px;
  background: #f8fbff;
  display: grid;
  grid-template-columns: 42px minmax(0, 1fr);
  gap: 12px;
  align-items: start;
  text-align: left;
}

.saved-address-item.active {
  border-color: #2563eb;
  box-shadow: inset 0 0 0 1px #2563eb;
}

.saved-address-item__icon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
  color: #1d4ed8;
  display: grid;
  place-items: center;
  font-size: 18px;
}

.saved-address-item__content {
  min-width: 0;
}

.saved-address-item__header {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 4px;
}

.saved-address-item__content strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 800;
}

.saved-address-item__badge {
  display: inline-flex;
  align-items: center;
  padding: 4px 8px;
  border-radius: 999px;
  background: #e0edff;
  color: #1d4ed8;
  font-size: 11px;
  font-weight: 800;
  white-space: nowrap;
}

.saved-address-item__content p {
  margin: 0 0 6px;
  color: #475569;
  font-size: 13px;
  line-height: 1.6;
}

.saved-address-item__content small {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: #64748b;
  font-size: 12px;
}

.saved-address-item__content small i {
  color: #94a3b8;
}

.address-modal__empty {
  padding: 24px 20px;
  border: 1px dashed #cbd5e1;
  border-radius: 16px;
  background: #f8fafc;
  text-align: center;
}

.address-modal__empty i {
  color: #1d4ed8;
  font-size: 28px;
}

.address-modal__empty p {
  margin: 10px 0 16px;
  color: #475569;
  line-height: 1.6;
}

.address-modal-overlay {
  position: fixed;
  inset: 0;
  z-index: 1300;
  display: grid;
  place-items: center;
  padding: 18px;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(10px);
}

.address-modal {
  width: min(100%, 760px);
  max-height: min(90vh, 900px);
  overflow: auto;
  padding: 20px;
  border-radius: 20px;
  background: #ffffff;
  border: 1px solid #e5edf8;
  box-shadow: 0 30px 80px rgba(15, 23, 42, 0.28);
}

.address-modal__header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 16px;
}

.address-modal__header h3 {
  margin: 0 0 6px;
  color: #0f172a;
  font-size: 22px;
  font-weight: 900;
}

.address-modal__header p {
  margin: 0;
  color: #64748b;
  line-height: 1.6;
}

.address-modal__close {
  width: 38px;
  height: 38px;
  border: 0;
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  display: grid;
  place-items: center;
}

.address-modal__success {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  margin-bottom: 14px;
  border-radius: 12px;
  background: #ecfdf5;
  color: #15803d;
  font-weight: 800;
}

.address-modal__success i {
  font-size: 18px;
}

.address-modal__footer {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  flex-wrap: wrap;
  margin-top: 16px;
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

  .selected-address-card {
    flex-direction: column;
  }

  .selected-address-card__main {
    width: 100%;
  }

  .link-btn {
    padding-left: 50px;
  }

  .saved-address-item {
    grid-template-columns: 1fr;
  }

  .saved-address-item__icon {
    width: 38px;
    height: 38px;
  }

  .address-modal {
    padding: 16px;
    border-radius: 16px;
  }

  .address-modal__header {
    flex-direction: column;
  }

  .address-modal__footer {
    justify-content: stretch;
  }

  .address-modal__footer .btn {
    width: 100%;
  }
}
</style>
