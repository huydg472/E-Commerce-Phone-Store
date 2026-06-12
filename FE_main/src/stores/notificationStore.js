import {defineStore} from 'pinia'

const timers = new Map()

const normalizeMessage = (message, fallback) => {
    const text = String(message ?? '').trim()
    return text || fallback
}

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        items: [],
        nextId: 1,
    }),

    actions: {
        push({type = 'success', title = '', message = '', timeout = 4500} = {}) {
            const id = this.nextId++
            const item = {
                id,
                type,
                title: normalizeMessage(
                    title,
                    type === 'error' ? 'Thất bại' : (type === 'favorite' ? 'Yêu thích' : 'Thành công')
                ),
                message: normalizeMessage(message, ''),
            }

            this.items = [...this.items, item]

            if (timeout > 0) {
                const timer = window.setTimeout(() => {
                    this.remove(id)
                }, timeout)

                timers.set(id, timer)
            }

            return id
        },

        success(message, title = 'Thành công') {
            return this.push({type: 'success', title, message})
        },

        error(message, title = 'Thất bại') {
            return this.push({type: 'error', title, message, timeout: 5000})
        },

        info(message, title = 'Thông báo') {
            return this.push({type: 'info', title, message})
        },

        favorite(message, title = 'Yêu thích') {
            return this.push({type: 'favorite', title, message})
        },

        remove(id) {
            const timer = timers.get(id)
            if (timer) {
                window.clearTimeout(timer)
                timers.delete(id)
            }

            this.items = this.items.filter((item) => item.id !== id)
        },

        clear() {
            timers.forEach((timer) => window.clearTimeout(timer))
            timers.clear()
            this.items = []
        },
    },
})
