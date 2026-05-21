import { useCartStore } from '@/stores/cartStore'

export function useCart() {
  const cartStore = useCartStore()

  return {
    cartStore,
  }
}
