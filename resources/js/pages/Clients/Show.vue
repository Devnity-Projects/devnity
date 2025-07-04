<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps<{ client: any }>()
</script>

<template>
  <AppLayout>
    <div class="max-w-2xl mx-auto space-y-4">
      <h1 class="text-2xl font-bold text-[#6a0dad] dark:text-[#b59cff] mb-4">
        {{ client.name }}
      </h1>
      <div class="rounded-xl bg-white dark:bg-[#232336] shadow p-6 space-y-2">
        <div><strong>Status:</strong> {{ client.status }}</div>
        <div><strong>E-mail:</strong> {{ client.email }}</div>
        <div v-if="client.contact_name"><strong>Contato:</strong> {{ client.contact_name }}</div>
        <div v-if="client.phone"><strong>Telefone:</strong> {{ client.phone }}</div>
        <div v-if="client.company"><strong>Empresa:</strong> {{ client.company }}</div>
        <div v-if="client.document"><strong>CPF/CNPJ:</strong> {{ client.document }}</div>
        <div v-if="client.address"><strong>Endereço:</strong> {{ client.address }}</div>
        <div v-if="client.city"><strong>Cidade:</strong> {{ client.city }}</div>
        <div v-if="client.state"><strong>Estado:</strong> {{ client.state }}</div>
        <div v-if="client.country"><strong>País:</strong> {{ client.country }}</div>
        <div v-if="client.notes"><strong>Notas:</strong> {{ client.notes }}</div>
      </div>
    </div>
  </AppLayout>
</template>
