<script setup lang="ts">
import { computed } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { ArrowLeft, Save, Edit } from 'lucide-vue-next'

// Components
import BasicInfoSectionEnhanced from '@/components/Clients/BasicInfoSectionEnhanced.vue'

interface ClientData {
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

interface Client extends ClientData {
  data?: ClientData
}

const props = defineProps<{
  client: Client
}>()

// Safeguard para acessar os dados do client
const clientData = computed(() => {
  return (props.client as any)?.data || props.client || {}
})

const form = useForm({
  name: clientData.value.name || '',
  type: clientData.value.type || 'Pessoa Física',
  document: clientData.value.document || '',
  email: clientData.value.email || '',
  phone: clientData.value.phone || '',
  responsible: clientData.value.responsible || '',
  responsible_email: clientData.value.responsible_email || '',
  responsible_phone: clientData.value.responsible_phone || '',
  state_registration: clientData.value.state_registration || '',
  municipal_registration: clientData.value.municipal_registration || '',
  zip_code: clientData.value.zip_code || '',
  address: clientData.value.address || '',
  number: clientData.value.number || '',
  complement: clientData.value.complement || '',
  neighborhood: clientData.value.neighborhood || '',
  city: clientData.value.city || '',
  state: clientData.value.state || '',
  country: clientData.value.country || 'Brasil',
  status: clientData.value.status || 'ativo',
  notes: clientData.value.notes || ''
})

function submit() {
  form.put(route('clients.update', clientData.value.id), {
    onSuccess: () => {
      router.visit(route('clients.show', clientData.value.id))
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
                  Atualize as informações de {{ clientData.name }}
                </p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Link
              :href="`/clients/${clientData.id}`"
              class="flex items-center gap-2 px-6 py-3 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-white dark:hover:bg-gray-800 transition-all duration-200 hover:shadow-lg hover:scale-105"
            >
              <ArrowLeft class="h-4 w-4" />
              Voltar
            </Link>
          </div>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-8">
        <!-- Informações Básicas -->
        <BasicInfoSectionEnhanced 
          v-model:name="form.name"
          v-model:type="form.type"
          v-model:document="form.document"
          v-model:status="form.status"
          :errors="form.errors"
        />

        <!-- Informações de Contato -->
        <div class="devnity-card">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-3">
            <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/20">
              <Mail class="h-5 w-5 text-blue-600 dark:text-blue-400" />
            </div>
            Informações de Contato
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Email
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="devnity-input"
                placeholder="email@exemplo.com"
              />
              <div v-if="form.errors.email" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.email }}
              </div>
            </div>
            
            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Telefone
              </label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                class="devnity-input"
                placeholder="(11) 99999-9999"
              />
              <div v-if="form.errors.phone" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.phone }}
              </div>
            </div>
          </div>
        </div>

        <!-- Responsável (se for PJ) -->
        <div v-if="form.type === 'Pessoa Jurídica'" class="devnity-card">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-3">
            <div class="p-2 rounded-lg bg-purple-100 dark:bg-purple-900/20">
              <User class="h-5 w-5 text-purple-600 dark:text-purple-400" />
            </div>
            Responsável
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label for="responsible" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Nome do Responsável
              </label>
              <input
                id="responsible"
                v-model="form.responsible"
                type="text"
                class="devnity-input"
                placeholder="Nome completo"
              />
              <div v-if="form.errors.responsible" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.responsible }}
              </div>
            </div>
            
            <div>
              <label for="responsible_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Email do Responsável
              </label>
              <input
                id="responsible_email"
                v-model="form.responsible_email"
                type="email"
                class="devnity-input"
                placeholder="email@exemplo.com"
              />
              <div v-if="form.errors.responsible_email" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.responsible_email }}
              </div>
            </div>
            
            <div>
              <label for="responsible_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Telefone do Responsável
              </label>
              <input
                id="responsible_phone"
                v-model="form.responsible_phone"
                type="tel"
                class="devnity-input"
                placeholder="(11) 99999-9999"
              />
              <div v-if="form.errors.responsible_phone" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.responsible_phone }}
              </div>
            </div>
          </div>
        </div>

        <!-- Endereço -->
        <div class="devnity-card">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-3">
            <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/20">
              <MapPin class="h-5 w-5 text-green-600 dark:text-green-400" />
            </div>
            Endereço
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="md:col-span-1">
              <label for="zip_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                CEP
              </label>
              <input
                id="zip_code"
                v-model="form.zip_code"
                type="text"
                class="devnity-input"
                placeholder="00000-000"
              />
              <div v-if="form.errors.zip_code" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.zip_code }}
              </div>
            </div>
            
            <div class="md:col-span-2">
              <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Endereço
              </label>
              <input
                id="address"
                v-model="form.address"
                type="text"
                class="devnity-input"
                placeholder="Rua, Avenida, etc."
              />
              <div v-if="form.errors.address" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.address }}
              </div>
            </div>
            
            <div>
              <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Número
              </label>
              <input
                id="number"
                v-model="form.number"
                type="text"
                class="devnity-input"
                placeholder="123"
              />
              <div v-if="form.errors.number" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.number }}
              </div>
            </div>
            
            <div>
              <label for="complement" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Complemento
              </label>
              <input
                id="complement"
                v-model="form.complement"
                type="text"
                class="devnity-input"
                placeholder="Apto, Sala, etc."
              />
              <div v-if="form.errors.complement" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.complement }}
              </div>
            </div>
            
            <div>
              <label for="neighborhood" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Bairro
              </label>
              <input
                id="neighborhood"
                v-model="form.neighborhood"
                type="text"
                class="devnity-input"
                placeholder="Nome do bairro"
              />
              <div v-if="form.errors.neighborhood" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.neighborhood }}
              </div>
            </div>
            
            <div>
              <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Cidade
              </label>
              <input
                id="city"
                v-model="form.city"
                type="text"
                class="devnity-input"
                placeholder="Nome da cidade"
              />
              <div v-if="form.errors.city" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.city }}
              </div>
            </div>
            
            <div>
              <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Estado
              </label>
              <input
                id="state"
                v-model="form.state"
                type="text"
                class="devnity-input"
                placeholder="UF"
                maxlength="2"
              />
              <div v-if="form.errors.state" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.state }}
              </div>
            </div>
            
            <div>
              <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                País
              </label>
              <input
                id="country"
                v-model="form.country"
                type="text"
                class="devnity-input"
                placeholder="Brasil"
              />
              <div v-if="form.errors.country" class="mt-2 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.country }}
              </div>
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div class="devnity-card">
          <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-3">
            <div class="p-2 rounded-lg bg-orange-100 dark:bg-orange-900/20">
              <FileText class="h-5 w-5 text-orange-600 dark:text-orange-400" />
            </div>
            Observações
          </h3>
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Notas e Observações
            </label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="4"
              class="devnity-input"
              placeholder="Informações adicionais sobre o cliente..."
            ></textarea>
            <div v-if="form.errors.notes" class="mt-2 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.notes }}
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-between pt-8 border-t border-gray-200 dark:border-gray-700">
          <Link
            :href="`/clients/${clientData.id}`"
            class="px-6 py-3 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200"
          >
            Cancelar
          </Link>
          
          <button
            type="submit"
            :disabled="form.processing"
            class="flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-500 text-white font-medium rounded-xl transition-all duration-200 hover:shadow-lg hover:scale-105 disabled:scale-100 disabled:shadow-none"
          >
            <Save class="h-4 w-4" />
            {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
