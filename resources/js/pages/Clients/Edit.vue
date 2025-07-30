<script setup lang="ts">
import { useForm, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft, Save, Edit } from 'lucide-vue-next'

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
          <!-- Dados Principais -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <div class="w-2 h-2 rounded-full bg-blue-500"></div>
              Dados Principais
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Nome/Razão Social *
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="Nome completo ou razão social"
                />
                <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Tipo *
                </label>
                <select
                  v-model="form.type"
                  required
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                >
                  <option value="Pessoa Física">Pessoa Física</option>
                  <option value="Pessoa Jurídica">Pessoa Jurídica</option>
                </select>
                <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  CPF/CNPJ *
                </label>
                <input
                  v-model="form.document"
                  type="text"
                  required
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="CPF ou CNPJ"
                />
                <div v-if="form.errors.document" class="text-red-500 text-sm mt-1">{{ form.errors.document }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Status *
                </label>
                <select
                  v-model="form.status"
                  required
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                >
                  <option value="ativo">Ativo</option>
                  <option value="inativo">Inativo</option>
                </select>
                <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
              </div>
            </div>
          </div>

          <!-- Contato -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <div class="w-2 h-2 rounded-full bg-green-500"></div>
              Informações de Contato
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  E-mail
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="email@exemplo.com"
                />
                <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Telefone
                </label>
                <input
                  v-model="form.phone"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="(11) 99999-9999"
                />
                <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</div>
              </div>
            </div>
          </div>

          <!-- Responsável -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <div class="w-2 h-2 rounded-full bg-purple-500"></div>
              Pessoa Responsável
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Nome do Responsável
                </label>
                <input
                  v-model="form.responsible"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="Nome do responsável"
                />
                <div v-if="form.errors.responsible" class="text-red-500 text-sm mt-1">{{ form.errors.responsible }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  E-mail do Responsável
                </label>
                <input
                  v-model="form.responsible_email"
                  type="email"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="email@exemplo.com"
                />
                <div v-if="form.errors.responsible_email" class="text-red-500 text-sm mt-1">{{ form.errors.responsible_email }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Telefone do Responsável
                </label>
                <input
                  v-model="form.responsible_phone"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="(11) 99999-9999"
                />
                <div v-if="form.errors.responsible_phone" class="text-red-500 text-sm mt-1">{{ form.errors.responsible_phone }}</div>
              </div>
            </div>
          </div>

          <!-- Inscrições (só para PJ) -->
          <div v-if="form.type === 'Pessoa Jurídica'" class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <div class="w-2 h-2 rounded-full bg-orange-500"></div>
              Inscrições
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Inscrição Estadual
                </label>
                <input
                  v-model="form.state_registration"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="Inscrição estadual"
                />
                <div v-if="form.errors.state_registration" class="text-red-500 text-sm mt-1">{{ form.errors.state_registration }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Inscrição Municipal
                </label>
                <input
                  v-model="form.municipal_registration"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="Inscrição municipal"
                />
                <div v-if="form.errors.municipal_registration" class="text-red-500 text-sm mt-1">{{ form.errors.municipal_registration }}</div>
              </div>
            </div>
          </div>

          <!-- Endereço -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
              Endereço
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  CEP
                </label>
                <input
                  v-model="form.zip_code"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="00000-000"
                />
                <div v-if="form.errors.zip_code" class="text-red-500 text-sm mt-1">{{ form.errors.zip_code }}</div>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Endereço
                </label>
                <input
                  v-model="form.address"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="Rua, avenida, etc."
                />
                <div v-if="form.errors.address" class="text-red-500 text-sm mt-1">{{ form.errors.address }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Número
                </label>
                <input
                  v-model="form.number"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="123"
                />
                <div v-if="form.errors.number" class="text-red-500 text-sm mt-1">{{ form.errors.number }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Complemento
                </label>
                <input
                  v-model="form.complement"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="Apt, sala, etc."
                />
                <div v-if="form.errors.complement" class="text-red-500 text-sm mt-1">{{ form.errors.complement }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Bairro
                </label>
                <input
                  v-model="form.neighborhood"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="Nome do bairro"
                />
                <div v-if="form.errors.neighborhood" class="text-red-500 text-sm mt-1">{{ form.errors.neighborhood }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Cidade
                </label>
                <input
                  v-model="form.city"
                  type="text"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="Nome da cidade"
                />
                <div v-if="form.errors.city" class="text-red-500 text-sm mt-1">{{ form.errors.city }}</div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Estado
                </label>
                <input
                  v-model="form.state"
                  type="text"
                  maxlength="2"
                  class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="SP"
                />
                <div v-if="form.errors.state" class="text-red-500 text-sm mt-1">{{ form.errors.state }}</div>
              </div>
            </div>
          </div>

          <!-- Observações -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
              <div class="w-2 h-2 rounded-full bg-gray-500"></div>
              Observações
            </h3>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Notas
              </label>
              <textarea
                v-model="form.notes"
                rows="4"
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                placeholder="Observações adicionais sobre o cliente..."
              ></textarea>
              <div v-if="form.errors.notes" class="text-red-500 text-sm mt-1">{{ form.errors.notes }}</div>
            </div>
          </div>

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
