<script setup lang="ts">
import { computed, ref } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { 
  ArrowLeft, 
  Save, 
  Edit, 
  User, 
  Mail, 
  MapPin, 
  FileText, 
  Building2, 
  Phone, 
  Search,
  Loader2
} from 'lucide-vue-next'

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

// CEP search functionality
const searchingCep = ref(false)

async function searchCep() {
  if (!form.zip_code || form.zip_code.length < 8) return
  
  searchingCep.value = true
  
  try {
    const response = await fetch(`/api/cep/${form.zip_code.replace(/\D/g, '')}`)
    if (response.ok) {
      const data = await response.json()
      
      if (!data.error) {
        form.address = data.logradouro || form.address
        form.neighborhood = data.bairro || form.neighborhood
        form.city = data.localidade || form.city
        form.state = data.uf || form.state
      }
    }
  } catch (error) {
    console.error('Erro ao buscar CEP:', error)
  } finally {
    searchingCep.value = false
  }
}

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
    <div class="devnity-animate-in space-y-6">
      <!-- Enhanced Hero Header -->
      <div class="relative overflow-hidden bg-gradient-to-br from-amber-50 via-orange-50 to-red-50 dark:from-gray-900 dark:via-amber-900/20 dark:to-orange-900/20 rounded-3xl p-8 border border-amber-200/50 dark:border-amber-700/30 shadow-xl">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.15)_1px,transparent_0)] [background-size:20px_20px]"></div>
        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-gradient-to-br from-amber-400/20 to-orange-500/20 rounded-full blur-xl"></div>
        <div class="absolute bottom-0 left-0 -mb-4 -ml-4 w-32 h-32 bg-gradient-to-tr from-orange-400/10 to-red-500/10 rounded-full blur-2xl"></div>
        
        <div class="relative flex flex-col lg:flex-row lg:items-center justify-between gap-6">
          <div class="space-y-3">
            <div class="flex items-center gap-4">
              <div class="p-3 rounded-2xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/30 dark:to-orange-900/30 shadow-lg">
                <Edit class="h-8 w-8 text-amber-600 dark:text-amber-400" />
              </div>
              <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-amber-600 via-orange-600 to-red-600 bg-clip-text text-transparent">
                  Editar Cliente
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-lg font-medium mt-1">
                  Atualize as informações de {{ clientData.name }}
                </p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Link
              :href="`/clients/${clientData.id}`"
              class="group flex items-center gap-2 px-6 py-3 text-gray-700 dark:text-gray-300 bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-white dark:hover:bg-gray-800 transition-all duration-300 hover:shadow-lg hover:scale-105 hover:-translate-y-0.5"
            >
              <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
              Voltar
            </Link>
          </div>
        </div>
      </div>

      <!-- Enhanced Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Informações Básicas - Enhanced Card -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
          <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6">
            <h3 class="text-xl font-bold text-white flex items-center gap-3">
              <div class="p-2 rounded-xl bg-white/20 backdrop-blur">
                <User class="h-5 w-5" />
              </div>
              Informações Básicas
            </h3>
            <p class="text-blue-100 mt-1">Dados principais do cliente</p>
          </div>
          
          <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-2">
                <label for="name" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  Nome Completo *
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="Digite o nome completo ou razão social"
                />
                <div v-if="form.errors.name" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.name }}
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="type" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  Tipo de Cliente *
                </label>
                <select
                  id="type"
                  v-model="form.type"
                  required
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 transition-all duration-200 font-medium"
                >
                  <option value="Pessoa Física">🧑‍💼 Pessoa Física</option>
                  <option value="Pessoa Jurídica">🏢 Pessoa Jurídica</option>
                </select>
                <div v-if="form.errors.type" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.type }}
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="document" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  {{ form.type === 'Pessoa Física' ? '📄 CPF' : '📋 CNPJ' }} *
                </label>
                <input
                  id="document"
                  v-model="form.document"
                  type="text"
                  required
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 transition-all duration-200 font-medium placeholder-gray-400"
                  :placeholder="form.type === 'Pessoa Física' ? '000.000.000-00' : '00.000.000/0000-00'"
                />
                <div v-if="form.errors.document" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.document }}
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="status" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  Status
                </label>
                <select
                  id="status"
                  v-model="form.status"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 transition-all duration-200 font-medium"
                >
                  <option value="ativo">✅ Ativo</option>
                  <option value="inativo">❌ Inativo</option>
                </select>
                <div v-if="form.errors.status" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.status }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Informações de Contato - Enhanced Card -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
          <div class="bg-gradient-to-r from-emerald-500 to-teal-600 p-6">
            <h3 class="text-xl font-bold text-white flex items-center gap-3">
              <div class="p-2 rounded-xl bg-white/20 backdrop-blur">
                <Mail class="h-5 w-5" />
              </div>
              Informações de Contato
            </h3>
            <p class="text-emerald-100 mt-1">Como entrar em contato</p>
          </div>
          
          <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <div class="space-y-2">
                <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  📧 Email
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="contato@exemplo.com"
                />
                <div v-if="form.errors.email" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.email }}
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="phone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  📱 Telefone
                </label>
                <input
                  id="phone"
                  v-model="form.phone"
                  type="tel"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 dark:focus:ring-emerald-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="(11) 99999-9999"
                />
                <div v-if="form.errors.phone" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.phone }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Responsável (se for PJ) - Enhanced Card -->
        <div v-if="form.type === 'Pessoa Jurídica'" class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
          <div class="bg-gradient-to-r from-purple-500 to-pink-600 p-6">
            <h3 class="text-xl font-bold text-white flex items-center gap-3">
              <div class="p-2 rounded-xl bg-white/20 backdrop-blur">
                <Building2 class="h-5 w-5" />
              </div>
              Responsável pela Empresa
            </h3>
            <p class="text-purple-100 mt-1">Informações do representante legal</p>
          </div>
          
          <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
              <div class="space-y-2">
                <label for="responsible" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  👤 Nome do Responsável
                </label>
                <input
                  id="responsible"
                  v-model="form.responsible"
                  type="text"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:ring-purple-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="Nome completo do responsável"
                />
                <div v-if="form.errors.responsible" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.responsible }}
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="responsible_email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  📧 Email do Responsável
                </label>
                <input
                  id="responsible_email"
                  v-model="form.responsible_email"
                  type="email"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:ring-purple-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="responsavel@empresa.com"
                />
                <div v-if="form.errors.responsible_email" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.responsible_email }}
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="responsible_phone" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  📱 Telefone do Responsável
                </label>
                <input
                  id="responsible_phone"
                  v-model="form.responsible_phone"
                  type="tel"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 dark:focus:ring-purple-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="(11) 99999-9999"
                />
                <div v-if="form.errors.responsible_phone" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.responsible_phone }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Endereço com Busca de CEP - Enhanced Card -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
          <div class="bg-gradient-to-r from-orange-500 to-red-600 p-6">
            <h3 class="text-xl font-bold text-white flex items-center gap-3">
              <div class="p-2 rounded-xl bg-white/20 backdrop-blur">
                <MapPin class="h-5 w-5" />
              </div>
              Endereço Completo
            </h3>
            <p class="text-orange-100 mt-1">Localização e endereço de correspondência</p>
          </div>
          
          <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
              <div class="space-y-2">
                <label for="zip_code" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  📮 CEP
                </label>
                <div class="relative">
                  <input
                    id="zip_code"
                    v-model="form.zip_code"
                    type="text"
                    class="w-full px-4 py-3.5 pr-12 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:focus:ring-orange-400 transition-all duration-200 font-medium placeholder-gray-400"
                    placeholder="00000-000"
                    @blur="searchCep"
                  />
                  <button
                    type="button"
                    @click="searchCep"
                    :disabled="searchingCep || !form.zip_code"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1.5 text-gray-400 hover:text-orange-500 transition-colors disabled:opacity-50"
                  >
                    <Loader2 v-if="searchingCep" class="h-4 w-4 animate-spin" />
                    <Search v-else class="h-4 w-4" />
                  </button>
                </div>
                <div v-if="form.errors.zip_code" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.zip_code }}
                </div>
              </div>
              
              <div class="md:col-span-2 space-y-2">
                <label for="address" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  🏠 Endereço
                </label>
                <input
                  id="address"
                  v-model="form.address"
                  type="text"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:focus:ring-orange-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="Rua, Avenida, Alameda..."
                />
                <div v-if="form.errors.address" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.address }}
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="number" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  🔢 Número
                </label>
                <input
                  id="number"
                  v-model="form.number"
                  type="text"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:focus:ring-orange-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="123"
                />
                <div v-if="form.errors.number" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.number }}
                </div>
              </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div class="space-y-2">
                <label for="complement" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  🏢 Complemento
                </label>
                <input
                  id="complement"
                  v-model="form.complement"
                  type="text"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:focus:ring-orange-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="Apto, Sala, Bloco..."
                />
                <div v-if="form.errors.complement" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.complement }}
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="neighborhood" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  🏘️ Bairro
                </label>
                <input
                  id="neighborhood"
                  v-model="form.neighborhood"
                  type="text"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:focus:ring-orange-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="Nome do bairro"
                />
                <div v-if="form.errors.neighborhood" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.neighborhood }}
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="city" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  🏙️ Cidade
                </label>
                <input
                  id="city"
                  v-model="form.city"
                  type="text"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:focus:ring-orange-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="Nome da cidade"
                />
                <div v-if="form.errors.city" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.city }}
                </div>
              </div>
              
              <div class="space-y-2">
                <label for="state" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                  🗺️ Estado
                </label>
                <input
                  id="state"
                  v-model="form.state"
                  type="text"
                  class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 dark:focus:ring-orange-400 transition-all duration-200 font-medium placeholder-gray-400"
                  placeholder="SP"
                  maxlength="2"
                />
                <div v-if="form.errors.state" class="text-sm text-red-600 dark:text-red-400 font-medium">
                  {{ form.errors.state }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Observações - Enhanced Card -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-xl border border-gray-200/50 dark:border-gray-700/50 overflow-hidden">
          <div class="bg-gradient-to-r from-indigo-500 to-blue-600 p-6">
            <h3 class="text-xl font-bold text-white flex items-center gap-3">
              <div class="p-2 rounded-xl bg-white/20 backdrop-blur">
                <FileText class="h-5 w-5" />
              </div>
              Observações e Notas
            </h3>
            <p class="text-indigo-100 mt-1">Informações adicionais sobre o cliente</p>
          </div>
          
          <div class="p-8">
            <div class="space-y-2">
              <label for="notes" class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
                📝 Notas e Observações
              </label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="4"
                class="w-full px-4 py-3.5 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:focus:ring-indigo-400 transition-all duration-200 font-medium placeholder-gray-400 resize-none"
                placeholder="Digite informações adicionais sobre o cliente, preferências, histórico de atendimento, etc..."
              ></textarea>
              <div v-if="form.errors.notes" class="text-sm text-red-600 dark:text-red-400 font-medium">
                {{ form.errors.notes }}
              </div>
            </div>
          </div>
        </div>

        <!-- Enhanced Action Buttons -->
        <div class="flex items-center justify-between pt-8">
          <Link
            :href="`/clients/${clientData.id}`"
            class="group flex items-center gap-3 px-8 py-4 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-2xl hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-400 dark:hover:border-gray-500 transition-all duration-300 hover:shadow-lg hover:scale-105"
          >
            <ArrowLeft class="h-5 w-5 transition-transform group-hover:-translate-x-1" />
            <span class="font-semibold">Cancelar</span>
          </Link>
          
          <button
            type="submit"
            :disabled="form.processing"
            class="group flex items-center gap-3 px-10 py-4 bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 hover:from-blue-700 hover:via-purple-700 hover:to-indigo-700 disabled:from-gray-400 disabled:via-gray-500 disabled:to-gray-600 text-white font-bold rounded-2xl transition-all duration-300 hover:shadow-2xl hover:scale-105 disabled:scale-100 disabled:shadow-none transform-gpu"
          >
            <Save class="h-5 w-5 transition-transform group-hover:scale-110" />
            <span>{{ form.processing ? 'Salvando Alterações...' : 'Salvar Alterações' }}</span>
            <div v-if="form.processing" class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
