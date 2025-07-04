<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import ClientActions from './ClientActions.vue'

const props = defineProps<{
  clients: any[]
}>()
</script>

<template>
  <div class="overflow-x-auto rounded-xl shadow bg-white dark:bg-[#232336]">
    <table class="min-w-full text-sm">
      <thead>
        <tr class="bg-[#f3eaff] dark:bg-[#30204a] text-[#6a0dad] dark:text-[#b59cff]">
          <th class="py-3 px-4 text-left">Nome</th>
          <th class="py-3 px-4 text-left">Contato</th>
          <th class="py-3 px-4 text-left">E-mail</th>
          <th class="py-3 px-4 text-left">Status</th>
          <th class="py-3 px-4 text-right">Ações</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="client in clients" :key="client.id"
            class="border-b hover:bg-[#f8f5fd] dark:hover:bg-[#262147] transition group">
          <td class="py-2 px-4 font-medium">
            <Link :href="route('clients.show', client.id)"
              class="text-[#6a0dad] dark:text-[#b59cff] hover:underline">
              {{ client.name }}
            </Link>
          </td>
          <td class="py-2 px-4">{{ client.contact_name }}</td>
          <td class="py-2 px-4">{{ client.email }}</td>
          <td class="py-2 px-4">
            <span
              class="inline-block px-2 py-1 rounded-full text-xs font-semibold"
              :class="{
                'bg-[#6a0dad]/10 text-[#6a0dad]': client.status === 'ativo',
                'bg-[#e55e55]/10 text-[#e55e55]': client.status === 'inativo',
                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-200': client.status === 'potencial'
              }"
            >
              {{ client.status.charAt(0).toUpperCase() + client.status.slice(1) }}
            </span>
          </td>
          <td class="py-2 px-4 flex gap-2 justify-end">
            <ClientActions :client="client" />
          </td>
        </tr>
        <tr v-if="clients.length === 0">
          <td colspan="5" class="text-center text-gray-500 py-8">Nenhum cliente encontrado.</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
