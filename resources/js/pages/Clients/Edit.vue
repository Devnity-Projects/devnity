<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import ClientForm from '@/Components/Clients/ClientForm.vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps<{ client: any }>()

const form = useForm({ ...props.client })

function submit(formInstance) {
  form.put(route('clients.update', props.client.id), {
    onSuccess: () => router.visit(route('clients.index')),
  })
}
</script>

<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto">
      <h1 class="text-2xl font-bold mb-6 text-[#6a0dad] dark:text-[#b59cff]">Editar Cliente</h1>
      <ClientForm :client="props.client" :processing="form.processing" submit-label="Salvar Alterações" @submit="submit" />
    </div>
  </AppLayout>
</template>
