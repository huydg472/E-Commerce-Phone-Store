import {createRouter, createWebHistory} from 'vue-router'
import clientRoutes from './client.routes'
import authRoutes from './auth.routes'
import adminRoutes from './admin.routes'
import {authGuard} from '@/middleware/auth'
import {guestGuard} from '@/middleware/guest'
import {adminGuard} from '@/middleware/admin'
import {useAuthStore} from '@/stores/authStore'
import {useSettingsStore} from '@/stores/settingsStore'
import MaintenancePage from '@/pages/error/MaintenancePage.vue'

const routes = [
    {
        path: '/bao-tri',
        name: 'maintenance',
        component: MaintenancePage,
        meta: {allowDuringMaintenance: true},
    },
    ...clientRoutes,
    ...authRoutes,
    ...adminRoutes,
    {
        path: '/403',
        name: 'forbidden',
        component: () => import('@/pages/error/ForbiddenPage.vue'),
    },
    {
        path: '/500',
        name: 'server-error',
        component: () => import('@/pages/error/ServerErrorPage.vue'),
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: () => import('@/pages/error/NotFoundPage.vue'),
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return {top: 0}
    },
})

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()
    const settingsStore = useSettingsStore()

    if (to.meta['requiresAdmin']) {
        return adminGuard(to, from, next)
    }

    if (!settingsStore.loaded && !settingsStore.loading) {
        try {
            await settingsStore.fetchPublic()
        } catch (error) {
          // If settings cannot be loaded, fall back to normal navigation.
        }
    }

    const maintenanceMode = Boolean(settingsStore.settings?.maintenance_mode)
    const isAdminBypass = authStore.isAdminOrStaff
    const allowDuringMaintenance = Boolean(to.meta?.allowDuringMaintenance)

    if (maintenanceMode && !isAdminBypass && !allowDuringMaintenance && to.name !== 'maintenance') {
        return next({name: 'maintenance'})
    }

    if (to.meta['requiresAuth']) {
        return authGuard(to, from, next)
    }

    if (to.meta['requiresGuest']) {
        return guestGuard(to, from, next)
    }

    next()
})
export default router
