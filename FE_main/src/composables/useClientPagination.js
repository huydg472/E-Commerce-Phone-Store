import {computed, ref, watch} from 'vue'

export function useClientPagination(source, options = {}) {
    const {
        defaultPageSize = 5,
        pageSizeOptions = [5, 10],
    } = options

    const currentPage = ref(1)
    const pageSize = ref(defaultPageSize)

    const items = computed(() => (Array.isArray(source?.value) ? source.value : []))

    const totalItems = computed(() => items.value.length)
    const totalPages = computed(() => Math.max(1, Math.ceil(totalItems.value / pageSize.value)))

    const paginatedItems = computed(() => {
        const start = (currentPage.value - 1) * pageSize.value
        return items.value.slice(start, start + pageSize.value)
    })

    const pageStart = computed(() => {
        if (!totalItems.value) {
            return 0
        }

        return (currentPage.value - 1) * pageSize.value + 1
    })

    const pageEnd = computed(() => {
        if (!totalItems.value) {
            return 0
        }

        return Math.min(pageStart.value + paginatedItems.value.length - 1, totalItems.value)
    })

    const goToPage = (page) => {
        const nextPage = Math.min(Math.max(Number(page) || 1, 1), totalPages.value)
        currentPage.value = nextPage
    }

    const setPageSize = (size) => {
        const nextSize = Number(size) || defaultPageSize
        pageSize.value = pageSizeOptions.includes(nextSize) ? nextSize : defaultPageSize
        currentPage.value = 1
    }

    watch(items, () => {
        if (currentPage.value > totalPages.value) {
            currentPage.value = totalPages.value
        }
    }, {immediate: true})

    watch(pageSize, () => {
        currentPage.value = 1
    })

    return {
        currentPage,
        pageSize,
        pageSizeOptions,
        totalItems,
        totalPages,
        paginatedItems,
        pageStart,
        pageEnd,
        goToPage,
        setPageSize,
    }
}
