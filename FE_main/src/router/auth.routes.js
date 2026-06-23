export default [
    {
        path: '/auth',
        component: () => import('@/layouts/AuthLayout.vue'),
        meta: {requiresGuest: true, allowDuringMaintenance: true},
        children: [
            {
                path: 'login',
                name: 'login',
                component: () => import('@/pages/auth/LoginPage.vue'),
                meta: {allowDuringMaintenance: true},
            },
            {
                path: 'register',
                name: 'register',
                component: () => import('@/pages/auth/RegisterPage.vue'),
                meta: {allowDuringMaintenance: true},
            },
            {
                path: 'forgot-password',
                name: 'forgot-password',
                component: () => import('@/pages/auth/ForgotPasswordPage.vue'),
                meta: {allowDuringMaintenance: true},
            },
        ],
    },

    {
        path: '/auth/reset-password',
        name: 'reset-password',
        component: () => import('@/pages/auth/ResetPasswordPage.vue'),
        meta: {allowDuringMaintenance: true},

        // Không có token hoặc email thì không cho vào trang đổi mật khẩu
        beforeEnter: (to) => {
            if (!to.query.token || !to.query.email) {
                return {name: 'forgot-password'}
            }

            return true
        },
    },

    {
        path: '/auth/reset-password-success',
        name: 'reset-password-success',
        component: () => import('@/pages/auth/ResetPasswordSuccessPage.vue'),
        meta: {allowDuringMaintenance: true},

        // Không reset thành công thì không cho tự gọi trang success
        beforeEnter: () => {
            const isSuccess = sessionStorage.getItem('reset_password_success')

            if (isSuccess !== 'true') {
                return {name: 'forgot-password'}
            }

            return true
        },
    },
]
