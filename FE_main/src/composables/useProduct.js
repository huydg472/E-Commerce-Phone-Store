import {useProductStore} from '@/stores/productStore'

export function useProduct() {
    const productStore = useProductStore()

    return {
        productStore,
    }
}
