<script setup>
import {computed, onBeforeUnmount, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {orderService} from '@/services/orderService'

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
</script>

<template>
  <div class="payment-page">
    <section class="hero">
      <div>
        <p class="eyebrow">THANH TOÁN QR DEMO</p>
        <h1>Quét mã để hoàn tất giao dịch</h1>
        <p class="subtitle">
          Giao diện mô phỏng cổng thanh toán thật: có QR, có đếm ngược và tự động cập nhật trạng thái đơn sau 10 giây.
        </p>
      </div>

      <div class="status-card">
        <span class="status-card__label">{{ statusLabel }}</span>
        <strong>{{ countdown }}s</strong>
        <small>Đơn {{ orderLabel }} · {{ gatewayMeta.name }}</small>
      </div>
    </section>

    <section class="content-grid">
      <article class="panel payment-panel">
        <div class="panel-head">
          <div>
            <p class="panel-kicker">Cổng thanh toán</p>
            <h2>{{ gatewayMeta.name }}</h2>
          </div>

          <div class="gateway-switch">
            <button
              v-for="gateway in gateways"
              :key="gateway.key"
              type="button"
              class="gateway-switch__btn"
              :class="{active: selectedGateway === gateway.key}"
              @click="selectGateway(gateway.key)"
            >
              {{ gateway.name }}
            </button>
          </div>
        </div>

        <div class="gateway-summary">
          <div class="gateway-summary__brand" :class="`gateway-summary__brand--${gatewayMeta.accent}`">
            {{ gatewayMeta.badge }}
          </div>

          <div class="gateway-summary__meta">
            <strong>Đơn hàng {{ orderLabel }}</strong>
            <span>{{ gatewayMeta.description }}</span>
          </div>

          <div class="gateway-summary__amount">
            <span>Số tiền</span>
            <strong>{{ formattedAmount }} đ</strong>
          </div>
        </div>

        <div class="qr-shell">
          <div class="qr-stage">
            <div class="qr-phone" :class="`qr-phone--${gatewayMeta.accent}`">
              <div class="qr-phone__topbar">
                <span></span>
                <span></span>
                <span></span>
              </div>

              <div class="qr-phone__header">
                <div class="qr-phone__merchant">
                  <div class="merchant-badge">{{ gatewayMeta.badge }}</div>
                  <div>
                    <strong>Thanh toán QR</strong>
                    <small>Đơn {{ orderLabel }}</small>
                  </div>
                </div>

                <div class="qr-phone__amount">
                  <span>Số tiền</span>
                  <strong>{{ formattedAmount }} đ</strong>
                </div>
              </div>

              <div class="qr-phone__screen">
                <div class="qr-frame" :class="`qr-frame--${gatewayMeta.accent}`">
                  <div class="qr-grid" :class="{ 'qr-grid--paid': paymentState === 'paid' }">
                    <span
                      v-for="(row, rowIndex) in qrMatrix"
                      :key="rowIndex"
                      class="qr-row"
                    >
                      <i
                        v-for="(cell, cellIndex) in row"
                        :key="cellIndex"
                        class="qr-cell"
                        :class="{ 'qr-cell--on': cell, 'qr-cell--off': !cell }"
                      />
                    </span>
                  </div>

                  <div class="qr-rings" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                  </div>

                  <div class="qr-scanline" aria-hidden="true">
                    <span />
                  </div>

                  <div class="qr-mask">
                    <span>QR DEMO</span>
                    <strong>KHÔNG QUÉT ĐƯỢC</strong>
                    <small>Tự động xác nhận sau {{ countdown }} giây</small>
                  </div>
                </div>
              </div>

              <div class="qr-phone__footer">
                <div class="qr-phone__status">
                  <i class="bi bi-shield-lock"></i>
                  <span>{{ statusLabel }}</span>
                </div>
                <div class="progress-line" aria-hidden="true">
                  <span :style="{width: `${progressPercent}%`}" />
                </div>
              </div>
            </div>

            <aside class="qr-sidecard">
              <div class="qr-sidecard__header">
                <span class="qr-sidecard__tag">{{ gatewayMeta.name }}</span>
                <strong>Hướng dẫn quét</strong>
                <p>
                  Dùng ứng dụng ngân hàng hoặc ví điện tử để quét mã bên trái, rồi xác nhận trong app để hoàn tất giao dịch.
                </p>
              </div>

              <div class="qr-guide">
                <div class="qr-guide__item">
                  <i class="bi bi-phone"></i>
                  <div>
                    <strong>Mở ứng dụng</strong>
                    <span>Chọn QR Pay hoặc quét mã.</span>
                  </div>
                </div>
                <div class="qr-guide__item">
                  <i class="bi bi-upc-scan"></i>
                  <div>
                    <strong>Quét mã</strong>
                    <span>Đơn hàng và số tiền sẽ tự điền.</span>
                  </div>
                </div>
                <div class="qr-guide__item">
                  <i class="bi bi-check2-circle"></i>
                  <div>
                    <strong>Xác nhận</strong>
                    <span>Hệ thống mock sẽ tự đổi trạng thái sau 10 giây.</span>
                  </div>
                </div>
              </div>

              <div class="qr-meta">
                <div>
                  <span>Mã giao dịch</span>
                  <strong>{{ transactionCode }}</strong>
                </div>
                <div>
                  <span>Trạng thái</span>
                  <strong>{{ statusLabel }}</strong>
                </div>
              </div>

              <p class="qr-note">
                Giao diện này được dựng để mô phỏng màn hình quét QR thực tế: ít chữ, tập trung vào mã QR, trạng thái và số tiền.
              </p>
            </aside>
          </div>

          <div class="qr-footer">
            <div class="qr-footer__chip">
              <i class="bi bi-phone"></i>
              <span>Mở app ngân hàng hoặc ví điện tử để quét</span>
            </div>

            <div class="qr-footer__chip qr-footer__chip--soft">
              <i class="bi bi-lightning-charge-fill"></i>
              <span>Tự chuyển sang thành công sau 10 giây nếu không quét</span>
            </div>
          </div>
        </div>

        <div class="action-row">
          <button type="button" class="primary-btn" @click="resetFlow">
            <i class="bi bi-arrow-repeat"></i>
            Chạy lại 10 giây
          </button>
          <button type="button" class="ghost-btn" @click="cancelPayment">
            <i class="bi bi-x-circle"></i>
            Hủy giao dịch
          </button>
        </div>
      </article>

      <aside class="panel side-panel">
        <div class="panel-head">
          <div>
            <p class="panel-kicker">Trạng thái</p>
            <h2>Xử lý thanh toán</h2>
          </div>
          <span class="state-pill" :class="paymentState">{{ statusLabel }}</span>
        </div>

        <div class="timeline">
          <div
            v-for="(step, index) in paymentSteps"
            :key="step.title"
            class="timeline-item"
            :class="{active: step.active, done: step.done}"
          >
            <div class="timeline-item__dot">
              <span>{{ index + 1 }}</span>
            </div>
            <div class="timeline-item__body">
              <strong>{{ step.title }}</strong>
              <p>{{ step.description }}</p>
            </div>
          </div>
        </div>

        <div class="detail-grid">
          <div>
            <span>Mã giao dịch</span>
            <strong>{{ transactionCode }}</strong>
          </div>
          <div>
            <span>Gateway</span>
            <strong>{{ gatewayMeta.name }}</strong>
          </div>
          <div>
            <span>Mã đơn</span>
            <strong>{{ orderLabel }}</strong>
          </div>
          <div>
            <span>Số tiền</span>
            <strong>{{ formattedAmount }} đ</strong>
          </div>
        </div>

        <div v-if="paymentState === 'paid'" class="result-box result-box--success">
          <i class="bi bi-check2-circle"></i>
          <div>
            <strong>Đã ghi nhận thanh toán</strong>
            <p>{{ syncMessage || 'Giao dịch thành công, đơn vẫn đang chờ cửa hàng xác nhận để tiếp tục xử lý.' }}</p>
          </div>
        </div>

        <div v-else-if="paymentState === 'error'" class="result-box result-box--error">
          <i class="bi bi-exclamation-triangle"></i>
          <div>
            <strong>Xác nhận thất bại</strong>
            <p>{{ syncError }}</p>
          </div>
        </div>

        <div v-else class="result-box result-box--info">
          <i class="bi bi-shield-check"></i>
          <div>
            <strong>Đang chờ kết quả</strong>
            <p>Trang này mô phỏng giao diện thanh toán thật để test luồng VNPay và MoMo theo trạng thái đã thanh toán nhưng chưa xác nhận.</p>
          </div>
        </div>

        <button
          v-if="paymentState === 'paid'"
          type="button"
          class="success-btn"
          @click="router.push({name: 'order.success', query: {order_id: orderId}})"
        >
          <i class="bi bi-arrow-right-circle"></i>
          Chuyển đến trang thành công
        </button>
      </aside>
    </section>
  </div>
</template>

<style scoped>
.payment-page {
  max-width: 1320px;
  margin: 0 auto;
  padding: 24px;
}

.hero {
  display: flex;
  justify-content: space-between;
  gap: 20px;
  padding: 28px;
  border-radius: 24px;
  border: 1px solid #e5edf7;
  background:
    radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 28%),
    radial-gradient(circle at bottom left, rgba(244, 63, 94, 0.10), transparent 24%),
    linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
  box-shadow: 0 18px 46px rgba(15, 23, 42, 0.06);
}

