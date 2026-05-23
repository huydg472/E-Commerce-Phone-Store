export default [
    {
        path: '/auth',
        component: () => import('@/layouts/AuthLayout.vue'),
        meta: {requiresGuest: true},
        children: [
            {
                path: 'login',
                name: 'login',
                component: () => import('@/pages/auth/LoginPage.vue'),
            },
            {
                path: 'register',
                name: 'register',
                component: () => import('@/pages/auth/RegisterPage.vue'),
            },
            {
                path: 'forgot-password',
                name: 'forgot-password',
                component: () => import('@/pages/auth/ForgotPasswordPage.vue'),
            },
        ],
    },

    {
        path: '/auth/reset-password',
        name: 'reset-password',
        component: () => import('@/pages/auth/ResetPasswordPage.vue'),

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
