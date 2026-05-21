import { useAuthStore } from '@/stores/authStore'

export function adminGuard(to, from, next) {
  const authStore = useAuthStore()

  if (!authStore.isLoggedIn) {
    return next({ name: 'login' })
  }

  if (!authStore.isAdmin) {
    return next({ name: 'forbidden' })
  }

  next()
}
