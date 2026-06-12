import {useAuthStore} from '@/stores/authStore'

export function adminGuard(to, from, next) {
    const authStore = useAuthStore()

    if (!authStore.isLoggedIn) {
        return next({name: 'login'})
    }

    const hasRolePermissions = Array.isArray(authStore.user?.role?.permissions)

    const verify = async () => {
        if (!hasRolePermissions) {
            try {
                await authStore.fetchMe()
            } catch (error) {
                if (error.response?.status === 401) {
                    authStore.clearSession()
                    return next({name: 'login'})
                }

                return next({name: 'forbidden'})
            }
        }

        if (!authStore.isAdminOrStaff) {
            return next({name: 'forbidden'})
        }

        const requiredPermission = to.meta?.permission
        if (requiredPermission && !authStore.can(requiredPermission)) {
            return next({name: 'forbidden'})
        }

        return next()
    }

    return verify().catch(() => next({name: 'forbidden'}))
}
