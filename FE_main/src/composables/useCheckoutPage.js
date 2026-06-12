import {computed, onMounted, reactive, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/authStore'
import {useCartStore} from '@/stores/cartStore'
import {useCouponStore} from '@/stores/couponStore'
import {useOrderStore} from '@/stores/orderStore'
import {paymentService} from '@/services/paymentService'
import {shippingAddressService} from '@/services/shippingAddressService'
import {formatCurrency} from '@/utils/formatCurrency'

export function useCheckoutPage() {
    const router = useRouter()
    const route = useRoute()
    const authStore = useAuthStore()
    const cartStore = useCartStore()
    const couponStore = useCouponStore()
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

    const discountValue = computed(() => couponStore.discountAmount(subtotalValue.value))

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
                coupon_code: couponStore.appliedCode,
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

    onMounted(async () => {
        couponStore.hydrate()
        await loadInitialData()
    })

    return {
        pageLoading,
        isSubmitting,
        errorMessage,
        addressPickerOpen,
        addressModalOpen,
        addressModalSaving,
        addressModalSuccess,
        addresses,
        selectedShippingAddressId,
        shippingMethod,
        selectedPaymentMethod,
        hasSavedAddresses,
        selectedCartItemIds,
        isDirectBuy,
        directBuyItem,
        form,
        addressForm,
        shippingMethods,
        rawCartItems,
        checkoutCartItems,
        checkoutCartItemIds,
        selectedShippingAddress,
        isUsingSavedAddress,
        selectedAddressLine,
        selectedAddressPickerTitle,
        shippingAddressText,
        selectedShippingMethod,
        summaryItems,
        subtotalValue,
        shippingFeeValue,
        discountValue,
        totalValue,
        submitButtonLabel,
        openAddressPicker,
        closeAddressPicker,
        chooseSavedAddress,
        openNewAddressModal,
        openNewAddressModalFromPicker,
        closeAddressModal,
        saveNewAddressFromModal,
        handleSubmitOrder,
    }
}
