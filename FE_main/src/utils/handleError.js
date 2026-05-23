export function getErrorMessage(error) {
    return error?.response?.data?.message || error?.message || 'Có lỗi xảy ra'
}

export function getValidationErrors(error) {
    return error?.response?.data?.errors || {}
}