.eyebrow,
.panel-kicker {
  margin: 0 0 8px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 900;
  letter-spacing: 0.18em;
  text-transform: uppercase;
}

h1 {
  margin: 0;
  color: #0f172a;
  font-size: clamp(28px, 4vw, 44px);
  font-weight: 900;
  letter-spacing: -0.04em;
}

.subtitle {
  max-width: 760px;
  margin: 12px 0 0;
  color: #475569;
  font-size: 16px;
  line-height: 1.7;
}

.status-card {
  min-width: 220px;
  align-self: flex-end;
  padding: 18px 20px;
  border-radius: 18px;
  background: linear-gradient(135deg, #2563eb 0%, #38bdf8 100%);
  color: #fff;
  box-shadow: 0 16px 28px rgba(37, 99, 235, 0.22);
}

.status-card__label,
.status-card strong,
.status-card small {
  display: block;
}

.status-card__label {
  font-size: 13px;
  opacity: 0.95;
}

.status-card strong {
  margin-top: 6px;
  font-size: 20px;
  font-weight: 900;
}

.status-card small {
  margin-top: 6px;
  font-size: 12px;
  opacity: 0.9;
}

.content-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.1fr) minmax(330px, 0.9fr);
  gap: 22px;
  margin-top: 22px;
}

