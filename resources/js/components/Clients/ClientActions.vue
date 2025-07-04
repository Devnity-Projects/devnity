<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3'
import { Pencil, Trash2 } from 'lucide-vue-next'

const props = defineProps<{
  client: any
}>()

function destroy() {
  if (confirm('Tem certeza que deseja remover este cliente?')) {
    router.delete(route('clients.destroy', props.client.id))
  }
}
</script>

<template>
  <Link :href="route('clients.edit', client.id)" class="p-1 hover:bg-[#f3eaff] dark:hover:bg-[#30204a] rounded transition">
    <Pencil class="w-4 h-4" />
  </Link>
  <button @click="destroy" class="p-1 hover:bg-[#ffeaea] dark:hover:bg-[#442429] rounded transition">
    <Trash2 class="w-4 h-4 text-[#e55e55]" />
  </button>
</template>
