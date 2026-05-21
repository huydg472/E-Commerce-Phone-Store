export function formatCurrency(value) {
  if (value === null || value === undefined || value === '') {
    return '0 ₫'
  }

  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  }).format(Number(value))
}
