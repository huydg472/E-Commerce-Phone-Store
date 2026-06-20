import {computed, onBeforeUnmount, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {orderService} from '@/services/orderService'

export function usePaymentGatewayDemo() {
    const route = useRoute()
    const router = useRouter()

    const gateways = {
        vnpay: {
            key: 'vnpay',
            name: 'VNPay',
            accent: 'blue',
            description: 'Thanh toán qua QR VNPay, giao dịch thành công thì đơn vẫn chờ cửa hàng xác nhận.',
            badge: 'VNPay QR',
        },
        momo: {
            key: 'momo',
            name: 'MoMo',
            accent: 'pink',
            description: 'Thanh toán bằng ví MoMo, giao dịch chỉ ghi nhận đã trả tiền và chờ shop duyệt.',
            badge: 'MoMo QR',
        },
    }

    const selectedGateway = ref('vnpay')
    const paymentState = ref('idle')
    const countdown = ref(10)
    const transactionCode = ref('MOCK-000000')
    const orderId = ref('')
    const amount = ref(0)
    const syncMessage = ref('')
    const syncError = ref('')
    let countdownTimer = null
    let successTimer = null

    const gatewayMeta = computed(() => gateways[selectedGateway.value] ?? gateways.vnpay)
    const orderLabel = computed(() => (orderId.value ? `#${orderId.value}` : 'DEMO-ORDER'))
    const formattedAmount = computed(() =>
        new Intl.NumberFormat('vi-VN', {maximumFractionDigits: 0}).format(Number(amount.value || 0)),
    )
    const progressPercent = computed(() => Math.max(0, Math.min(100, ((10 - countdown.value) / 10) * 100)))

    const statusLabel = computed(() => {
        switch (paymentState.value) {
            case 'pending':
                return `Đang chờ quét mã ${countdown.value}s`
            case 'syncing':
                return 'Đang xác nhận thanh toán'
            case 'paid':
                return 'Đã thanh toán, chờ xác nhận'
            case 'cancelled':
                return 'Giao dịch đã hủy'
            case 'error':
                return 'Xác nhận thất bại'
            default:
                return 'Chưa bắt đầu'
        }
    })

    const paymentSteps = computed(() => [
        {
            title: 'Quét mã QR',
            description: 'Dùng ứng dụng ngân hàng hoặc ví điện tử để quét mã trên màn hình.',
            active: paymentState.value === 'pending',
            done: ['syncing', 'paid'].includes(paymentState.value),
        },
        {
            title: 'Hệ thống đối soát',
            description: 'Giao dịch được ghi nhận và đẩy vào luồng chờ cửa hàng xác nhận.',
            active: paymentState.value === 'syncing',
            done: paymentState.value === 'paid',
        },
        {
            title: 'Chờ shop xác nhận',
            description: 'Thanh toán đã được ghi nhận, đơn vẫn chờ cửa hàng xác nhận trước khi chuyển sang xử lý.',
            active: paymentState.value === 'paid',
            done: paymentState.value === 'paid',
        },
    ])

    const buildQrMatrix = (seedText) => {
        const size = 25
        const seed = Array.from(seedText).reduce((hash, char) => ((hash * 31 + char.charCodeAt(0)) >>> 0), 7) || 7
        let state = seed

        const random = () => {
            state = (state * 1664525 + 1013904223) >>> 0
            return state / 0xffffffff
        }

        const matrix = Array.from({length: size}, () => Array.from({length: size}, () => false))

        const paintFinder = (startX, startY) => {
            for (let y = 0; y < 7; y += 1) {
                for (let x = 0; x < 7; x += 1) {
                    const absoluteX = startX + x
                    const absoluteY = startY + y
                    const border = x === 0 || y === 0 || x === 6 || y === 6
                    const inner = x >= 2 && x <= 4 && y >= 2 && y <= 4
                    matrix[absoluteY][absoluteX] = border || inner
                }
            }
        }

        paintFinder(0, 0)
        paintFinder(size - 7, 0)
        paintFinder(0, size - 7)

        for (let y = 0; y < size; y += 1) {
            for (let x = 0; x < size; x += 1) {
                const inFinder =
                    (x < 7 && y < 7) ||
                    (x >= size - 7 && y < 7) ||
                    (x < 7 && y >= size - 7)

                if (inFinder) {
                    continue
                }

                const centerStripe = x >= 10 && x <= 14 && y >= 10 && y <= 14
                const noise = random() > (centerStripe ? 0.15 : 0.58)

                matrix[y][x] = noise
            }
        }

        return matrix
    }

    const qrMatrix = computed(() =>
        buildQrMatrix(`${selectedGateway.value}:${orderLabel.value}:${Number(amount.value || 0)}`),
    )

    const clearCountdownTimer = () => {
        if (countdownTimer) {
            window.clearInterval(countdownTimer)
            countdownTimer = null
        }
    }

    const clearSuccessTimer = () => {
        if (successTimer) {
            window.clearTimeout(successTimer)
            successTimer = null
        }
    }

    const applyRouteQuery = () => {
        const gateway = String(route.query.gateway || '').toLowerCase()
        const routeOrderId = String(route.query.order_id || '').trim()
        const routeAmount = Number(route.query.amount || 0)

        if (gateway && gateways[gateway]) {
            selectedGateway.value = gateway
        }

        if (routeOrderId) {
            orderId.value = routeOrderId
        }

        if (Number.isFinite(routeAmount) && routeAmount > 0) {
            amount.value = routeAmount
        }
    }

    const makeTransactionCode = () => {
        const stamp = Date.now().toString().slice(-8)
        return `${selectedGateway.value.toUpperCase()}-MOCK-${stamp}`
    }

    const syncPaymentStatus = async () => {
        syncMessage.value = ''
        syncError.value = ''

        if (!orderId.value) {
            paymentState.value = 'paid'
            return
        }

        paymentState.value = 'syncing'

        try {
            const response = await orderService.mockPayment(orderId.value, {
                payment_method: selectedGateway.value,
                transaction_code: transactionCode.value,
            })

            const syncedOrder = response?.data?.data

            if (syncedOrder?.payment?.transaction_code) {
                transactionCode.value = syncedOrder.payment.transaction_code
            }

            syncMessage.value = 'Đã ghi nhận thanh toán, đơn vẫn chờ cửa hàng xác nhận.'
            paymentState.value = 'paid'
            clearSuccessTimer()
            successTimer = window.setTimeout(() => {
                router.push({name: 'order.success', query: {order_id: orderId.value}})
            }, 1500)
        } catch (error) {
            syncError.value = error?.response?.data?.message || 'Không thể cập nhật trạng thái thanh toán.'
            paymentState.value = 'error'
        }
    }

    const finishPayment = async () => {
        clearCountdownTimer()
        countdown.value = 0
        transactionCode.value = makeTransactionCode()
        await syncPaymentStatus()
    }

    const startPayment = () => {
        clearCountdownTimer()
        syncMessage.value = ''
        syncError.value = ''
        paymentState.value = 'pending'
        countdown.value = 10
        transactionCode.value = makeTransactionCode()

        countdownTimer = window.setInterval(() => {
            if (countdown.value <= 1) {
                finishPayment()
                return
            }

            countdown.value -= 1
        }, 1000)
    }

    const selectGateway = (gatewayKey) => {
        selectedGateway.value = gatewayKey
        startPayment()
    }

    const cancelPayment = () => {
        clearCountdownTimer()
        paymentState.value = 'cancelled'
        countdown.value = 0
        syncMessage.value = ''
        syncError.value = 'Giao dịch đã bị hủy trước khi hoàn tất.'
    }

    const resetFlow = () => {
        applyRouteQuery()
        startPayment()
    }

    watch(
        () => route.query,
        () => {
            applyRouteQuery()
            startPayment()
        },
        {immediate: true, deep: true},
    )

    onBeforeUnmount(() => {
        clearCountdownTimer()
        clearSuccessTimer()
    })

    return {
        gateways,
        selectedGateway,
        paymentState,
        countdown,
        transactionCode,
        orderId,
        amount,
        syncMessage,
        syncError,
        gatewayMeta,
        orderLabel,
        formattedAmount,
        progressPercent,
        statusLabel,
        paymentSteps,
        qrMatrix,
        selectGateway,
        resetFlow,
        cancelPayment,
        router,
    }
}
