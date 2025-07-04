<script setup lang="ts">
import { ref } from 'vue'
const message = ref('')
const color = ref('success')
function show(msg, type = 'success') {
  message.value = msg
  color.value = type
  setTimeout(() => { message.value = '' }, 3000)
}
defineExpose({ show })
</script>

<template>
  <transition name="fade">
    <div v-if="message" :class="['fixed top-6 right-6 px-6 py-3 rounded-xl shadow text-white z-50',
      color === 'success' ? 'bg-[#6a0dad]' : 'bg-[#e55e55]']">
      {{ message }}
    </div>
  </transition>
</template>
