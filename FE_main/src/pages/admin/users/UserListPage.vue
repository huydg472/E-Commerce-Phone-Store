<script setup>
import {computed, onMounted, ref, watch} from 'vue'
import ListPaginationControls from '@/components/common/ListPaginationControls.vue'
import UserTable from '@/components/user/UserTable.vue'
import {useUserStore} from '@/stores/userStore'
import {userService} from '@/services/userService'
import {useNotificationStore} from '@/stores/notificationStore.js'

const userStore = useUserStore()
const notificationStore = useNotificationStore()

const search = ref('')
const currentPage = ref(1)
const pageSize = ref(5)
const loadingError = ref('')
const statsUsers = ref([])
let searchTimer = null

const users = computed(() => (Array.isArray(userStore.items) ? userStore.items : []))

const pagination = computed(() => userStore.pagination || {current_page: 1, last_page: 1, total: 0})

const stats = computed(() => {
  const source = Array.isArray(statsUsers.value) ? statsUsers.value : []
  const total = pagination.value.total || source.length || users.value.length
  const active = source.filter((user) => user?.status === 'active').length
  const inactive = source.filter((user) => user?.status !== 'active').length
  const staff = source.filter((user) => ['admin', 'staff'].includes(user?.role?.name)).length

  return {total, active, inactive, staff}
})

const pageStart = computed(() => {
  if (!users.value.length) return 0
  return (pagination.value.current_page - 1) * pageSize.value + 1
})

const pageEnd = computed(() => Math.min(pageStart.value + users.value.length - 1, pagination.value.total))

const fetchUsers = async (page = currentPage.value) => {
  loadingError.value = ''

  try {
    const response = await userStore.fetchAll({
      page,
      per_page: pageSize.value,
      q: search.value.trim() || undefined,
    })

    const meta = response.data?.meta || response.data?.data?.meta || userStore.pagination
    currentPage.value = meta?.current_page || page
  } catch (error) {
    loadingError.value = error.response?.data?.message || 'Không tải được danh sách người dùng.'
  }
}

const fetchStatsUsers = async () => {
  const accumulated = []
  let page = 1
  let lastPage = 1

  try {
    do {
      const response = await userService.getAll({page, per_page: 50})
      const responseData = response.data ?? {}
      const payload = responseData.data ?? responseData ?? null
      const pageItems = Array.isArray(payload)
          ? payload
          : (Array.isArray(payload?.data) ? payload.data : [])

      accumulated.push(...pageItems)

      const meta = (!Array.isArray(payload) && payload && (
          Object.prototype.hasOwnProperty.call(payload, 'current_page')
          || Object.prototype.hasOwnProperty.call(payload, 'last_page')
          || Object.prototype.hasOwnProperty.call(payload, 'total')
      ))
          ? payload
          : ((!Array.isArray(responseData) && responseData && (
              Object.prototype.hasOwnProperty.call(responseData, 'current_page')
              || Object.prototype.hasOwnProperty.call(responseData, 'last_page')
              || Object.prototype.hasOwnProperty.call(responseData, 'total')
          )) ? responseData : null)

      lastPage = Number(meta?.last_page) || 1
      page += 1
    } while (page <= lastPage)

    statsUsers.value = accumulated
  } catch (error) {
    statsUsers.value = users.value
  }
}

const handleSearchInput = () => {
  currentPage.value = 1

  if (searchTimer) {
    clearTimeout(searchTimer)
  }

  searchTimer = setTimeout(() => {
    fetchUsers(1)
  }, 250)
}

const handlePageSizeChange = () => {
  currentPage.value = 1
  fetchUsers(1)
}

const handlePageChange = (page) => {
  currentPage.value = page
  fetchUsers(page)
}

const handleStatusToggle = async (user) => {
  loadingError.value = ''
  const nextStatus = user.status === 'active' ? 'inactive' : 'active'
  const previousStatus = user.status
  const previousUpdatedAt = user.updated_at

  user.status = nextStatus

  try {
    const response = await userService.update(user.id, {status: nextStatus})
    const updatedUser = response.data?.data ?? response.data ?? null
    if (updatedUser?.updated_at) {
      user.updated_at = updatedUser.updated_at
    }
    await fetchStatsUsers()
    notificationStore.success('Đã sửa trạng thái.')
  } catch (error) {
    user.status = previousStatus
    user.updated_at = previousUpdatedAt
    loadingError.value = error.response?.data?.message || 'Không cập nhật được trạng thái người dùng.'
  }
}

onMounted(() => {
  Promise.all([
    fetchUsers(1),
    fetchStatsUsers(),
  ]).catch(() => {
  })
})

watch(search, () => {
  handleSearchInput()
})

watch(pageSize, () => {
  handlePageSizeChange()
})
</script>

