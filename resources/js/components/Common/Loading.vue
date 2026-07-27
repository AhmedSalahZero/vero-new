<template>
  <div v-if="isLoading">
    <div class="loading-container">
      <div class="spinner"></div>
      <p class="font-weight-bold">Retrieving your data...</p>
    </div>
  </div>
</template>
<script setup>
defineProps({
  isLoading: {
    type: Boolean,
    default: true,
    required: true,
  },
})
</script>
<style scoped>
.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 50px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(0, 0, 0, 0.1);
  border-left-color: #3498db; /* لون البراند الخاص بك */
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

p {
  color: #666;
  font-family: sans-serif;
  font-size: 14px;
  letter-spacing: 0.5px;
}
</style>