.panel {
  padding: 24px;
  border-radius: 22px;
  background: #fff;
  border: 1px solid #e8eef7;
  box-shadow: 0 16px 42px rgba(15, 23, 42, 0.05);
}

.panel-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 14px;
  margin-bottom: 18px;
}

.panel-head h2 {
  margin: 0;
  color: #0f172a;
  font-size: 24px;
  font-weight: 900;
}

.gateway-switch {
  display: inline-flex;
  gap: 8px;
  padding: 6px;
  border-radius: 999px;
  background: #f1f5f9;
}

.gateway-switch__btn {
  min-height: 36px;
  padding: 0 14px;
  border: 0;
  border-radius: 999px;
  background: transparent;
  color: #475569;
  font-size: 13px;
  font-weight: 800;
  cursor: pointer;
  transition: background-color 0.2s ease, color 0.2s ease, transform 0.2s ease;
}

.gateway-switch__btn.active {
  background: #fff;
  color: #1d4ed8;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
}

.gateway-summary {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 16px;
  align-items: center;
  padding: 16px;
  border-radius: 20px;
  background: linear-gradient(135deg, #f8fbff 0%, #ffffff 100%);
  border: 1px solid #e5edf8;
}

.gateway-summary__brand {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 110px;
  min-height: 48px;
  padding: 0 16px;
  border-radius: 14px;
  color: #fff;
  font-weight: 900;
  letter-spacing: 0.02em;
  box-shadow: 0 10px 22px rgba(15, 23, 42, 0.12);
}

.gateway-summary__brand--blue {
  background: linear-gradient(135deg, #0b4fd1 0%, #1d6bff 100%);
}

.gateway-summary__brand--pink {
  background: linear-gradient(135deg, #d61b7a 0%, #ff4ea3 100%);
}

.gateway-summary__meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.gateway-summary__meta strong {
  color: #0f172a;
  font-size: 16px;
  font-weight: 900;
}

.gateway-summary__meta span {
  color: #475569;
  line-height: 1.5;
}

.gateway-summary__amount {
  text-align: right;
}

.gateway-summary__amount span {
  display: block;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 4px;
}

.gateway-summary__amount strong {
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
}

.qr-shell {
  margin-top: 18px;
  display: grid;
  gap: 14px;
}

.qr-stage {
  display: grid;
  grid-template-columns: minmax(0, 1fr) 320px;
  gap: 16px;
  align-items: stretch;
}

.qr-phone {
  position: relative;
  border-radius: 30px;
  padding: 16px;
  background: linear-gradient(180deg, #f8fbff 0%, #eef5ff 100%);
  border: 1px solid #dbe7f7;
  box-shadow: 0 24px 50px rgba(15, 23, 42, 0.08);
  overflow: hidden;
}

.qr-phone::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at top right, rgba(37, 99, 235, 0.14), transparent 24%),
    radial-gradient(circle at bottom left, rgba(244, 63, 94, 0.10), transparent 20%);
  pointer-events: none;
}

.qr-phone__topbar {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: center;
  gap: 6px;
  margin-bottom: 14px;
}

.qr-phone__topbar span {
  width: 8px;
  height: 8px;
  border-radius: 999px;
  background: rgba(15, 23, 42, 0.18);
}

.qr-phone__header {
  position: relative;
  z-index: 1;
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: center;
  padding: 14px 16px;
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.82);
  border: 1px solid rgba(15, 23, 42, 0.06);
  backdrop-filter: blur(12px);
}

.qr-phone__merchant {
  display: flex;
  align-items: center;
  gap: 12px;
}

.merchant-badge {
  min-width: 88px;
  padding: 10px 12px;
  border-radius: 16px;
  color: #fff;
  font-size: 12px;
  font-weight: 900;
  text-align: center;
  letter-spacing: 0.02em;
  background: linear-gradient(135deg, #0b4fd1 0%, #1d6bff 100%);
  box-shadow: 0 10px 18px rgba(29, 107, 255, 0.22);
}

.qr-phone--pink .merchant-badge {
  background: linear-gradient(135deg, #d61b7a 0%, #ff4ea3 100%);
  box-shadow: 0 10px 18px rgba(214, 27, 122, 0.22);
}

.qr-phone__merchant strong,
.qr-phone__merchant small {
  display: block;
}

.qr-phone__merchant strong {
  color: #0f172a;
  font-size: 16px;
  font-weight: 900;
}

.qr-phone__merchant small {
  margin-top: 4px;
  color: #64748b;
  font-size: 12px;
}

.qr-phone__amount {
  text-align: right;
}

.qr-phone__amount span {
  display: block;
  color: #64748b;
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.qr-phone__amount strong {
  margin-top: 4px;
  display: block;
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
}

.qr-phone__screen {
  position: relative;
  z-index: 1;
  padding: 18px 4px 14px;
}

.qr-frame {
  position: relative;
  display: grid;
  place-items: center;
  min-height: 520px;
  border-radius: 28px;
  padding: 28px;
  overflow: hidden;
  background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
  border: 1px solid #d8e5f6;
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.5);
}

.qr-frame::before {
  content: '';
  position: absolute;
  inset: 18px;
  border-radius: 24px;
  border: 1px dashed rgba(37, 99, 235, 0.18);
  pointer-events: none;
}

.qr-frame--blue {
  box-shadow: inset 0 0 0 1px rgba(37, 99, 235, 0.08);
}

.qr-frame--pink {
  box-shadow: inset 0 0 0 1px rgba(236, 72, 153, 0.08);
}

.qr-grid {
  width: min(100%, 320px);
  aspect-ratio: 1;
  padding: 16px;
  border-radius: 24px;
  background: #fff;
  box-shadow: 0 20px 36px rgba(15, 23, 42, 0.08);
  display: grid;
  gap: 2px;
  position: relative;
  z-index: 1;
}

.qr-row {
  display: grid;
  grid-template-columns: repeat(25, minmax(0, 1fr));
  gap: 2px;
}

.qr-cell {
  aspect-ratio: 1;
  border-radius: 2px;
}

.qr-cell--on {
  background: #0f172a;
}

.qr-cell--off {
  background: #eff6ff;
}

.qr-grid--paid {
  position: relative;
  filter: saturate(0.92) brightness(0.98);
}

.qr-grid--paid::after {
  content: '';
  position: absolute;
  inset: 0;
  border-radius: 24px;
  background: linear-gradient(135deg, rgba(34, 197, 94, 0.08), rgba(59, 130, 246, 0.08));
}

.qr-rings {
  position: absolute;
  inset: auto 50% 36px;
  transform: translateX(-50%);
  display: grid;
  place-items: center;
  width: 180px;
  height: 180px;
  pointer-events: none;
}

.qr-rings span {
  position: absolute;
  border-radius: 50%;
  border: 1px solid rgba(37, 99, 235, 0.12);
}

.qr-rings span:nth-child(1) {
  width: 120px;
  height: 120px;
}

.qr-rings span:nth-child(2) {
  width: 150px;
  height: 150px;
}

.qr-rings span:nth-child(3) {
  width: 180px;
  height: 180px;
}

.qr-scanline {
  position: absolute;
  inset: 28px 28px 28px;
  pointer-events: none;
  overflow: hidden;
  border-radius: 24px;
}

.qr-scanline span {
  position: absolute;
  left: 0;
  right: 0;
  height: 4px;
  border-radius: 999px;
  background: linear-gradient(90deg, transparent, rgba(37, 99, 235, 0.7), transparent);
  animation: scanline 2.8s linear infinite;
  box-shadow: 0 0 20px rgba(37, 99, 235, 0.22);
}

.qr-mask {
  position: absolute;
  inset: auto 50% 28px;
  transform: translateX(-50%);
  width: min(78%, 240px);
  padding: 16px 18px;
  border-radius: 20px;
  text-align: center;
  background: rgba(255, 255, 255, 0.92);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(15, 23, 42, 0.08);
  box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
}

.qr-mask span {
  display: inline-block;
  margin-bottom: 8px;
  padding: 4px 10px;
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.qr-mask strong {
  display: block;
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
  letter-spacing: -0.03em;
}

.qr-mask small {
  display: block;
  margin-top: 6px;
  color: #475569;
  font-size: 12px;
  line-height: 1.6;
}

.qr-footer {
  position: relative;
  z-index: 1;
  display: grid;
  gap: 10px;
  padding: 0 8px 6px;
}

.qr-footer__chip {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  width: fit-content;
  padding: 10px 14px;
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 13px;
  font-weight: 800;
}

.qr-footer__chip--soft {
  background: #f8fafc;
  color: #0f172a;
}

.qr-footer__chip--soft i {
  color: #f59e0b;
}

.progress-line {
  height: 10px;
  border-radius: 999px;
  overflow: hidden;
  background: #e5eefb;
}

.progress-line span {
  display: block;
  height: 100%;
  border-radius: inherit;
  background: linear-gradient(90deg, #2563eb 0%, #38bdf8 100%);
  transition: width 0.3s ease;
}

.qr-note {
  margin: 0;
  color: #475569;
  font-size: 14px;
  line-height: 1.7;
}

.qr-sidecard {
  display: grid;
  gap: 18px;
  align-content: start;
  padding: 20px;
  border-radius: 24px;
  background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  border: 1px solid #e5edf8;
  box-shadow: 0 18px 38px rgba(15, 23, 42, 0.05);
}

.qr-sidecard__tag {
  display: inline-flex;
  width: fit-content;
  margin-bottom: 10px;
  padding: 6px 10px;
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 12px;
  font-weight: 900;
}

.qr-sidecard__header strong {
  display: block;
  color: #0f172a;
  font-size: 18px;
  font-weight: 900;
  margin-bottom: 8px;
}

.qr-sidecard__header p {
  margin: 0;
  color: #475569;
  font-size: 14px;
  line-height: 1.7;
}

.qr-guide {
  display: grid;
  gap: 12px;
}

.qr-guide__item {
  display: grid;
  grid-template-columns: 40px 1fr;
  gap: 12px;
  align-items: start;
  padding: 12px;
  border-radius: 18px;
  background: #fff;
  border: 1px solid #e8eef7;
}

.qr-guide__item i {
  width: 40px;
  height: 40px;
  border-radius: 14px;
  display: grid;
  place-items: center;
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  color: #2563eb;
  font-size: 18px;
}

.qr-guide__item strong {
  display: block;
  color: #0f172a;
  font-size: 14px;
  font-weight: 900;
  margin-bottom: 4px;
}

.qr-guide__item span {
  color: #475569;
  font-size: 13px;
  line-height: 1.5;
}

.qr-meta {
  display: grid;
  gap: 12px;
  padding: 16px;
  border-radius: 18px;
  background: #0f172a;
  color: #fff;
}

.qr-meta span {
  display: block;
  margin-bottom: 4px;
  color: rgba(255, 255, 255, 0.72);
  font-size: 12px;
  font-weight: 700;
}

.qr-meta strong {
  display: block;
  font-size: 14px;
  font-weight: 900;
  word-break: break-word;
}

.action-row {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 8px;
}

.primary-btn,
.ghost-btn,
.success-btn {
  min-height: 46px;
  padding: 0 16px;
  border: 0;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 800;
  cursor: pointer;
}

.primary-btn {
  background: linear-gradient(135deg, #2563eb, #38bdf8);
  color: #fff;
}

.ghost-btn {
  background: #eff6ff;
  color: #1d4ed8;
}

.success-btn {
  margin-top: 16px;
  width: 100%;
  background: linear-gradient(135deg, #16a34a, #22c55e);
  color: #fff;
}

.side-panel {
  display: flex;
  flex-direction: column;
}

.timeline {
  display: grid;
  gap: 14px;
  margin-top: 4px;
}

.timeline-item {
  display: grid;
  grid-template-columns: 40px 1fr;
  gap: 14px;
  align-items: flex-start;
}

.timeline-item__dot {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 14px;
  font-weight: 900;
}

.timeline-item.active .timeline-item__dot {
  background: linear-gradient(135deg, #2563eb, #38bdf8);
  color: #fff;
}

.timeline-item.done .timeline-item__dot {
  background: #ecfdf5;
  color: #15803d;
}

.timeline-item__body strong {
  display: block;
  margin-bottom: 4px;
  color: #0f172a;
  font-size: 15px;
  font-weight: 900;
}

.timeline-item__body p {
  margin: 0;
  color: #475569;
  font-size: 13px;
  line-height: 1.6;
}

.detail-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
  margin-top: 20px;
  padding: 18px;
  border-radius: 18px;
  background: linear-gradient(135deg, #f8fbff 0%, #ffffff 100%);
  border: 1px solid #e5edf8;
}

.detail-grid span {
  display: block;
  margin-bottom: 4px;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.detail-grid strong {
  color: #0f172a;
  font-size: 14px;
  font-weight: 900;
  word-break: break-word;
}

.result-box {
  display: flex;
  gap: 12px;
  align-items: flex-start;
  margin-top: 18px;
  padding: 16px;
  border-radius: 18px;
}

.result-box i {
  font-size: 24px;
  line-height: 1;
  margin-top: 2px;
}

.result-box strong {
  display: block;
  margin-bottom: 4px;
  font-size: 15px;
  font-weight: 900;
}

.result-box p {
  margin: 0;
  font-size: 13px;
  line-height: 1.6;
}

.result-box--success {
  background: #ecfdf5;
  color: #166534;
}

.result-box--success i {
  color: #16a34a;
}

.result-box--error {
  background: #fef2f2;
  color: #b91c1c;
}

.result-box--error i {
  color: #ef4444;
}

.result-box--info {
  background: #eff6ff;
  color: #1d4ed8;
}

.result-box--info i {
  color: #2563eb;
}

.state-pill {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  border-radius: 999px;
  background: #eff6ff;
  color: #1d4ed8;
  font-size: 12px;
  font-weight: 800;
}

.state-pill.pending,
.state-pill.syncing {
  background: #fff7ed;
  color: #c2410c;
}

.state-pill.paid {
  background: #ecfdf5;
  color: #15803d;
}

.state-pill.cancelled,
.state-pill.error {
  background: #fef2f2;
  color: #b91c1c;
}

@keyframes scanline {
  0% {
    top: 0;
    opacity: 0;
  }
  10% {
    opacity: 1;
  }
  50% {
    opacity: 1;
  }
  90% {
    opacity: 1;
  }
  100% {
    top: calc(100% - 4px);
    opacity: 0;
  }
}

@media (max-width: 1100px) {
  .content-grid {
    grid-template-columns: 1fr;
  }

  .hero {
    flex-direction: column;
  }

  .status-card {
    align-self: flex-start;
  }

  .gateway-summary {
    grid-template-columns: 1fr;
    justify-items: start;
  }

  .gateway-summary__amount {
    text-align: left;
  }

  .qr-stage {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .payment-page {
    padding: 16px;
  }

  .panel,
  .hero {
    padding: 18px;
    border-radius: 18px;
  }

  .panel-head {
    flex-direction: column;
  }

  .detail-grid {
    grid-template-columns: 1fr;
  }

  .qr-frame {
    min-height: 420px;
  }

  .qr-grid {
    width: min(100%, 300px);
  }

  .qr-phone__header {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>
