<script setup>
defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
})

defineEmits(['close'])
</script>

<template>
  <Teleport to="body">
    <div v-if="show" class="modal-backdrop">
      <div class="modal-card">
        <div class="modal-header">
          <h3>{{ title }}</h3>
          <button type="button" @click="$emit('close')">×</button>
        </div>

        <div class="modal-body">
          <slot/>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 1000;
  display: grid;
  place-items: center;
  background: rgba(15, 23, 42, 0.45);
}

.modal-card {
  width: min(560px, calc(100% - 32px));
  max-height: calc(100vh - 32px);
  overflow: auto;
  background: #fff;
  border-radius: 14px;
  padding: 18px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
</style>
