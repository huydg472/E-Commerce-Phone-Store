import {computed, onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useOrderStore} from '@/stores/orderStore'
import {useCartStore} from '@/stores/cartStore'
import {paymentService} from '@/services/paymentService'
import {formatCurrency} from '@/utils/formatCurrency'
import {formatDate} from '@/utils/formatDate'
import {useClientPagination} from '@/composables/useClientPagination.js'

export function useOrderHistoryPage() {
    const router = useRouter()
    const orderStore = useOrderStore()
    const cartStore = useCartStore()
    const {items: orderItems, loading: orderLoading} = storeToRefs(orderStore)

    const searchKeyword = ref('')
    const selectedStatus = ref('all')
    const pageLoading = ref(true)
    const errorMessage = ref('')
    const detailModalOpen = ref(false)
    const detailLoading = ref(false)
    const detailError = ref('')
    const retryLoading = ref(false)
    const retryError = ref('')

    const statusMap = {
        pending: {label: 'Chờ xác nhận', className: 'pending'},
        confirmed: {label: 'Đã xác nhận', className: 'confirmed'},
        shipping: {label: 'Đang giao', className: 'shipping'},
        completed: {label: 'Hoàn thành', className: 'completed'},
        cancelled: {label: 'Đã hủy', className: 'cancelled'},
    }

    const toNumber = (value) => {
        const numericValue = Number(value)
        return Number.isFinite(numericValue) ? numericValue : 0
    }

    const unwrapOrderItems = (order) => {
        const sources = [
            order?.orderItems,
            order?.order_items,
            order?.items,
            order?.order?.orderItems,
            order?.order?.order_items,
        ]

        for (const source of sources) {
            if (Array.isArray(source) && source.length > 0) {
                return source
            }
        }

        return []
    }

    const getItemName = (item) =>
        item?.product_name ||
        item?.productName ||
        item?.name ||
        item?.productVariant?.product?.name ||
        item?.product_variant?.product?.name ||
        item?.product?.name ||
        item?.variant?.product?.name ||
        'Sản phẩm'

    const getItemVariantName = (item) =>
        item?.variant_name ||
        item?.variantName ||
        item?.productVariant?.name ||
        item?.product_variant?.name ||
        item?.variant?.name ||
        ''

    const getItemQuantity = (item) => {
        const quantity = toNumber(item?.quantity ?? item?.qty ?? item?.count ?? 0)
        return quantity > 0 ? quantity : 1
    }

    const getVariantFirstImage = (variant) => {
        const firstImage =
            variant?.productVariantImages?.[0] ??
            variant?.product_variant_images?.[0] ??
            variant?.images?.[0] ??
            null

        return (
            firstImage?.image_url ||
            firstImage?.imageUrl ||
            firstImage?.url ||
            firstImage?.image ||
            firstImage?.image_path ||
            ''
        )
    }

    const getItemImage = (item) => {
        const variant = item?.productVariant ?? item?.product_variant ?? item?.variant ?? null
        const product = variant?.product ?? item?.product ?? null

        return (
            getVariantFirstImage(variant) ||
            variant?.thumbnail_url ||
            variant?.thumbnailUrl ||
            variant?.image ||
            product?.thumbnail_url ||
            product?.thumbnailUrl ||
            product?.image ||
            product?.image_url ||
            product?.imageUrl ||
            '/images/default-product.png'
        )
    }

    const getPaymentMethod = (order) =>
        String(order?.payment?.payment_method || order?.payment_method || 'cod').toLowerCase()

    const getPaymentStatus = (order) =>
        String(order?.payment?.payment_status || order?.payment_status || 'unpaid').toLowerCase()

    const selectedOrder = computed(() => orderStore.item ?? null)
    const selectedOrderItems = computed(() => {
        const source = selectedOrder.value?.orderItems ?? selectedOrder.value?.order_items ?? []
        return Array.isArray(source) ? source : []
    })

    const selectedOrderPayment = computed(() => selectedOrder.value?.payment ?? null)
    const selectedPaymentMethod = computed(() =>
        String(selectedOrderPayment.value?.payment_method || selectedOrder.value?.payment_method || '').toLowerCase(),
    )
    const selectedPaymentStatus = computed(() =>
        String(selectedOrderPayment.value?.payment_status || selectedOrder.value?.payment_status || 'unpaid').toLowerCase(),
    )
    const canRetryVnpayPayment = computed(() =>
        selectedPaymentMethod.value === 'vnpay' && selectedPaymentStatus.value !== 'paid',
    )
    const pendingPaymentMethods = new Set(['vnpay', 'momo'])

    const displayOrders = computed(() => {
        const source = Array.isArray(orderItems.value) ? orderItems.value : []

        return source.map((order) => {
            const items = unwrapOrderItems(order)
            const firstItem = items[0] || null
            const image = getItemImage(firstItem)
            const totalQuantity =
                items.length > 0 ? items.reduce((sum, item) => sum + getItemQuantity(item), 0) : getItemQuantity(firstItem)
            const previewProducts = items.length > 0
                ? items.slice(0, 2).map((item) => ({
                    name: getItemName(item),
                    image: getItemImage(item),
                }))
                : [{
                    name: getItemName(firstItem),
                    image,
                }]

            return {
                id: order.id,
                code: order.order_code || `#${order.id}`,
                orderDate: formatDate(order.ordered_at || order.created_at),
                status: order.order_status || 'pending',
                paymentMethod: getPaymentMethod(order),
                paymentStatus: getPaymentStatus(order),
                paymentId: order.payment?.id || null,
                total: toNumber(order.total_amount),
                address: order.shipping_address_text || '',
                orderItems: items,
                product: {
                    name: getItemName(firstItem),
                    color: getItemVariantName(firstItem),
                    quantity: totalQuantity,
                    extraCount: items.length > 1 ? items.length - 1 : 0,
                    image,
                    previewProducts,
                },
            }
        })
    })

    const filteredOrders = computed(() => {
        const keyword = searchKeyword.value.trim().toLowerCase()

        return displayOrders.value.filter((order) => {
            const matchesStatus = selectedStatus.value === 'all' || order.status === selectedStatus.value
            const matchesKeyword =
                !keyword ||
                order.code.toLowerCase().includes(keyword) ||
                order.product.name.toLowerCase().includes(keyword) ||
                order.address.toLowerCase().includes(keyword)

            return matchesStatus && matchesKeyword
        })
    })

    const {
        currentPage,
        pageSize,
        totalPages,
        paginatedItems: paginatedOrders,
        pageStart,
        pageEnd,
    } = useClientPagination(filteredOrders, {
        defaultPageSize: 5,
        pageSizeOptions: [5, 10],
    })

    const orderSummary = computed(() => [
        {
            label: 'Tổng đơn hàng',
            value: displayOrders.value.length,
            icon: 'bi bi-bag',
        },
        {
            label: 'Đang giao',
            value: displayOrders.value.filter((order) => order.status === 'shipping').length,
            icon: 'bi bi-truck',
        },
        {
            label: 'Hoàn thành',
            value: displayOrders.value.filter((order) => order.status === 'completed').length,
            icon: 'bi bi-check-circle',
        },
        {
            label: 'Đã hủy',
            value: displayOrders.value.filter((order) => order.status === 'cancelled').length,
            icon: 'bi bi-x-circle',
        },
    ])

    const orderTabs = [
        {key: 'all', label: 'Tất cả'},
        {key: 'pending', label: 'Chờ xác nhận'},
        {key: 'shipping', label: 'Đang giao'},
        {key: 'completed', label: 'Hoàn thành'},
        {key: 'cancelled', label: 'Đã hủy'},
    ]

    const loadOrders = async () => {
        pageLoading.value = true
        errorMessage.value = ''

        try {
            await orderStore.fetchAll()
        } catch (error) {
            if (error.response?.status === 401) {
                await router.replace({name: 'login'})
                return
            }

            errorMessage.value = error.response?.data?.message || 'Không tải được danh sách đơn hàng.'
        } finally {
            pageLoading.value = false
        }
    }

    const handleViewDetail = (order) => {
        detailModalOpen.value = true
        detailLoading.value = true
        detailError.value = ''
        retryError.value = ''
        retryLoading.value = false

        orderStore.fetchById(order.id)
            .catch((error) => {
                detailError.value = error?.response?.data?.message || 'Không tải được chi tiết đơn hàng.'
            })
            .finally(() => {
                detailLoading.value = false
            })
    }

    const closeDetailModal = () => {
        detailModalOpen.value = false
        detailError.value = ''
        retryError.value = ''
        retryLoading.value = false
    }

    const handleRetryVnpayPayment = async () => {
        if (!selectedOrder.value?.payment?.id || !canRetryVnpayPayment.value) {
            return
        }

        retryLoading.value = true
        retryError.value = ''

        try {
            const response = await paymentService.createVnpayUrl(selectedOrder.value.payment.id)
            const paymentUrl = response.data?.data?.payment_url

            if (!paymentUrl) {
                throw new Error('Không tạo được link VNPay.')
            }

            window.location.href = paymentUrl
        } catch (error) {
            retryError.value = error?.response?.data?.message || error?.message || 'Không thể tạo lại link VNPay.'
        } finally {
            retryLoading.value = false
        }
    }

    const handleOrderPrimaryAction = async (order) => {
        const paymentMethod = String(order.paymentMethod || '').toLowerCase()
        const paymentStatus = String(order.paymentStatus || 'unpaid').toLowerCase()

        if (order.status !== 'pending' || paymentStatus === 'paid') {
            return
        }

        if (paymentMethod === 'cod') {
            const confirmed = window.confirm('Bạn có chắc muốn hủy đơn hàng này không?')

            if (!confirmed) {
                return
            }

            try {
                await orderStore.cancel(order.id)
                await orderStore.fetchAll().catch(() => {
                })

                if (selectedOrder.value?.id === order.id) {
                    await orderStore.fetchById(order.id).catch(() => {
                    })
                }
            } catch (error) {
                errorMessage.value = error?.response?.data?.message || 'Không thể hủy đơn hàng.'
            }

            return
        }

        if (paymentMethod === 'momo') {
            await router.push({
                name: 'payment.demo',
                query: {
                    gateway: 'momo',
                    order_id: order.id,
                    amount: String(order.total ?? 0),
                },
            })
            return
        }

        if (paymentMethod === 'vnpay') {
            if (!order.paymentId) {
                errorMessage.value = 'Không tìm thấy bản ghi thanh toán.'
                return
            }

            retryLoading.value = true
            retryError.value = ''

            try {
                const response = await paymentService.createVnpayUrl(order.paymentId)
                const paymentUrl = response.data?.data?.payment_url

                if (!paymentUrl) {
                    throw new Error('Không tạo được link VNPay.')
                }

                window.location.href = paymentUrl
            } catch (error) {
                errorMessage.value = error?.response?.data?.message || error?.message || 'Không thể tạo lại link VNPay.'
            } finally {
                retryLoading.value = false
            }
        }
    }

    const handleReorder = async (order) => {
        const items = Array.isArray(order?.orderItems) ? order.orderItems : []

        if (!items.length) {
            errorMessage.value = 'Không tìm thấy sản phẩm của đơn hàng này.'
            return
        }

        try {
            errorMessage.value = ''

            for (const item of items) {
                const productVariantId = Number(item?.product_variant_id ?? item?.productVariant?.id)
                const quantity = Math.max(Number(item?.quantity) || 1, 1)

                if (!Number.isInteger(productVariantId) || productVariantId <= 0) {
                    continue
                }

                await cartStore.create({
                    product_variant_id: productVariantId,
                    quantity,
                    unit_price: Number(item?.unit_price ?? item?.price ?? 0),
                    price: Number(item?.unit_price ?? item?.price ?? 0),
                    productVariant: item?.productVariant ?? item?.product_variant ?? null,
                })
            }

            await cartStore.fetchAll().catch(() => {
            })
            await router.push({name: 'cart'})
        } catch (error) {
            errorMessage.value = error?.response?.data?.message || 'Không thể thêm lại sản phẩm vào giỏ hàng.'
        }
    }

    onMounted(loadOrders)

    return {
        orderLoading,
        searchKeyword,
        selectedStatus,
        pageLoading,
        errorMessage,
        detailModalOpen,
        detailLoading,
        detailError,
        retryLoading,
        retryError,
        statusMap,
        selectedOrder,
        selectedOrderItems,
        selectedOrderPayment,
        selectedPaymentMethod,
        selectedPaymentStatus,
        canRetryVnpayPayment,
        pendingPaymentMethods,
        displayOrders,
        filteredOrders,
        currentPage,
        pageSize,
        totalPages,
        paginatedOrders,
        pageStart,
        pageEnd,
        orderSummary,
        orderTabs,
        loadOrders,
        handleViewDetail,
        closeDetailModal,
        handleRetryVnpayPayment,
        handleOrderPrimaryAction,
        handleReorder,
        getItemImage,
        formatCurrency,
        formatDate,
    }
}
