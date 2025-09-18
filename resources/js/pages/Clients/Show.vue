<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'
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

interface ClientData {
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

interface Client extends ClientData {
  data?: ClientData
}

const props = defineProps<{
  client: Client
  recentProjects?: any[]
  projectsCount?: number
}>()

// Safeguard para acessar os dados do client
const clientData = computed(() => {
  // Se client tem uma propriedade data, use ela, senão use client diretamente
  return (props.client as any)?.data || props.client || {}
})

const isPJ = computed(() => clientData.value.type === 'Pessoa Jurídica')
const showDeleteModal = ref(false)

const statusInfo = computed(() => {
  if (clientData.value.status === 'ativo') {
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

function deleteClient() {
  showDeleteModal.value = true
}

function confirmDelete() {
  router.delete(route('clients.destroy', clientData.value.id), {
    onSuccess: () => {
      router.visit(route('clients.index'))
    },
  })
  showDeleteModal.value = false
}

function getProjectStatusColor(status: string) {
  const colors: Record<string, string> = {
    'planning': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    'in_progress': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    'completed': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    'cancelled': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
    'on_hold': 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400'
  }
  return colors[status] || 'bg-gray-100 text-gray-800'
}

function getProjectStatusText(status: string) {
  const texts: Record<string, string> = {
    'planning': 'Planejamento',
    'in_progress': 'Em Progresso',
    'completed': 'Concluído',
    'cancelled': 'Cancelado',
    'on_hold': 'Em Espera'
  }
  return texts[status] || status
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
              <div class="p-3 rounded-2xl bg-blue-100 dark:bg-blue-900/20">
                <component :is="typeInfo.icon" class="h-8 w-8 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <h1 class="text-4xl font-bold devnity-text-gradient">
                  {{ clientData.name }}
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-lg">
                  {{ clientData.formatted_document || clientData.document }}
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
              :href="`/clients/${clientData.id}/edit`"
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
                  <div v-if="clientData.email" class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <Mail class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">E-mail</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">{{ clientData.email }}</div>
                    </div>
                  </div>

                  <div v-if="clientData.phone" class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <Phone class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">Telefone</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">{{ clientData.formatted_phone || clientData.phone }}</div>
                    </div>
                  </div>

                  <div class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <Calendar class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">Cadastro</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">
                        {{ new Date(clientData.created_at).toLocaleDateString('pt-BR') }}
                      </div>
                    </div>
                  </div>
                </div>

                <div class="space-y-4">
                  <div v-if="clientData.responsible" class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <User class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">Responsável</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">{{ clientData.responsible }}</div>
                    </div>
                  </div>

                  <div v-if="clientData.responsible_email" class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <Mail class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">E-mail do Responsável</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">{{ clientData.responsible_email }}</div>
                    </div>
                  </div>

                  <div v-if="clientData.responsible_phone" class="flex items-start gap-3">
                    <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                      <Phone class="h-4 w-4 text-gray-600 dark:text-gray-400" />
                    </div>
                    <div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">Telefone do Responsável</div>
                      <div class="font-medium text-gray-900 dark:text-gray-100">{{ clientData.responsible_phone }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Address -->
          <div v-if="clientData.address || clientData.city || clientData.state || clientData.zip_code" class="devnity-card">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                <MapPin class="h-5 w-5 text-green-500" />
                Endereço
              </h3>
              
              <div class="space-y-3">
                <div v-if="clientData.address" class="flex flex-wrap gap-2 text-sm">
                  <span class="font-medium text-gray-900 dark:text-gray-100">{{ clientData.address }}</span>
                  <span v-if="clientData.number" class="text-gray-600 dark:text-gray-400">, {{ clientData.number }}</span>
                  <span v-if="clientData.complement" class="text-gray-600 dark:text-gray-400">- {{ clientData.complement }}</span>
                </div>
                
                <div v-if="clientData.neighborhood || clientData.city || clientData.state" class="flex flex-wrap gap-2 text-sm text-gray-600 dark:text-gray-400">
                  <span v-if="clientData.neighborhood">{{ clientData.neighborhood }}</span>
                  <span v-if="clientData.neighborhood && (clientData.city || clientData.state)">•</span>
                  <span v-if="clientData.city">{{ clientData.city }}</span>
                  <span v-if="clientData.city && clientData.state">-</span>
                  <span v-if="clientData.state">{{ clientData.state }}</span>
                </div>
                
                <div v-if="clientData.zip_code" class="text-sm text-gray-600 dark:text-gray-400">
                  CEP: {{ clientData.zip_code }}
                </div>
                
                <div v-if="clientData.country" class="text-sm text-gray-600 dark:text-gray-400">
                  {{ clientData.country }}
                </div>
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div v-if="clientData.notes" class="devnity-card">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                <FileText class="h-5 w-5 text-purple-500" />
                Observações
              </h3>
              
              <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                {{ clientData.notes }}
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Registration Info (for PJ) -->
          <div v-if="isPJ && (clientData.state_registration || clientData.municipal_registration)" class="devnity-card">
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
                <Building class="h-5 w-5 text-orange-500" />
                Inscrições
              </h3>
              
              <div class="space-y-4">
                <div v-if="clientData.state_registration">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Inscrição Estadual</div>
                  <div class="font-medium text-gray-900 dark:text-gray-100">{{ clientData.state_registration }}</div>
                </div>
                
                <div v-if="clientData.municipal_registration">
                  <div class="text-sm text-gray-500 dark:text-gray-400">Inscrição Municipal</div>
                  <div class="font-medium text-gray-900 dark:text-gray-100">{{ clientData.municipal_registration }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Projects Section -->
          <div class="devnity-card" v-if="recentProjects && recentProjects.length > 0">
            <div class="p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                  Projetos Recentes
                </h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ projectsCount }} projeto(s) total
                </div>
              </div>
              
              <div class="space-y-3">
                <div
                  v-for="project in recentProjects"
                  :key="project.id"
                  class="p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                >
                  <div class="flex items-center justify-between">
                    <div>
                      <div class="font-medium text-gray-900 dark:text-gray-100 text-sm">
                        {{ project.name }}
                      </div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ new Date(project.created_at).toLocaleDateString('pt-BR') }}
                      </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                      :class="getProjectStatusColor(project.status)">
                      {{ getProjectStatusText(project.status) }}
                    </span>
                  </div>
                </div>
              </div>

              <Link
                href="/projects"
                class="mt-4 block text-center text-sm text-blue-600 dark:text-blue-400 hover:underline"
              >
                Ver todos os projetos
              </Link>
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
                  :href="`/clients/${clientData.id}/edit`"
                  class="w-full flex items-center gap-3 px-4 py-3 text-left text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 rounded-lg transition-colors"
                >
                  <Edit class="h-4 w-4" />
                  Editar Cliente
                </Link>
                
                <button
                  @click="deleteClient"
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

    <!-- Modal de Confirmação de Exclusão -->
    <ConfirmationModal
      :show="showDeleteModal"
      title="Excluir Cliente"
      :message="`Tem certeza que deseja excluir o cliente '${clientData.name}'? Esta ação não pode ser desfeita e todos os dados relacionados serão perdidos.`"
      confirm-text="Sim, Excluir"
      cancel-text="Cancelar"
      type="danger"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
      @close="showDeleteModal = false"
    />
  </AppLayout>
</template>
