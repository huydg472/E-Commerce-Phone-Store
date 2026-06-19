import {computed, onMounted, reactive, ref, watch} from 'vue'
import {useRoute} from 'vue-router'
import {storeToRefs} from 'pinia'
import {useProductStore} from '@/stores/productStore.js'
import {useProductVariantStore} from '@/stores/productVariantStore.js'
import {useClientPagination} from '@/composables/useClientPagination.js'
import {formatCurrency} from '@/utils/formatCurrency.js'
import {formatDate} from '@/utils/formatDate.js'

export function useProductVariantPage() {
    const route = useRoute()
    const productStore = useProductStore()
    const variantStore = useProductVariantStore()

    const {item: product, loading: productLoading} = storeToRefs(productStore)

    const loadingError = ref('')
    const formError = ref('')
    const showModal = ref(false)
    const saving = ref(false)
    const deletingId = ref(null)
    const editingVariantId = ref(null)
    const fieldErrors = reactive({})

    const productId = computed(() => route.params.id)
    const isActiveTab = (name) => route.name === name
    const variantRows = computed(() => product.value?.productVariants || product.value?.product_variants || [])

    const {
        currentPage,
        pageSize,
        totalPages,
        paginatedItems: paginatedVariants,
        pageStart,
        pageEnd,
    } = useClientPagination(variantRows, {
        defaultPageSize: 5,
        pageSizeOptions: [5, 10],
    })

    const codePart = (value) => {
        const code = String(value ?? '')
            .trim()
            .toUpperCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^A-Z0-9]+/g, '')

        return code || 'NA'
    }

    const productCode = computed(() => {
        const source = String(product.value?.slug || product.value?.name || `SP${productId.value}`)
            .trim()
            .toUpperCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')

        const tokens = source.split(/[^A-Z0-9]+/).filter(Boolean)
        const code = tokens
            .map((token) => (/\d/.test(token) ? token : token.slice(0, 1)))
            .join('')

        return code || `SP${productId.value}`
    })

    const generateSku = () => {
        return [
            productCode.value,
            codePart(form.color),
            codePart(form.storage),
            codePart(form.ram),
        ].join('-')
    }

    const summary = computed(() => {
        const variants = variantRows.value
        const active = variants.filter((variant) => variant?.status === 'active').length
        const inactive = variants.length - active
        const featured = variants.filter((variant) => Boolean(variant?.is_featured)).length

        return {
            total: variants.length,
            active,
            inactive,
            featured,
        }
    })

    const form = reactive({
        product_id: '',
        color: '',
        storage: '',
        ram: '',
        sku: '',
        import_price: '',
        price: '',
        sale_price: '',
        quantity: 0,
        status: 'active',
        is_featured: false,
        description: '',
    })

    watch(
        () => [productCode.value, form.color, form.storage, form.ram],
        () => {
            form.sku = generateSku()
        }
    )

    const clearFieldErrors = () => {
        Object.keys(fieldErrors).forEach((key) => {
            delete fieldErrors[key]
        })
    }

    const setFieldErrors = (errors = {}) => {
        clearFieldErrors()

        Object.entries(errors).forEach(([key, value]) => {
            fieldErrors[key] = Array.isArray(value) ? value[0] : value
        })
    }

    const resetForm = () => {
        form.product_id = String(productId.value || '')
        form.color = ''
        form.storage = ''
        form.ram = ''
        form.sku = generateSku()
        form.import_price = ''
        form.price = ''
        form.sale_price = ''
        form.quantity = 0
        form.status = 'active'
        form.is_featured = false
        form.description = ''
        editingVariantId.value = null
    }

    const loadProduct = async () => {
        loadingError.value = ''

        try {
            await productStore.fetchById(productId.value)
        } catch (error) {
            loadingError.value = error.response?.data?.message || 'KhÃ´ng táº£i Ä‘Æ°á»£c dá»¯ liá»‡u sáº£n pháº©m.'
        }
    }

    const openCreateModal = () => {
        resetForm()
        formError.value = ''
        clearFieldErrors()
        showModal.value = true
    }

    const openEditModal = (variant) => {
        resetForm()
        editingVariantId.value = variant?.id ?? null
        form.product_id = String(variant?.product_id ?? productId.value ?? '')
        form.color = variant?.color ?? ''
        form.storage = variant?.storage ?? ''
        form.ram = variant?.ram ?? ''
        form.sku = generateSku()
        form.import_price = variant?.import_price ?? ''
        form.price = variant?.price ?? ''
        form.sale_price = variant?.sale_price ?? ''
        form.quantity = variant?.quantity ?? 0
        form.status = variant?.status ?? 'active'
        form.is_featured = Boolean(variant?.is_featured)
        form.description = variant?.description ?? ''
        formError.value = ''
        clearFieldErrors()
        showModal.value = true
    }

    const closeModal = () => {
        showModal.value = false
        formError.value = ''
        clearFieldErrors()
        resetForm()
    }

    const loadData = async () => {
        await loadProduct()
        resetForm()
    }

    const handleSubmit = async () => {
        saving.value = true
        formError.value = ''
        clearFieldErrors()

        try {
            const payload = {
                product_id: Number(form.product_id || productId.value),
                color: form.color.trim(),
                storage: form.storage.trim(),
                ram: form.ram.trim(),
                sku: form.sku.trim(),
                import_price: form.import_price === '' ? null : Number(form.import_price),
                price: Number(form.price),
                sale_price: form.sale_price === '' ? null : Number(form.sale_price),
                quantity: Number(form.quantity || 0),
                status: form.status,
                is_featured: Boolean(form.is_featured),
                description: form.description.trim() || null,
            }

            if (editingVariantId.value) {
                await variantStore.update(editingVariantId.value, payload)
            } else {
                await variantStore.create(payload)
            }

            await loadProduct()
            closeModal()
        } catch (error) {
            formError.value = error.response?.data?.message || 'KhÃ´ng lÆ°u Ä‘Æ°á»£c biáº¿n thá»ƒ.'
            setFieldErrors(error.response?.data?.errors)
        } finally {
            saving.value = false
        }
    }

    const handleDelete = async (variant) => {
        if (!variant || deletingId.value) return

        if (!window.confirm(`XÃ³a biáº¿n thá»ƒ SKU "${variant.sku}"?`)) {
            return
        }

        deletingId.value = variant.id
        loadingError.value = ''

        try {
            await variantStore.remove(variant.id)
            await loadProduct()
        } catch (error) {
            loadingError.value = error.response?.data?.message || 'KhÃ´ng xÃ³a Ä‘Æ°á»£c biáº¿n thá»ƒ.'
        } finally {
            deletingId.value = null
        }
    }

    const handleToggleStatus = async (variant) => {
        const nextStatus = variant?.status === 'active' ? 'inactive' : 'active'
        const previousStatus = variant?.status

        variant.status = nextStatus
        loadingError.value = ''

        try {
            await variantStore.update(variant.id, {status: nextStatus})
        } catch (error) {
            variant.status = previousStatus
            loadingError.value = error.response?.data?.message || 'KhÃ´ng cáº­p nháº­t Ä‘Æ°á»£c tráº¡ng thÃ¡i biáº¿n thá»ƒ.'
        }
    }

    const handleToggleFeatured = async (variant) => {
        const nextFeatured = !Boolean(variant?.is_featured)
        const previousFeatured = Boolean(variant?.is_featured)

        variant.is_featured = nextFeatured
        loadingError.value = ''

        try {
            await variantStore.update(variant.id, {is_featured: nextFeatured})
        } catch (error) {
            variant.is_featured = previousFeatured
            loadingError.value = error.response?.data?.message || 'KhÃ´ng cáº­p nháº­t Ä‘Æ°á»£c tráº¡ng thÃ¡i ná»•i báº­t.'
        }
    }

    const formatMoney = (value) => {
        return formatCurrency(value) || '---'
    }

    onMounted(loadData)

    return {
        route,
        product,
        productLoading,
        loadingError,
        formError,
        showModal,
        saving,
        deletingId,
        editingVariantId,
        fieldErrors,
        productId,
        isActiveTab,
        variantRows,
        currentPage,
        pageSize,
        totalPages,
        paginatedVariants,
        pageStart,
        pageEnd,
        summary,
        form,
        openCreateModal,
        openEditModal,
        closeModal,
        handleSubmit,
        handleDelete,
        handleToggleStatus,
        handleToggleFeatured,
        formatMoney,
        formatDate,
    }
}
