import {useOrderStore} from '@/stores/orderStore'

export function useOrder() {
    const orderStore = useOrderStore()

    return {
        orderStore,
    }
}
