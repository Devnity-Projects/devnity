<script setup lang="ts">
const props = defineProps({
  open: Boolean,
  title: String,
  width: { type: String, default: 'max-w-lg' },
})
const emit = defineEmits(['close'])
</script>

<template>
  <transition name="fade">
    <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/30">
      <div :class="['bg-white p-8 rounded-2xl shadow-2xl relative w-full', width]">
        <h2 v-if="title" class="text-xl font-bold mb-4">{{ title }}</h2>
        <button @click="emit('close')" class="absolute top-4 right-4 text-gray-400 hover:text-[#e55e55] text-2xl">&times;</button>
        <slot />
      </div>
    </div>
  </transition>
</template>
<style>
.fade-enter-active, .fade-leave-active { transition: opacity .2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
