export const unwrapList = (response) => {
    const payload = response?.data?.data ?? response?.data ?? []
    return Array.isArray(payload) ? payload : (payload?.data ?? [])
}

export const unwrapPaginated = (response) => {
    const payload = response?.data?.data ?? response?.data ?? {}
    const items = Array.isArray(payload) ? payload : (payload?.data ?? [])

    return {
        items,
        total: payload?.total ?? response?.data?.total ?? items.length,
    }
}

export const toNumber = (value) => {
    const numericValue = Number(value)
    return Number.isFinite(numericValue) ? numericValue : 0
}

export const formatMoney = (value) => {
    return `${new Intl.NumberFormat('vi-VN', {maximumFractionDigits: 0}).format(Math.round(toNumber(value)))} đ`
}

export const toDate = (value) => {
    if (!value) {
        return null
    }

    const date = new Date(value)
    return Number.isNaN(date.getTime()) ? null : date
}

export const formatMonthKey = (value) => {
    const date = toDate(value)
    if (!date) {
        return ''
    }

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    return `${year}-${month}`
}

export const formatDateKey = (value) => {
    const date = toDate(value)
    if (!date) {
        return ''
    }

    const day = String(date.getDate()).padStart(2, '0')
    const month = String(date.getMonth() + 1).padStart(2, '0')
    return `${day}/${month}`
}

export const formatMonthLabel = (value) => {
    const date = toDate(value)
    if (!date) {
        return ''
    }

    return new Intl.DateTimeFormat('vi-VN', {
        month: '2-digit',
        year: 'numeric',
    }).format(date)
}

export const isInLastDays = (value, days) => {
    const date = toDate(value)
    if (!date) {
        return false
    }

    const now = new Date()
    const start = new Date(now)
    start.setHours(0, 0, 0, 0)
    start.setDate(start.getDate() - Math.max(days - 1, 0))

    return date >= start
}

