import { ref } from 'vue'

export function usePagination(defaultPage = 1, defaultPerPage = 10) {
  const page = ref(defaultPage)
  const perPage = ref(defaultPerPage)

  function setPage(value) {
    page.value = value
  }

  return {
    page,
    perPage,
    setPage,
  }
}
