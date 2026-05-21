import { ref } from 'vue'

export function useSearch(defaultKeyword = '') {
  const keyword = ref(defaultKeyword)

  function setKeyword(value) {
    keyword.value = value
  }

  return {
    keyword,
    setKeyword,
  }
}
