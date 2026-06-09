import {defineStore} from 'pinia'
import {useAuthStore} from '@/stores/authStore'
import {cartItemService} from '@/services/cartItemService'

const cloneValue = (value) => {
    try {
        return structuredClone(value)
    } catch (error) {
        return JSON.parse(JSON.stringify(value))
    }
}

const toNumber = (value) => {
    const numericValue = Number(value)
    return Number.isFinite(numericValue) ? numericValue : 0
}

const getItemPrice = (item) => {
    return toNumber(
        item?.price ??
        item?.productVariant?.sale_price ??
        item?.productVariant?.salePrice ??
        item?.productVariant?.price ??
        item?.productVariant?.display_price ??
        item?.productVariant?.displayPrice ??
        0
    )
}

const calculateCartTotals = (items) => {
    const subtotal = (Array.isArray(items) ? items : []).reduce((sum, item) => {
        return sum + (getItemPrice(item) * toNumber(item?.quantity ?? 0))
    }, 0)

    return {
        subtotal,
        total_amount: subtotal,
    }
}

const clearInvalidSession = () => {
    const authStore = useAuthStore()
    authStore.clearSession()
}

export const useCartStore = defineStore('cart', {
    state: () => ({
        items: [],
        item: null,
        loading: false,
        pagination: null,
        updateSeqById: {},
    }),

    actions: {
        applyCartPayload(payload) {
            this.item = payload
            this.items = Array.isArray(payload?.items) ? payload.items : []
            this.pagination = payload?.meta ?? this.pagination ?? null
        },

        syncCartTotals() {
            const totals = calculateCartTotals(this.items)

            if (this.item && typeof this.item === 'object') {
                this.item = {
                    ...this.item,
                    items: this.items,
                    subtotal: totals.subtotal,
                    total_amount: totals.total_amount,
                }
            }
        },

        async fetchAll(params = {}) {
            this.loading = true

            try {
                const response = await cartItemService.getAll(params)
                const payload = response.data?.data ?? response.data ?? null

                this.applyCartPayload(payload)
                this.pagination = response.data?.meta || null

                return response
            } catch (error) {
                if (error.response?.status === 401) {
                    clearInvalidSession()
                    this.items = []
                    this.item = null
                    this.pagination = null
                }

                throw error
            } finally {
                this.loading = false
            }
        },

        async fetchById(id) {
            this.loading = true

            try {
                const response = await cartItemService.getById(id)
                this.item = response.data?.data ?? response.data ?? null
                return response
            } catch (error) {
                if (error.response?.status === 401) {
                    clearInvalidSession()
                    this.items = []
                    this.item = null
                    this.pagination = null
                }

                throw error
            } finally {
                this.loading = false
            }
        },

        async create(payload) {
            const previousItems = cloneValue(this.items)
            const previousItem = this.item ? cloneValue(this.item) : null
            const quantity = Math.max(Number(payload?.quantity) || 1, 1)
            const productVariantId = Number(payload?.product_variant_id)

            const existingIndex = this.items.findIndex((cartItem) => {
                return Number(cartItem?.product_variant_id ?? cartItem?.productVariant?.id) === productVariantId
            })

            if (existingIndex >= 0) {
                const currentItem = this.items[existingIndex]
                const nextQuantity = toNumber(currentItem?.quantity ?? 0) + quantity
                const price = getItemPrice(currentItem)

                this.items = this.items.map((cartItem, index) => {
                    if (index !== existingIndex) {
                        return cartItem
                    }

                    return {
                        ...cartItem,
                        quantity: nextQuantity,
                        subtotal: price * nextQuantity,
                        price: cartItem.price ?? price,
                    }
                })
            } else {
                this.items = [
                    ...this.items,
                    {
                        id: `temp-${Date.now()}-${productVariantId}`,
                        product_variant_id: productVariantId,
                        quantity,
                        price: toNumber(payload?.unit_price ?? payload?.price ?? 0),
                        subtotal: toNumber(payload?.unit_price ?? payload?.price ?? 0) * quantity,
                        productVariant: payload?.productVariant ?? null,
                        __temp: true,
                    },
                ]
            }

            if (this.item && typeof this.item === 'object') {
                this.item = {
                    ...this.item,
                    items: this.items,
                }
                this.syncCartTotals()
            }

            try {
                const response = await cartItemService.create(payload)
                const responseData = response.data?.data ?? response.data ?? null

                if (responseData && typeof responseData === 'object') {
                    const createdVariantId = Number(
                        responseData.product_variant_id ??
                        responseData.productVariant?.id ??
                        payload?.product_variant_id
                    )
                    const createdQuantity = Math.max(Number(responseData.quantity ?? quantity) || quantity, 1)
                    const createdId = responseData.id

                    this.items = this.items.map((cartItem) => {
                        const cartVariantId = Number(
                            cartItem?.product_variant_id ??
                            cartItem?.productVariant?.id
                        )

                        if (
                            (createdId && cartItem.id === createdId) ||
                            (createdVariantId && cartVariantId === createdVariantId) ||
                            cartItem.__temp
                        ) {
                            const mergedPrice = getItemPrice({
                                ...cartItem,
                                ...responseData,
                            })
                            return {
                                ...cartItem,
                                ...responseData,
                                id: createdId ?? cartItem.id,
                                product_variant_id: createdVariantId || cartItem.product_variant_id,
                                quantity: createdQuantity,
                                price: cartItem.price ?? mergedPrice,
                                subtotal: mergedPrice * createdQuantity,
                                __temp: false,
                            }
                        }

                        return cartItem
                    })

                    if (this.item && Array.isArray(this.item.items)) {
                        this.item = {
                            ...this.item,
                            items: this.items,
                        }
                        this.syncCartTotals()
                    }
                }

                void this.fetchAll().catch(() => {
                })
                return response
            } catch (error) {
                if (error.response?.status === 401) {
                    clearInvalidSession()
                }

                this.items = previousItems
                this.item = previousItem
                throw error
            }
        },

        async update(id, payload) {
            const previousItems = cloneValue(this.items)
            const previousItem = this.item ? cloneValue(this.item) : null
            const currentSeq = (this.updateSeqById[id] ?? 0) + 1
            this.updateSeqById = {
                ...this.updateSeqById,
                [id]: currentSeq,
            }

            const nextQuantity = payload?.quantity !== undefined
                ? Math.max(Number(payload.quantity) || 0, 1)
                : null

            try {
                if (nextQuantity !== null) {
                    this.items = this.items.map((cartItem) => {
                        if (cartItem.id !== id) {
                            return cartItem
                        }

                        const price = getItemPrice(cartItem)
                        const subtotal = price * nextQuantity

                        return {
                            ...cartItem,
                            quantity: nextQuantity,
                            subtotal,
                            price: cartItem.price ?? price,
                        }
                    })

                    if (this.item && Array.isArray(this.item.items)) {
                        this.item = {
                            ...this.item,
                            items: this.items,
                        }
                        this.syncCartTotals()
                    }
                }

                const response = await cartItemService.update(id, payload)
                const responseData = response.data?.data ?? response.data ?? null

                if (this.updateSeqById[id] !== currentSeq) {
                    return response
                }

                if (responseData && typeof responseData === 'object') {
                    this.items = this.items.map((cartItem) => {
                        if (cartItem.id !== id) {
                            return cartItem
                        }

                        return {
                            ...cartItem,
                            ...responseData,
                            quantity: Number(responseData.quantity ?? cartItem.quantity ?? payload?.quantity ?? 1),
                        }
                    })

                    if (this.item && Array.isArray(this.item.items)) {
                        this.item = {
                            ...this.item,
                            items: this.items,
                        }
                        this.syncCartTotals()
                    }
                }

                return response
            } catch (error) {
                if (error.response?.status === 401) {
                    clearInvalidSession()
                }

                if (this.updateSeqById[id] !== currentSeq) {
                    return null
                }

                this.items = previousItems
                this.item = previousItem
                throw error
            }
        },

        async remove(id) {
            const previousItems = cloneValue(this.items)
            const previousItem = this.item ? cloneValue(this.item) : null

            this.items = this.items.filter((cartItem) => cartItem.id !== id)

            if (this.item && Array.isArray(this.item.items)) {
                this.item = {
                    ...this.item,
                    items: this.items,
                }
                this.syncCartTotals()
            }

            try {
                const response = await cartItemService.delete(id)
                return response
            } catch (error) {
                if (error.response?.status === 401) {
                    clearInvalidSession()
                }

                this.items = previousItems
                this.item = previousItem
                throw error
            }
        },
    },
})
