import {defineStore} from 'pinia'
import {couponService} from '@/services/couponService'
import {useNotificationStore} from '@/stores/notificationStore.js'

const STORAGE_KEY = 'applied_coupon'

const safeParse = (value) => {
    try {
        return JSON.parse(value)
    } catch (error) {
        return null
    }
}

const readStoredCoupon = () => {
    if (typeof window === 'undefined') {
        return null
    }

    const raw = window.localStorage.getItem(STORAGE_KEY)
    if (!raw) {
        return null
    }

    const parsed = safeParse(raw)
    return parsed && typeof parsed === 'object' ? parsed : null
}

const writeStoredCoupon = (coupon) => {
    if (typeof window === 'undefined') {
        return
    }

    if (!coupon) {
        window.localStorage.removeItem(STORAGE_KEY)
        return
    }

    window.localStorage.setItem(STORAGE_KEY, JSON.stringify(coupon))
}

const calculateDiscount = (coupon, subtotal) => {
    if (!coupon) {
        return 0
    }

    const baseAmount = Math.max(Number(subtotal) || 0, 0)
    const value = Math.max(Number(coupon.value) || 0, 0)
    let discount = coupon.type === 'fixed' ? value : baseAmount * (value / 100)

    if (coupon.max_discount !== null && coupon.max_discount !== undefined && coupon.max_discount !== '') {
        discount = Math.min(discount, Math.max(Number(coupon.max_discount) || 0, 0))
    }

    return Math.max(Math.min(discount, baseAmount), 0)
}

export const useCouponStore = defineStore('coupon', {
    state: () => ({
        inputCode: '',
        appliedCoupon: readStoredCoupon(),
        loading: false,
        error: '',
    }),

    getters: {
        hasCoupon: (state) => Boolean(state.appliedCoupon),
        appliedCode: (state) => state.appliedCoupon?.code || '',
        discountAmount: (state) => (subtotal) => calculateDiscount(state.appliedCoupon, subtotal),
    },

    actions: {
        hydrate() {
            this.appliedCoupon = readStoredCoupon()
            this.inputCode = this.appliedCoupon?.code || ''
        },

        persist() {
            writeStoredCoupon(this.appliedCoupon)
        },

        clear() {
            this.inputCode = ''
            this.appliedCoupon = null
            this.error = ''
            this.persist()
        },

        async apply(subtotal) {
            const code = String(this.inputCode || '').trim().toUpperCase()

            if (!code) {
                this.error = 'Vui lòng nhập mã coupon.'
                return null
            }

            this.loading = true
            this.error = ''

            try {
                const response = await couponService.apply({
                    code,
                    subtotal: Number(subtotal) || 0,
                })

                const payload = response.data?.data ?? response.data ?? {}
                this.appliedCoupon = payload.coupon || null
                this.inputCode = this.appliedCoupon?.code || code
                this.persist()

                useNotificationStore().success('Đã áp dụng mã giảm giá.')
                return response
            } catch (error) {
                this.appliedCoupon = null
                this.error = error.response?.data?.message || 'Không áp dụng được mã giảm giá.'
                this.persist()

                if (error?.response?.status === 422) {
                    return null
                }

                throw error
            } finally {
                this.loading = false
            }
        },
    },
})

