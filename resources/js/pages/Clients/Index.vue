<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import ClientTable from '@/components/Clients/ClientTable.vue'
import ClientFilters from '@/components/Clients/ClientFilters.vue'
import { Link } from '@inertiajs/vue3'
import { Plus } from 'lucide-vue-next'
import { ref, computed } from 'vue'

const props = defineProps<{
  clients: {
    data: any[]
  }
}>()

const search = ref('')
const status = ref('')

// Filtragem local (para filtro real/pesado, envie para backend)
const filteredClients = computed(() => {
  let list = props.clients.data
  if (search.value) {
    const s = search.value.toLowerCase()
    list = list.filter(c =>
      c.name.toLowerCase().includes(s) ||
      (c.email && c.email.toLowerCase().includes(s)) ||
      (c.contact_name && c.contact_name.toLowerCase().includes(s))
    )
  }
  if (status.value) {
    list = list.filter(c => c.status === status.value)
  }
  return list
})
</script>

<template>
  <AppLayout>
    <div>
      <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mb-6">
        <h1 class="text-2xl font-bold text-[#6a0dad] dark:text-[#b59cff]">Clientes</h1>
        <Link :href="route('clients.create')" class="flex items-center gap-2 px-4 py-2 bg-[#6a0dad] text-white font-medium rounded-xl shadow hover:bg-[#5a058a] transition">
          <Plus class="w-5 h-5" /> Adicionar
        </Link>
      </div>
      <ClientFilters
        @update:search="search = $event"
        @update:status="status = $event"
      />
      <ClientTable :clients="filteredClients" />
    </div>
  </AppLayout>
</template>
