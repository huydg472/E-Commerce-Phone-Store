export function formatCurrency(value) {
    if (value === null || value === undefined || value === '') {
        return ''
    }

    const numericValue = Number(value)

    if (Number.isNaN(numericValue)) {
        return ''
    }

    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0,
    }).format(numericValue)
}
