<script setup lang="ts">
defineProps({
  value: Number,
  max: { type: Number, default: 100 }
})
</script>

<template>
  <div class="w-full bg-gray-200 rounded-xl h-3">
    <div :style="{ width: `${(value / max) * 100}%` }"
      class="h-3 rounded-xl bg-[#6a0dad] transition-all"></div>
  </div>
</template>
