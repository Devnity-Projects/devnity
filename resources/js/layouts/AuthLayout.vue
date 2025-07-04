<script setup>
import ThemeToggle from '@/components/ui/ThemeToggle.vue'
</script>
<template>
  <div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-[#f3f0ff] to-[#fff] dark:from-[#181826] dark:to-[#22223a] transition-colors">
    <div class="absolute top-4 right-4">
      <ThemeToggle />
    </div>
    <slot />
  </div>
</template>
