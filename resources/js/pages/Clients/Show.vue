<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
import { computed } from 'vue'

const props = defineProps<{
  client: any
}>()

// Para títulos bonitos
const isPJ = computed(() => props.client.type === 'Pessoa Jurídica')
</script>

<template>
  <AppLayout>
    <div class="max-w-3xl mx-auto mt-6">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-2">
        <h1 class="text-2xl font-bold text-[#6a0dad] dark:text-[#b59cff]">
          {{ props.client.name }}
        </h1>
        <div>
          <span class="inline-block rounded-full px-3 py-1 text-xs font-medium"
                :class="props.client.status === 'ativo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300'">
            {{ props.client.status === 'ativo' ? 'Ativo' : 'Inativo' }}
          </span>
        </div>
      </div>

      <!-- Dados principais -->
      <div class="rounded-2xl shadow bg-white dark:bg-[#232336] px-6 py-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <div class="mb-2"><span class="font-semibold">Tipo:</span> {{ props.client.type }}</div>
          <div class="mb-2"><span class="font-semibold">CPF/CNPJ:</span> {{ props.client.document }}</div>
          <div v-if="props.client.email" class="mb-2"><span class="font-semibold">E-mail:</span> {{ props.client.email }}</div>
          <div v-if="props.client.phone" class="mb-2"><span class="font-semibold">Telefone:</span> {{ props.client.phone }}</div>
        </div>
        <div>
          <div v-if="props.client.responsible" class="mb-2"><span class="font-semibold">Responsável:</span> {{ props.client.responsible }}</div>
          <div v-if="props.client.responsible_email" class="mb-2"><span class="font-semibold">E-mail do responsável:</span> {{ props.client.responsible_email }}</div>
          <div v-if="props.client.responsible_phone" class="mb-2"><span class="font-semibold">Telefone do responsável:</span> {{ props.client.responsible_phone }}</div>
          <div v-if="isPJ && props.client.state_registration" class="mb-2"><span class="font-semibold">IE:</span> {{ props.client.state_registration }}</div>
          <div v-if="isPJ && props.client.municipal_registration" class="mb-2"><span class="font-semibold">IM:</span> {{ props.client.municipal_registration }}</div>
        </div>
      </div>

      <!-- Endereço -->
      <div class="rounded-2xl shadow bg-white dark:bg-[#232336] px-6 py-4 mt-8">
        <div class="font-semibold mb-2 text-[#6a0dad] dark:text-[#b59cff]">Endereço</div>
        <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm">
          <div v-if="props.client.address"><span class="font-semibold">Endereço:</span> {{ props.client.address }}</div>
          <div v-if="props.client.number"><span class="font-semibold">Nº</span> {{ props.client.number }}</div>
          <div v-if="props.client.complement"><span class="font-semibold">Compl.:</span> {{ props.client.complement }}</div>
          <div v-if="props.client.neighborhood"><span class="font-semibold">Bairro:</span> {{ props.client.neighborhood }}</div>
          <div v-if="props.client.city"><span class="font-semibold">Cidade:</span> {{ props.client.city }}</div>
          <div v-if="props.client.state"><span class="font-semibold">UF:</span> {{ props.client.state }}</div>
          <div v-if="props.client.country"><span class="font-semibold">País:</span> {{ props.client.country }}</div>
          <div v-if="props.client.zip_code"><span class="font-semibold">CEP:</span> {{ props.client.zip_code }}</div>
        </div>
      </div>

      <!-- Notas -->
      <div v-if="props.client.notes" class="rounded-2xl shadow bg-white dark:bg-[#232336] px-6 py-4 mt-8">
        <div class="font-semibold mb-2 text-[#6a0dad] dark:text-[#b59cff]">Notas</div>
        <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
          {{ props.client.notes }}
        </div>
      </div>
    </div>
  </AppLayout>
</template>