<template>
  <div class="admin-page">
    <section class="hero-card">
      <div class="hero-copy">
        <p class="eyebrow">Quản lý người dùng</p>
        <h1>Danh sách người dùng</h1>
        <p class="subtitle">Quản lý tài khoản quản trị viên, nhân viên, khách hàng, vai trò và trạng thái hoạt động.</p>

        <div class="hero-actions">
          <RouterLink to="/admin/users/create" class="primary-action">
            <i class="bi bi-plus-lg"></i>
            Tạo người dùng
          </RouterLink>

          <button type="button" class="secondary-action" @click="search = ''">
            <i class="bi bi-arrow-counterclockwise"></i>
            Xóa tìm kiếm
          </button>
        </div>
      </div>

      <div class="hero-stats">
        <article class="stat-card">
          <span class="stat-icon stat-icon-total">
            <i class="bi bi-people"></i>
          </span>
          <div>
            <strong>{{ stats.total }}</strong>
            <span>Tổng người dùng</span>
          </div>
        </article>

        <article class="stat-card">
          <span class="stat-icon stat-icon-active">
            <i class="bi bi-check2-circle"></i>
          </span>
          <div>
            <strong>{{ stats.active }}</strong>
            <span>Đang hoạt động</span>
          </div>
        </article>

        <article class="stat-card">
          <span class="stat-icon stat-icon-featured">
            <i class="bi bi-person-gear"></i>
          </span>
          <div>
            <strong>{{ stats.staff }}</strong>
            <span>Admin / Staff</span>
          </div>
        </article>

        <article class="stat-card">
          <span class="stat-icon stat-icon-inactive">
            <i class="bi bi-slash-circle"></i>
          </span>
          <div>
            <strong>{{ stats.inactive }}</strong>
            <span>Tạm ẩn</span>
          </div>
        </article>
      </div>
    </section>

    <div class="toolbar">
      <div class="search-box">
        <i class="bi bi-search"></i>
        <input v-model.trim="search" type="search" placeholder="Tìm theo tên, username, email, vai trò..."/>
      </div>
    </div>

    <div v-if="userStore.loading" class="state-card">
      <div class="spinner-border text-primary" role="status"></div>
      <p>Đang tải danh sách người dùng...</p>
    </div>

    <div v-else-if="loadingError" class="state-card error-state">
      <i class="bi bi-exclamation-triangle"></i>
      <p>{{ loadingError }}</p>
      <button type="button" class="secondary-action" @click="fetchUsers(currentPage)">Thử lại</button>
    </div>

    <UserTable v-else :users="users" :loading="userStore.loading" @toggle-status="handleStatusToggle"/>

    <ListPaginationControls
        v-if="!userStore.loading && !loadingError"
        :current-page="currentPage"
        :total-pages="pagination.last_page"
        :page-size="pageSize"
        :total-items="pagination.total"
        :page-start="pageStart"
        :page-end="pageEnd"
        item-label="người dùng"
        @update:currentPage="handlePageChange"
        @update:pageSize="pageSize = $event"
    />
  </div>
</template>

<style scoped>
.admin-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.hero-card {
  padding: 24px;
  border: 1px solid #e5eaf3;
  border-radius: 20px;
  background: radial-gradient(circle at top right, rgba(37, 99, 235, 0.16), transparent 30%),
  linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
  display: grid;
  grid-template-columns: minmax(0, 1.3fr) minmax(320px, 0.9fr);
  gap: 18px;
}

.eyebrow {
  margin: 0 0 6px;
  color: #2563eb;
  font-size: 13px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.hero-copy h1 {
  margin: 0;
  color: #0f172a;
  font-size: 32px;
  font-weight: 900;
  line-height: 1.1;
}

.subtitle {
  max-width: 760px;
  margin: 8px 0 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.7;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 18px;
}

.primary-action,
.secondary-action {
  min-height: 44px;
  padding: 0 16px;
  border-radius: 12px;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  text-decoration: none;
  font-size: 14px;
  font-weight: 800;
  border: 1px solid transparent;
}

.primary-action {
  color: #ffffff;
  background: #2563eb;
}

.secondary-action {
  color: #334155;
  background: #ffffff;
  border-color: #dbe3ef;
}

.hero-stats {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.hero-stats .stat-card {
  width: 100%;
  height: 100%;
  min-height: 96px;
  padding: 16px;
  display: flex;
  align-items: center;
  gap: 14px;
  border: 1px solid #edf2f7;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.92);
}

.hero-stats .stat-card strong {
  display: block;
  margin: 0;
  color: #020617;
  font-size: 24px;
  font-weight: 900;
  line-height: 1;
}

.hero-stats .stat-card span:last-child {
  display: block;
  margin-top: 6px;
  color: #64748b;
  font-size: 13px;
  font-weight: 700;
  line-height: 1.3;
}

.hero-stats .stat-icon {
  width: 44px;
  height: 44px;
  display: grid;
  place-items: center;
  flex: 0 0 auto;
  border-radius: 16px;
  color: #ffffff;
  font-size: 18px;
}

.stat-icon-total {
  background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
}

.stat-icon-active {
  background: linear-gradient(135deg, #10b981 0%, #22c55e 100%);
}

.stat-icon-featured {
  background: linear-gradient(135deg, #f59e0b 0%, #f97316 100%);
}

.stat-icon-inactive {
  background: linear-gradient(135deg, #64748b 0%, #475569 100%);
}

.toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 0;
  max-width: 420px;
  height: 46px;
  padding: 0 14px;
  border: 1px solid #dbe3ef;
  border-radius: 14px;
  background: #ffffff;
  display: flex;
  align-items: center;
  gap: 10px;
}

.search-box input {
  width: 100%;
  border: none;
  outline: none;
  background: transparent;
  color: #0f172a;
  font-size: 14px;
  font-weight: 500;
  line-height: 1.2;
}


.state-card {
  min-height: 240px;
  border: 1px solid #e5eaf3;
  border-radius: 16px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 14px;
  color: #475569;
}

.state-card.error-state {
  color: #dc2626;
}

.table-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
  padding-top: 4px;
}

.table-summary {
  color: #64748b;
  font-size: 14px;
}

@media (max-width: 1199.98px) {
  .hero-card {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 767.98px) {
  .hero-card {
    padding: 20px;
  }

  .hero-copy h1 {
    font-size: 24px;
  }

  .hero-actions {
    flex-direction: column;
  }

  .hero-stats {
    grid-template-columns: 1fr;
  }

  .search-box,
  .primary-action,
  .secondary-action {
    width: 100%;
    max-width: none;
  }
}
</style>
