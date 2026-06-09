<script setup>
import { computed } from 'vue'

const props = defineProps({
  permissions: {
    type: Array,
    default: () => [],
  },
  modelValue: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['update:modelValue'])

const groupedPermissions = computed(() => {
  const groups = new Map()

  props.permissions.forEach((permission) => {
    const moduleName = permission.module || 'Khác'
    if (!groups.has(moduleName)) {
      groups.set(moduleName, [])
    }
    groups.get(moduleName).push(permission)
  })

  return Array.from(groups.entries()).map(([module, items]) => ({
    module,
    items: items.sort((a, b) => {
      const actionA = a.action || ''
      const actionB = b.action || ''
      return actionA.localeCompare(actionB)
    }),
  }))
})

const checkedIds = computed({
  get: () => props.modelValue || [],
  set: (value) => emit('update:modelValue', value),
})

const togglePermission = (permissionId) => {
  const next = new Set(checkedIds.value)
  if (next.has(permissionId)) {
    next.delete(permissionId)
  } else {
    next.add(permissionId)
  }
  checkedIds.value = Array.from(next)
}

const permissionLabel = (permission) => {
  return permission.display_name || permission.name || `${permission.module}.${permission.action}`
}
</script>

<template>
  <div class="permission-group">
    <div v-if="!groupedPermissions.length" class="permission-empty">
      Chưa có dữ liệu quyền.
    </div>

    <div v-for="group in groupedPermissions" :key="group.module" class="permission-module">
      <div class="module-head">
        <h4>{{ group.module }}</h4>
        <span>{{ group.items.length }} quyền</span>
      </div>

      <div class="permission-list">
        <label v-for="permission in group.items" :key="permission.id" class="permission-item">
          <input
            :checked="checkedIds.includes(permission.id)"
            type="checkbox"
            @change="togglePermission(permission.id)"
          />
          <div>
            <strong>{{ permissionLabel(permission) }}</strong>
            <small>{{ permission.action }}</small>
          </div>
        </label>
      </div>
    </div>
  </div>
</template>

<style scoped>
.permission-group {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.permission-module {
  padding: 14px;
  border: 1px solid #e5eaf3;
  border-radius: 14px;
  background: #fafcff;
}

.module-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
}

.module-head h4 {
  margin: 0;
  color: #0f172a;
  font-size: 15px;
  font-weight: 850;
}

.module-head span {
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
}

.permission-list {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.permission-item {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  padding: 10px 12px;
  border: 1px solid #dbe3ef;
  border-radius: 12px;
  background: #ffffff;
  cursor: pointer;
}

.permission-item input {
  margin-top: 2px;
}

.permission-item strong {
  display: block;
  color: #0f172a;
  font-size: 13px;
  font-weight: 800;
}

.permission-item small {
  display: block;
  color: #64748b;
  font-size: 12px;
}

.permission-empty {
  padding: 14px;
  border: 1px dashed #dbe3ef;
  border-radius: 12px;
  color: #64748b;
  background: #fff;
}

@media (max-width: 768px) {
  .permission-list {
    grid-template-columns: 1fr;
  }
}
</style>
