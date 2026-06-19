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
                path: 'tin-tuc/chu-de/:slug?',
                name: 'news.topics',
                component: () => import('@/pages/client/NewsTopicPage.vue'),
            },
            {
                path: 'tin-tuc/:slug',
                name: 'news.show',
                component: () => import('@/pages/client/NewsDetailPage.vue'),
            },

            {
                path: 'lien-he',
                name: 'contact',
                alias: '/ho-tro/faq',
                component: () => import('@/pages/client/ContactPage.vue')
            },
            {
                path: 'gioi-thieu',
                name: 'about',
                component: () => import('@/pages/client/StaticInfoPage.vue'),
                props: {pageKey: 'about'},
            },
            {
                path: 'bao-hanh',
                name: 'warranty',
                component: () => import('@/pages/client/StaticInfoPage.vue'),
                props: {pageKey: 'warranty'},
            },
            {
                path: 'doi-tra',
                name: 'returns',
                component: () => import('@/pages/client/StaticInfoPage.vue'),
                props: {pageKey: 'returns'},
            },
            {
                path: 'bao-mat',
                name: 'privacy',
                component: () => import('@/pages/client/StaticInfoPage.vue'),
                props: {pageKey: 'privacy'},
            },
            {
                path: 'huong-dan-mua-hang',
                name: 'buy-guide',
                component: () => import('@/pages/client/StaticInfoPage.vue'),
                props: {pageKey: 'buyGuide'},
            },
            {
                path: 'huong-dan-thanh-toan',
                name: 'payment-guide',
                component: () => import('@/pages/client/StaticInfoPage.vue'),
                props: {pageKey: 'paymentGuide'},
            },
            {
                path: 'cau-hoi-thuong-gap',
                name: 'faq',
                component: () => import('@/pages/client/StaticInfoPage.vue'),
                props: {pageKey: 'faq'},
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
                component: () => import('@/pages/client/AccessoriesPage.vue'),
            },
            {
                path: 'cart',
                name: 'cart',
                component: () => import('@/pages/client/CartPage.vue'),
                meta: {requiresAuth: true},
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
                path: 'demo-thanh-toan',
                name: 'payment.demo',
                component: () => import('@/pages/client/PaymentGatewayDemoPage.vue'),
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
                        path: 'yeu-thich',
                        name: 'favorites',
                        component: () => import('@/pages/client/FavoritePage.vue'),
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
