import {PERMISSIONS} from '@/constants/permissions'

export default [
    {
        path: '/admin',
        component: () => import('@/layouts/AdminLayout.vue'),
        meta: {requiresAdmin: true},
        children: [
            {
                path: '',
                redirect: {name: 'admin.dashboard'},
            },
            {
                path: 'dashboard',
                name: 'admin.dashboard',
                component: () => import('@/pages/admin/DashboardPage.vue'),
            },
            {
                path: 'account',
                name: 'admin.account',
                component: () => import('@/pages/admin/account/AccountPage.vue'),
            },
            {
                path: 'account/change-password',
                name: 'admin.account.change-password',
                component: () => import('@/pages/admin/account/ChangePasswordPage.vue'),
            },
            {
                path: 'account/edit',
                name: 'admin.account.edit',
                component: () => import('@/pages/admin/account/AccountEditPage.vue'),
            },

            {
                path: 'products',
                name: 'admin.products.index',
                component: () => import('@/pages/admin/products/ProductListPage.vue'),
                meta: {permission: PERMISSIONS.PRODUCTS.VIEW},
            },
            {
                path: 'products/create',
                name: 'admin.products.create',
                component: () => import('@/pages/admin/products/ProductCreatePage.vue'),
                meta: {permission: PERMISSIONS.PRODUCTS.CREATE},
            },
            {
                path: 'products/:id',
                name: 'admin.products.show',
                component: () => import('@/pages/admin/products/ProductDetailPage.vue'),
                meta: {permission: PERMISSIONS.PRODUCTS.VIEW},
            },
            {
                path: 'products/:id/edit',
                name: 'admin.products.edit',
                component: () => import('@/pages/admin/products/ProductEditPage.vue'),
                meta: {permission: PERMISSIONS.PRODUCTS.UPDATE},
            },
            {
                path: 'products/:id/variants',
                name: 'admin.products.variants',
                component: () => import('@/pages/admin/products/ProductVariantPage.vue'),
                meta: {permission: PERMISSIONS.PRODUCT_VARIANTS.VIEW},
            },
            {
                path: 'products/:id/specifications',
                name: 'admin.products.specifications',
                component: () => import('@/pages/admin/products/ProductSpecificationPage.vue'),
                meta: {permission: PERMISSIONS.PRODUCT_SPECIFICATIONS.VIEW},
            },
            {
                path: 'products/:id/images',
                name: 'admin.products.images',
                component: () => import('@/pages/admin/products/ProductImagePage.vue'),
                meta: {permission: PERMISSIONS.PRODUCT_VARIANT_IMAGES.VIEW},
            },

            {
                path: 'categories',
                name: 'admin.categories',
                component: () => import('@/pages/admin/categories/CategoryPage.vue'),
                meta: {permission: PERMISSIONS.CATEGORIES.VIEW},
            },
            {
                path: 'brands',
                name: 'admin.brands',
                component: () => import('@/pages/admin/brands/BrandPage.vue'),
                meta: {permission: PERMISSIONS.BRANDS.VIEW},
            },

            {
                path: 'orders',
                name: 'admin.orders.index',
                component: () => import('@/pages/admin/orders/OrderListPage.vue'),
                meta: {permission: PERMISSIONS.ORDERS.VIEW},
            },
            {
                path: 'orders/:id',
                name: 'admin.orders.show',
                component: () => import('@/pages/admin/orders/OrderDetailPage.vue'),
                meta: {permission: PERMISSIONS.ORDERS.VIEW},
            },

            {
                path: 'payments',
                name: 'admin.payments.index',
                component: () => import('@/pages/admin/payments/PaymentListPage.vue'),
                meta: {permission: PERMISSIONS.PAYMENTS.VIEW},
            },
            {
                path: 'payments/:id',
                name: 'admin.payments.show',
                component: () => import('@/pages/admin/payments/PaymentDetailPage.vue'),
                meta: {permission: PERMISSIONS.PAYMENTS.VIEW},
            },
            {
                path: 'coupons',
                name: 'admin.coupons.index',
                component: () => import('@/pages/admin/coupons/CouponPage.vue'),
                meta: {permission: PERMISSIONS.COUPONS.VIEW},
            },
            {
                path: 'news/posts',
                name: 'admin.news.posts',
                component: () => import('@/pages/admin/news/NewsPostPage.vue'),
                meta: {permission: PERMISSIONS.NEWS_POSTS.VIEW},
            },
            {
                path: 'news/categories',
                name: 'admin.news.categories',
                component: () => import('@/pages/admin/news/NewsCategoryPage.vue'),
                meta: {permission: PERMISSIONS.NEWS_CATEGORIES.VIEW},
            },

            {
                path: 'users',
                name: 'admin.users.index',
                component: () => import('@/pages/admin/users/UserListPage.vue'),
                meta: {permission: PERMISSIONS.USERS.VIEW},
            },
            {
                path: 'users/create',
                name: 'admin.users.create',
                component: () => import('@/pages/admin/users/UserCreatePage.vue'),
                meta: {permission: PERMISSIONS.USERS.CREATE},
            },
            {
                path: 'users/:id',
                name: 'admin.users.show',
                component: () => import('@/pages/admin/users/UserDetailPage.vue'),
                meta: {permission: PERMISSIONS.USERS.VIEW},
            },
            {
                path: 'users/:id/edit',
                name: 'admin.users.edit',
                component: () => import('@/pages/admin/users/UserEditPage.vue'),
                meta: {permission: PERMISSIONS.USERS.UPDATE},
            },

            {
                path: 'roles',
                name: 'admin.roles',
                component: () => import('@/pages/admin/roles/RolePage.vue'),
                meta: {permission: PERMISSIONS.ROLES.VIEW},
            },
            {
                path: 'permissions',
                name: 'admin.permissions',
                component: () => import('@/pages/admin/permissions/PermissionPage.vue'),
                meta: {permission: PERMISSIONS.PERMISSION.VIEW},
            },
            {
                path: 'stock-logs',
                name: 'admin.stock-logs',
                component: () => import('@/pages/admin/stock-logs/StockLogPage.vue'),
                meta: {permission: PERMISSIONS.STOCK_LOGS.VIEW},
            },

            {
                path: 'reports/revenue',
                name: 'admin.reports.revenue',
                component: () => import('@/pages/admin/reports/RevenueReportPage.vue'),
            },
            {
                path: 'reports/products',
                name: 'admin.reports.products',
                component: () => import('@/pages/admin/reports/ProductReportPage.vue'),
            },
            {
                path: 'reports/orders',
                name: 'admin.reports.orders',
                component: () => import('@/pages/admin/reports/OrderReportPage.vue'),
            },
            {
                path: 'settings',
                name: 'admin.settings',
                component: () => import('@/pages/admin/settings/SettingPage.vue'),
                meta: {permission: PERMISSIONS.SETTINGS.VIEW},
            },
        ],
    },
]
