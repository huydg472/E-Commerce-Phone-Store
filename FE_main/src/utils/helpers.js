export function debounce(callback, delay = 400) {
    let timeoutId = null

    return (...args) => {
        clearTimeout(timeoutId)

        timeoutId = setTimeout(() => {
            callback(...args)
        }, delay)
    }
}

export function cloneObject(data) {
    return JSON.parse(JSON.stringify(data))
}
