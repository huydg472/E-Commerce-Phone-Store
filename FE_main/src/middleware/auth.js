import { useAuthStore } from '@/stores/authStore'

export function authGuard(to, from, next) {
  const authStore = useAuthStore()

  if (!authStore.isLoggedIn) {
    return next({ name: 'login' })
  }

  next()
}
