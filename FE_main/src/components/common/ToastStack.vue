<script setup>
import {storeToRefs} from 'pinia'
import {useNotificationStore} from '@/stores/notificationStore.js'

const notificationStore = useNotificationStore()
const {items} = storeToRefs(notificationStore)

const iconByType = {
  success: 'bi-check-circle-fill',
  error: 'bi-x-circle-fill',
  info: 'bi-info-circle-fill',
  favorite: 'bi-heart-fill',
}

const close = (id) => {
  notificationStore.remove(id)
}
</script>

<template>
  <teleport to="body">
    <div class="toast-stack" aria-live="polite" aria-atomic="true">
      <TransitionGroup name="toast-fade" tag="div" class="toast-stack__list">
        <article
            v-for="item in items"
            :key="item.id"
            class="toast-card"
            :class="`toast-card--${item.type}`"
        >
          <div class="toast-card__icon">
            <i :class="`bi ${iconByType[item.type] || iconByType.info}`"></i>
          </div>
          <div class="toast-card__content">
            <strong>{{ item.title }}</strong>
            <p v-if="item.message">{{ item.message }}</p>
          </div>
          <button type="button" class="toast-card__close" @click="close(item.id)" aria-label="Đóng thông báo">
            <i class="bi bi-x-lg"></i>
          </button>
        </article>
      </TransitionGroup>
    </div>
  </teleport>
</template>

<style scoped>
.toast-stack {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  pointer-events: none;
}

.toast-stack__list {
  display: flex;
  flex-direction: column;
  gap: 12px;
  align-items: flex-end;
}

.toast-card {
  pointer-events: auto;
  display: flex;
  align-items: flex-start;
  gap: 12px;
  min-width: 360px;
  max-width: min(520px, calc(100vw - 32px));
  padding: 16px 18px;
  border-radius: 18px;
  border: 1px solid rgba(148, 163, 184, 0.24);
  background: rgba(255, 255, 255, 0.99);
  box-shadow: 0 20px 56px rgba(15, 23, 42, 0.22);
  backdrop-filter: blur(16px);
}

.toast-card--success {
  border-color: rgba(34, 197, 94, 0.42);
  box-shadow: 0 20px 56px rgba(34, 197, 94, 0.18);
}

.toast-card--error {
  border-color: rgba(239, 68, 68, 0.42);
  box-shadow: 0 20px 56px rgba(239, 68, 68, 0.18);
}

.toast-card--info {
  border-color: rgba(59, 130, 246, 0.42);
  box-shadow: 0 20px 56px rgba(59, 130, 246, 0.18);
}

.toast-card--favorite {
  border-color: rgba(244, 114, 182, 0.52);
  box-shadow: 0 20px 56px rgba(244, 114, 182, 0.22);
}

.toast-card__icon {
  display: grid;
  place-items: center;
  width: 36px;
  height: 36px;
  border-radius: 12px;
  flex: 0 0 auto;
}

.toast-card--success .toast-card__icon {
  color: #16a34a;
  background: rgba(34, 197, 94, 0.12);
}

.toast-card--error .toast-card__icon {
  color: #dc2626;
  background: rgba(239, 68, 68, 0.12);
}

.toast-card--info .toast-card__icon {
  color: #2563eb;
  background: rgba(59, 130, 246, 0.12);
}

.toast-card--favorite .toast-card__icon {
  color: #e11d48;
  background: rgba(244, 114, 182, 0.16);
}

.toast-card__content {
  flex: 1 1 auto;
  min-width: 0;
}

.toast-card__content strong {
  display: block;
  font-size: 1rem;
  line-height: 1.35;
  color: #0f172a;
}

.toast-card__content p {
  margin: 5px 0 0;
  font-size: 0.92rem;
  line-height: 1.45;
  color: #475569;
}

.toast-card__close {
  border: 0;
  background: transparent;
  color: #94a3b8;
  padding: 0;
  line-height: 1;
  flex: 0 0 auto;
}

.toast-card__close:hover {
  color: #0f172a;
}

.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: all 0.24s ease;
}

.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
  transform: translateY(-10px) scale(0.98);
}

@media (max-width: 576px) {
  .toast-stack {
    left: 16px;
    right: 16px;
    top: 16px;
  }

  .toast-card {
    min-width: 0;
    width: 100%;
  }
}
</style>
