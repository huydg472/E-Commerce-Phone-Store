<script setup>
defineEmits(['change'])
</script>

<template>
  <div class="form-group">
    <label>Upload ảnh</label>
    <input type="file" accept="image/*" @change="$emit('change', $event.target.files[0])">
  </div>
</template>
