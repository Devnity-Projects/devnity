<script setup lang="ts">
import { useForm, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft, Save, Edit } from 'lucide-vue-next'

// Components
import BasicInfoSection from '@/components/Clients/BasicInfoSection.vue'
import ContactInfoSection from '@/components/Clients/ContactInfoSection.vue'
import ResponsibleInfoSection from '@/components/Clients/ResponsibleInfoSection.vue'
import RegistrationSection from '@/components/Clients/RegistrationSection.vue'
import AddressSection from '@/components/Clients/AddressSection.vue'
import NotesSection from '@/components/Clients/NotesSection.vue'

interface Client {
  id: number
  name: string
  type: string
  document: string
  email?: string
  phone?: string
  responsible?: string
  responsible_email?: string
  responsible_phone?: string
  state_registration?: string
  municipal_registration?: string
  zip_code?: string
  address?: string
  number?: string
  complement?: string
  neighborhood?: string
  city?: string
  state?: string
  country?: string
  status: string
  notes?: string
}

const props = defineProps<{
  client: Client
}>()

const form = useForm({
  name: props.client.name || '',
  type: props.client.type || 'Pessoa Física',
  document: props.client.document || '',
  email: props.client.email || '',
  phone: props.client.phone || '',
  responsible: props.client.responsible || '',
  responsible_email: props.client.responsible_email || '',
  responsible_phone: props.client.responsible_phone || '',
  state_registration: props.client.state_registration || '',
  municipal_registration: props.client.municipal_registration || '',
  zip_code: props.client.zip_code || '',
  address: props.client.address || '',
  number: props.client.number || '',
  complement: props.client.complement || '',
  neighborhood: props.client.neighborhood || '',
  city: props.client.city || '',
  state: props.client.state || '',
  country: props.client.country || 'Brasil',
  status: props.client.status || 'ativo',
  notes: props.client.notes || ''
})

function submit() {
  form.put(route('clients.update', props.client.id), {
    onSuccess: () => {
      router.visit(route('clients.show', props.client.id))
    },
  })
}
</script>

<template>
  <AppLayout>
    <div class="devnity-animate-in space-y-8">
      <!-- Hero Header -->
      <div class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900 rounded-2xl p-8 border border-gray-200 dark:border-gray-700">
        <div class="absolute inset-0 bg-grid-slate-100 dark:bg-grid-slate-700/25 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:[mask-image:linear-gradient(0deg,rgba(255,255,255,0.1),rgba(255,255,255,0.5))]"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <div class="p-3 rounded-2xl bg-yellow-100 dark:bg-yellow-900/20">
                <Edit class="h-8 w-8 text-yellow-600 dark:text-yellow-400" />
              </div>
              <div>
                <h1 class="text-4xl font-bold devnity-text-gradient">
                  Editar Cliente
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-lg">
                  Atualize as informações de {{ client.name }}
                </p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Link
              :href="`/clients/${client.id}`"
              class="flex items-center gap-2 px-6 py-3 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-white dark:hover:bg-gray-800 transition-all duration-200 hover:shadow-lg hover:scale-105"
            >
              <ArrowLeft class="h-5 w-5" />
              Voltar
            </Link>
          </div>
        </div>
      </div>

      <!-- Form Container -->
      <div class="devnity-card max-w-4xl mx-auto">
        <form @submit.prevent="submit" class="space-y-8">
          <!-- Basic Information -->
          <BasicInfoSection :form="form" />

          <!-- Contact Information -->
          <ContactInfoSection :form="form" />

          <!-- Responsible Information -->
          <ResponsibleInfoSection :form="form" />

          <!-- Registration Information (for Legal Entities) -->
          <RegistrationSection :form="form" />

          <!-- Address Information -->
          <AddressSection :form="form" />

          <!-- Notes -->
          <NotesSection :form="form" />

          <!-- Actions -->
          <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
            <Link
              :href="`/clients/${client.id}`"
              class="px-6 py-3 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="devnity-button-primary flex items-center gap-2 px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Save class="h-5 w-5" />
              {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
