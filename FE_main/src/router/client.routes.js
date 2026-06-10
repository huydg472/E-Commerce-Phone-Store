export default [
    {
        path: '/',
        component: () => import('@/layouts/DefaultLayout.vue'),
        children: [
            {
                path: '',
                name: 'home',
                component: () => import('@/pages/client/HomePage.vue'),
            },
            {
                path: 'tin-tuc',
                name: 'news',
                component: () => import('@/pages/client/NewsPage.vue'),
            },

            {
                path: 'lien-he',
                name: 'contact',
                alias: '/ho-tro/faq',
                component: () => import('@/pages/client/ContactPage.vue')
            },

            {
                path: 'products',
                name: 'products.index',
                alias: '/san-pham',
                component: () => import('@/pages/client/ProductListPage.vue'),
            },
            {
                path: 'products/:slug',
                name: 'products.show',
                component: () => import('@/pages/client/ProductDetailPage.vue'),
            },
            {
                path: 'phu-kien',
                name: 'products.accessories',
                component: () => import('@/pages/client/ProductListPage.vue'),
            },
            {
                path: 'cart',
                name: 'cart',
                component: () => import('@/pages/client/CartPage.vue'),
            },
            {
                path: 'dashboard',
                name: 'dashboard',
                component: () => import('@/pages/client/DashboardPage.vue'),
            },
            {
                path: 'checkout',
                name: 'checkout',
                component: () => import('@/pages/client/CheckoutPage.vue'),
                meta: {requiresAuth: true},
            },
            {
                path: 'order-success',
                name: 'order.success',
                component: () => import('@/pages/client/OrderSuccessPage.vue'),
                meta: {requiresAuth: true},
            },
            {
                path: 'tai-khoan',
                component: () => import('@/layouts/AccountLayout.vue'),
                meta: {requiresAuth: true},
                children: [
                    {
                        path: '',
                        redirect: {name: 'profile'},
                    },
                    {
                        path: 'tong-quan',
                        name: 'profile',
                        alias: '/profile',
                        component: () => import('@/pages/client/ProfilePage.vue'),
                    },
                    {
                        path: 'thong-tin-ca-nhan',
                        name: 'profile.edit',
                        alias: '/profile/edit',
                        component: () => import('@/pages/client/PersonalInfoPage.vue'),
                    },
                    {
                        path: 'don-hang',
                        name: 'orders.history',
                        alias: '/orders',
                        component: () => import('@/pages/client/OrderHistoryPage.vue'),
                    },
                    {
                        path: 'don-hang/:id',
                        name: 'orders.show',
                        alias: '/orders/:id',
                        component: () => import('@/pages/client/OrderDetailPage.vue'),
                    },
                    {
                        path: 'doi-mat-khau',
                        name: 'change-password',
                        component: () => import('@/pages/client/ChangePasswordPage.vue'),
                    },
                    {
                        path: 'so-dia-chi',
                        name: 'shipping-addresses',
                        component: () => import('@/pages/client/ShippingAddressPage.vue'),
                    },
                ],
            },
        ],
    },
]
