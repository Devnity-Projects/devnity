<script setup lang="ts">
import { useForm, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft, Plus, UserPlus } from 'lucide-vue-next'

// Components
import BasicInfoSectionEnhanced from '@/components/Clients/BasicInfoSectionEnhanced.vue'
import ContactInfoSection from '@/components/Clients/ContactInfoSection.vue'
import ResponsibleInfoSection from '@/components/Clients/ResponsibleInfoSection.vue'
import RegistrationSection from '@/components/Clients/RegistrationSection.vue'
import AddressSection from '@/components/Clients/AddressSection.vue'
import NotesSection from '@/components/Clients/NotesSection.vue'

const form = useForm({
  name: '',
  type: 'Pessoa Física',
  document: '',
  email: '',
  phone: '',
  responsible: '',
  responsible_email: '',
  responsible_phone: '',
  state_registration: '',
  municipal_registration: '',
  zip_code: '',
  address: '',
  number: '',
  complement: '',
  neighborhood: '',
  city: '',
  state: '',
  country: 'Brasil',
  status: 'ativo',
  notes: ''
})

function submit() {
  form.post(route('clients.store'), {
    onSuccess: () => {
      router.visit(route('clients.index'))
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
              <div class="p-3 rounded-2xl bg-green-100 dark:bg-green-900/20">
                <UserPlus class="h-8 w-8 text-green-600 dark:text-green-400" />
              </div>
              <div>
                <h1 class="text-4xl font-bold devnity-text-gradient">
                  Novo Cliente
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-lg">
                  Adicione um novo cliente ao seu sistema
                </p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Link
              href="/clients"
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
          <BasicInfoSectionEnhanced :form="form" />

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
              href="/clients"
              class="px-6 py-3 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="devnity-button-primary flex items-center gap-2 px-6 py-3 rounded-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <Plus class="h-5 w-5" />
              {{ form.processing ? 'Salvando...' : 'Criar Cliente' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
