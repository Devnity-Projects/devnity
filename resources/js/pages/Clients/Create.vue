<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import ClientForm from '@/components/Clients/ClientForm.vue'
import { useForm, router } from '@inertiajs/vue3'

const form = useForm({
  name: '', contact_name: '', email: '', phone: '', status: 'ativo', company: '', document: '', address: '', city: '', state: '', country: '', notes: '',
})

function submit(formInstance) {
  form.post(route('clients.store'), {
    onSuccess: () => router.visit(route('clients.index')),
  })
}
</script>

<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto">
      <h1 class="text-2xl font-bold mb-6 text-[#6a0dad] dark:text-[#b59cff]">Adicionar Cliente</h1>
      <ClientForm :processing="form.processing" submit-label="Cadastrar" @submit="submit" />
    </div>
  </AppLayout>
</template>
