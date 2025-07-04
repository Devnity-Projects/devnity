<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import { Search } from 'lucide-vue-next'

const emit = defineEmits(['update:search', 'update:status'])
const search = ref('')
const statusFilter = ref('')

watch(search, v => emit('update:search', v))
watch(statusFilter, v => emit('update:status', v))
</script>

<template>
  <div class="flex flex-wrap gap-2 mb-4">
    <div class="relative">
      <input
        v-model="search"
        type="text"
        placeholder="Buscar cliente, e-mail ou contato..."
        class="rounded-xl border px-4 py-2 pr-10 w-64 bg-white dark:bg-[#232336] dark:text-gray-100 focus:ring-2 focus:ring-[#6a0dad] transition"
      />
      <Search class="w-4 h-4 absolute right-3 top-3 text-gray-400 pointer-events-none" />
    </div>
    <select
      v-model="statusFilter"
      class="rounded-xl border px-3 py-2 bg-white dark:bg-[#232336] dark:text-gray-100 focus:ring-2 focus:ring-[#6a0dad] transition"
    >
      <option value="">Todos status</option>
      <option value="ativo">Ativo</option>
      <option value="inativo">Inativo</option>
      <option value="potencial">Potencial</option>
    </select>
  </div>
</template>
