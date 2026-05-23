export function required(value) {
    return value !== null && value !== undefined && String(value).trim() !== ''
}

export function isEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
}

export function minLength(value, length) {
    return String(value || '').length >= length
}
