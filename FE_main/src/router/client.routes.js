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
                component: () => import('@/pages/client/ContactPage.vue')
            },

            {
                path: 'products',
                name: 'products.index',
                component: () => import('@/pages/client/ProductListPage.vue'),
            },
            {
                path: 'products/:slug',
                name: 'products.show',
                component: () => import('@/pages/client/ProductDetailPage.vue'),
            },
            {
                path: 'cart',
                name: 'cart',
                component: () => import('@/pages/client/CartPage.vue'),
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
                path: 'orders',
                name: 'orders.history',
                component: () => import('@/pages/client/OrderHistoryPage.vue'),
                meta: {requiresAuth: true},
            },
            {
                path: 'orders/:id',
                name: 'orders.show',
                component: () => import('@/pages/client/OrderDetailPage.vue'),
                meta: {requiresAuth: true},
            },
            {
                path: 'profile',
                name: 'profile',
                component: () => import('@/pages/client/ProfilePage.vue'),
                meta: {requiresAuth: true},
            },
            {
                path: 'change-password',
                name: 'change-password',
                component: () => import('@/pages/client/ChangePasswordPage.vue'),
                meta: {requiresAuth: true},
            },
            {
                path: 'shipping-addresses',
                name: 'shipping-addresses',
                component: () => import('@/pages/client/ShippingAddressPage.vue'),
                meta: {requiresAuth: true},
            },
        ],
    },
]
