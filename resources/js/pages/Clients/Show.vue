<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { 
  ArrowLeft, 
  Edit, 
  Trash2, 
  User, 
  Building, 
  Mail, 
  Phone, 
  MapPin, 
  FileText,
  CheckCircle,
  XCircle,
  Calendar,
  Hash
} from 'lucide-vue-next'

interface Client {
  id: number
  name: string
  type: string
  document: string
  formatted_document?: string
  email?: string
  phone?: string
  formatted_phone?: string
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
  created_at: string
}

const props = defineProps<{
  client: Client
}>()

const isPJ = computed(() => props.client.type === 'Pessoa Jurídica')

const statusInfo = computed(() => {
  if (props.client.status === 'ativo') {
    return {
      text: 'Ativo',
      icon: CheckCircle,
      class: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    }
  }
  return {
    text: 'Inativo',
    icon: XCircle,
    class: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
})

const typeInfo = computed(() => {
  if (isPJ.value) {
    return {
      text: 'Pessoa Jurídica',
      icon: Building,
      class: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400'
    }
  }
  return {
    text: 'Pessoa Física',
    icon: User,
    class: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
  }
})
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
              <div class="p-3 rounded-2xl bg-blue-100 dark:bg-blue-900/20">
                <component :is="typeInfo.icon" class="h-8 w-8 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <h1 class="text-4xl font-bold devnity-text-gradient">
                  {{ client.name }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-lg">
                  {{ client.formatted_document || client.document }}
                </p>
              </div>
            </div>
            <div class="flex items-center gap-3 mt-4">
              <span :class="[
                'inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium',
                statusInfo.class
              ]">
                <component :is="statusInfo.icon" class="h-4 w-4" />
                {{ statusInfo.text }}
              </span>
              <span :class="[
                'inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium',
                typeInfo.class
              ]">
                <component :is="typeInfo.icon" class="h-4 w-4" />
                {{ typeInfo.text }}
              </span>
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
            <Link
              :href="`/clients/${client.id}/edit`"
              class="devnity-button-primary flex items-center gap-2 px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200"
            >
              <Edit class="h-5 w-5" />
              Editar
            </Link>
          </div>
        </div>
      </div>

      <!-- Client Details Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Basic Information -->
          <div class="devnity-card">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                <Hash class="h-5 w-5 text-blue-500" />
                Informações Básicas
              </h3>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                  <div v-if="client.email" class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <Mail class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">E-mail</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">{{ client.email }}</div>
                    </div>
                  </div>

                  <div v-if="client.phone" class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <Phone class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">Telefone</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">{{ client.formatted_phone || client.phone }}</div>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <Calendar class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">Cadastro</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">
                        {{ new Date(client.created_at).toLocaleDateString('pt-BR') }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-4">
                  <div v-if="client.responsible" class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <User class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">Responsável</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">{{ client.responsible }}</div>
                    </div>
                  </div>

                  <div v-if="client.responsible_email" class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <Mail class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">E-mail do Responsável</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">{{ client.responsible_email }}</div>
                    </div>
                  </div>

                  <div v-if="client.responsible_phone" class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <Phone class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">Telefone do Responsável</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">{{ client.responsible_phone }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Address -->
          <div v-if="client.address || client.city || client.state || client.zip_code" class="devnity-card">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                <MapPin class="h-5 w-5 text-green-500" />
                Endereço
              </h3>
              
              <div class="space-y-3">
                <div v-if="client.address" class="flex flex-wrap gap-2 text-sm">
                  <span class="font-medium text-gray-900 dark:text-gray-100">{{ client.address }}</span>
                  <span v-if="client.number" class="text-gray-600 dark:text-gray-400">, {{ client.number }}</span>
                  <span v-if="client.complement" class="text-gray-600 dark:text-gray-400">- {{ client.complement }}</span>
                </div>
                
                <div v-if="client.neighborhood || client.city || client.state" class="flex flex-wrap gap-2 text-sm text-gray-600 dark:text-gray-400">
                  <span v-if="client.neighborhood">{{ client.neighborhood }}</span>
                  <span v-if="client.neighborhood && (client.city || client.state)">•</span>
                  <span v-if="client.city">{{ client.city }}</span>
                  <span v-if="client.city && client.state">-</span>
                  <span v-if="client.state">{{ client.state }}</span>
                </div>
                
                <div v-if="client.zip_code" class="text-sm text-gray-600 dark:text-gray-400">
                  CEP: {{ client.zip_code }}
                </div>
                
                <div v-if="client.country" class="text-sm text-gray-600 dark:text-gray-400">
                  {{ client.country }}
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="client.notes" class="devnity-card">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                <FileText class="h-5 w-5 text-purple-500" />
                Observações
              </h3>
              
              <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                {{ client.notes }}
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Registration Info (for PJ) -->
          <div v-if="isPJ && (client.state_registration || client.municipal_registration)" class="devnity-card">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <Building class="h-5 w-5 text-orange-500" />
                Inscrições
              </h3>
              
              <div class="space-y-4">
                <div v-if="client.state_registration">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Inscrição Estadual</div>
                  <div class="font-medium text-gray-900 dark:text-gray-100">{{ client.state_registration }}</div>
                </div>
                
                <div v-if="client.municipal_registration">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Inscrição Municipal</div>
                  <div class="font-medium text-gray-900 dark:text-gray-100">{{ client.municipal_registration }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="devnity-card">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Ações Rápidas
              </h3>
              
              <div class="space-y-3">
                <Link
                  :href="`/clients/${client.id}/edit`"
                  class="w-full flex items-center gap-3 px-4 py-3 text-left text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-colors"
                >
                  <Edit class="h-4 w-4" />
                  Editar Cliente
                </Link>
                
                <button
                  class="w-full flex items-center gap-3 px-4 py-3 text-left text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                >
                  <Trash2 class="h-4 w-4" />
                  Excluir Cliente
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
