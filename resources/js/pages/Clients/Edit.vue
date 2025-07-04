<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import ClientForm from '@/components/Clients/ClientForm.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps<{
  client: any
}>()

// O ClientForm já está preparado para editar, só passar os dados
</script>

<template>
  <AppLayout>
    <div class="max-w-3xl mx-auto mt-8">
      <h1 class="text-2xl font-bold text-[#6a0dad] dark:text-[#b59cff] mb-6">
        Editar Cliente
      </h1>
      <ClientForm
        :client="props.client"
        submit-label="Salvar Alterações"
      />
    </div>
  </AppLayout>
</template>
