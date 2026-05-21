export function useConfirm() {
  function confirmDelete(message = 'Bạn có chắc muốn xoá dữ liệu này không?') {
    return window.confirm(message)
  }

  return {
    confirmDelete,
  }
}
