export default [
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAdmin: true },
    children: [
      {
        path: '',
        redirect: { name: 'admin.dashboard' },
      },
      {
        path: 'dashboard',
        name: 'admin.dashboard',
        component: () => import('@/pages/admin/DashboardPage.vue'),
      },

      {
        path: 'products',
        name: 'admin.products.index',
        component: () => import('@/pages/admin/products/ProductListPage.vue'),
      },
      {
        path: 'products/create',
        name: 'admin.products.create',
        component: () => import('@/pages/admin/products/ProductCreatePage.vue'),
      },
      {
        path: 'products/:id',
        name: 'admin.products.show',
        component: () => import('@/pages/admin/products/ProductDetailPage.vue'),
      },
      {
        path: 'products/:id/edit',
        name: 'admin.products.edit',
        component: () => import('@/pages/admin/products/ProductEditPage.vue'),
      },
      {
        path: 'products/:id/variants',
        name: 'admin.products.variants',
        component: () => import('@/pages/admin/products/ProductVariantPage.vue'),
      },
      {
        path: 'products/:id/specifications',
        name: 'admin.products.specifications',
        component: () => import('@/pages/admin/products/ProductSpecificationPage.vue'),
      },
      {
        path: 'products/:id/images',
        name: 'admin.products.images',
        component: () => import('@/pages/admin/products/ProductImagePage.vue'),
      },

      {
        path: 'categories',
        name: 'admin.categories',
        component: () => import('@/pages/admin/categories/CategoryPage.vue'),
      },
      {
        path: 'brands',
        name: 'admin.brands',
        component: () => import('@/pages/admin/brands/BrandPage.vue'),
      },

      {
        path: 'orders',
        name: 'admin.orders.index',
        component: () => import('@/pages/admin/orders/OrderListPage.vue'),
      },
      {
        path: 'orders/:id',
        name: 'admin.orders.show',
        component: () => import('@/pages/admin/orders/OrderDetailPage.vue'),
      },

      {
        path: 'payments',
        name: 'admin.payments.index',
        component: () => import('@/pages/admin/payments/PaymentListPage.vue'),
      },
      {
        path: 'payments/:id',
        name: 'admin.payments.show',
        component: () => import('@/pages/admin/payments/PaymentDetailPage.vue'),
      },

      {
        path: 'users',
        name: 'admin.users.index',
        component: () => import('@/pages/admin/users/UserListPage.vue'),
      },
      {
        path: 'users/create',
        name: 'admin.users.create',
        component: () => import('@/pages/admin/users/UserCreatePage.vue'),
      },
      {
        path: 'users/:id',
        name: 'admin.users.show',
        component: () => import('@/pages/admin/users/UserDetailPage.vue'),
      },
      {
        path: 'users/:id/edit',
        name: 'admin.users.edit',
        component: () => import('@/pages/admin/users/UserEditPage.vue'),
      },

      {
        path: 'roles',
        name: 'admin.roles',
        component: () => import('@/pages/admin/roles/RolePage.vue'),
      },
      {
        path: 'permissions',
        name: 'admin.permissions',
        component: () => import('@/pages/admin/permissions/PermissionPage.vue'),
      },
      {
        path: 'stock-logs',
        name: 'admin.stock-logs',
        component: () => import('@/pages/admin/stock-logs/StockLogPage.vue'),
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
      },
    ],
  },
]
