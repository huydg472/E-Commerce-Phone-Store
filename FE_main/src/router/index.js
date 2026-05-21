import { createRouter, createWebHistory } from 'vue-router'
import clientRoutes from './client.routes'
import authRoutes from './auth.routes'
import adminRoutes from './admin.routes'
import { authGuard } from '@/middleware/auth'
import { guestGuard } from '@/middleware/guest'
import { adminGuard } from '@/middleware/admin'

const routes = [
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
    return { top: 0 }
  },
})

router.beforeEach((to, from, next) => {
  if (to.meta.requiresAdmin) {
    return adminGuard(to, from, next)
  }

  if (to.meta.requiresAuth) {
    return authGuard(to, from, next)
  }

  if (to.meta.requiresGuest) {
    return guestGuard(to, from, next)
  }

  next()
})

export default router
