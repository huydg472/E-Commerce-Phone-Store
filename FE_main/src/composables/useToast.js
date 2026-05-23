export function useToast() {
    function success(message) {
        alert(message)
    }

    function error(message) {
        alert(message)
    }

    return {
        success,
        error,
    }
}
