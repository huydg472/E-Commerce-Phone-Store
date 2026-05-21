export function formatPhone(value) {
  if (!value) return ''

  const phone = String(value).replace(/\D/g, '')

  if (phone.length === 10) {
    return phone.replace(/(\d{4})(\d{3})(\d{3})/, '$1 $2 $3')
  }

  return value
}
