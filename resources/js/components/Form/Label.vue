<template>
  <div>
    <label class="label"><slot></slot></label>
    <span
      class="text-danger text-nowrap"
      v-if="required"
      >*</span
    >
  </div>
</template>
<script setup>
defineProps({
  required: {
    required: false,
    type: [Boolean],
    default: false,
  },
})
</script>
