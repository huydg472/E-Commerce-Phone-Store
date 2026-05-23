import {useAuthStore} from '@/stores/authStore'

export function guestGuard(to, from, next) {
    const authStore = useAuthStore()

    if (authStore.isLoggedIn) {
        return next({name: 'home'})
    }

    next()
}
