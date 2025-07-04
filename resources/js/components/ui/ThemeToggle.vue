<script setup lang="ts">
import { useTheme } from '@/composables/useTheme'
const { theme, toggleTheme } = useTheme()
</script>

<template>
  <button
    @click="toggleTheme"
    class="rounded-full p-2 border-none outline-none bg-transparent transition hover:bg-gray-100 dark:hover:bg-[#33334d]"
    :aria-label="theme === 'dark' ? 'Trocar para claro' : 'Trocar para escuro'"
    title="Alternar tema"
  >
    <svg v-if="theme === 'dark'" width="22" height="22" fill="none" viewBox="0 0 24 24">
      <path stroke="#b59cff" stroke-width="2" d="M17.657 16.243A8 8 0 1 1 12 4a8.001 8.001 0 0 0 5.657 12.243Z"/>
    </svg>
    <svg v-else width="22" height="22" fill="none" viewBox="0 0 24 24">
      <circle cx="12" cy="12" r="5" stroke="#6a0dad" stroke-width="2"/>
      <path stroke="#6a0dad" stroke-width="2" d="M12 1v2m0 18v2m11-11h-2M3 12H1m16.95 6.95-1.414-1.414M6.464 6.464 5.05 5.05m12.02 0-1.415 1.414M6.464 17.536 5.05 18.95"/>
    </svg>
  </button>
</template>
