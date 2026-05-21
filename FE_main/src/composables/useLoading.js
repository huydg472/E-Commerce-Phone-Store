import { ref } from 'vue'

export function useLoading(defaultValue = false) {
  const loading = ref(defaultValue)

  function startLoading() {
    loading.value = true
  }

  function stopLoading() {
    loading.value = false
  }

  return {
    loading,
    startLoading,
    stopLoading,
  }
}
