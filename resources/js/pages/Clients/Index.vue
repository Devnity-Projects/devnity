<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import BulkActions from '@/components/Clients/BulkActions.vue'
import { 
  Plus, 
  Search, 
  Filter, 
  Download, 
  Users, 
  Building, 
  TrendingUp, 
  Calendar,
  MoreHorizontal,
  Eye,
  Edit,
  Trash2
} from 'lucide-vue-next'

interface Client {
  id: number
  name: string
  type: string
  email: string
  phone: string
  status: string
  city: string
  state: string
  created_at: string
  formatted_document: string
  formatted_phone: string
}

interface Props {
  clients: {
    data: Client[]
    links?: {
      label: string
      url: string | null
      active: boolean
    }[]
    meta?: {
      from?: number
      to?: number
      total?: number
      current_page?: number
      last_page?: number
      per_page?: number
    }
  }
  filters: {
    search?: string
    status?: string
    type?: string
    sort_by?: string
    sort_direction?: string
  }
  stats: {
    total: number
    active: number
    inactive: number
    individual: number
    legal: number
  }
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const type = ref(props.filters.type || '')
const sortBy = ref(props.filters.sort_by || 'name')
const sortDirection = ref(props.filters.sort_direction || 'asc')
const selectedClients = ref<number[]>([])

// Computed for select all functionality
const isAllSelected = computed(() => 
  props.clients.data.length > 0 && selectedClients.value.length === props.clients.data.length
)

const isIndeterminate = computed(() => 
  selectedClients.value.length > 0 && selectedClients.value.length < props.clients.data.length
)

// Quick stats
const quickStats = computed(() => [
  {
    label: 'Total',
    value: props.stats.total,
    icon: Users,
    color: 'text-blue-600 dark:text-blue-400',
    bgColor: 'bg-blue-50 dark:bg-blue-950/20'
  },
  {
    label: 'Ativos',
    value: props.stats.active,
    icon: TrendingUp,
    color: 'text-green-600 dark:text-green-400',
    bgColor: 'bg-green-50 dark:bg-green-950/20'
  },
  {
    label: 'Pessoa Física',
    value: props.stats.individual,
    icon: Users,
    color: 'text-purple-600 dark:text-purple-400',
    bgColor: 'bg-purple-50 dark:bg-purple-950/20'
  },
  {
    label: 'Pessoa Jurídica',
    value: props.stats.legal,
    icon: Building,
    color: 'text-orange-600 dark:text-orange-400',
    bgColor: 'bg-orange-50 dark:bg-orange-950/20'
  }
])

function applyFilters() {
  const params = new URLSearchParams()
  if (search.value) params.set('search', search.value)
  if (status.value) params.set('status', status.value)
  if (type.value) params.set('type', type.value)
  if (sortBy.value) params.set('sort_by', sortBy.value)
  if (sortDirection.value) params.set('sort_direction', sortDirection.value)
  
  router.get('/clients?' + params.toString())
}

function clearFilters() {
  search.value = ''
  status.value = ''
  type.value = ''
  router.get('/clients')
}

function exportClients() {
  const params = new URLSearchParams()
  if (search.value) params.set('search', search.value)
  if (status.value) params.set('status', status.value)
  if (type.value) params.set('type', type.value)
  
  window.open('/clients-export?' + params.toString())
}

function toggleSort(field: string) {
  if (sortBy.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortDirection.value = 'asc'
  }
  applyFilters()
}

function getStatusColor(status: string) {
  return status === 'ativo' 
    ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
    : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400'
}

function getTypeColor(type: string) {
  return type === 'Pessoa Física'
    ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
    : 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400'
}

function toggleSelectAll() {
  if (isAllSelected.value) {
    selectedClients.value = []
  } else {
    selectedClients.value = props.clients.data.map(client => client.id)
  }
}

function toggleClientSelection(clientId: number) {
  const index = selectedClients.value.indexOf(clientId)
  if (index > -1) {
    selectedClients.value.splice(index, 1)
  } else {
    selectedClients.value.push(clientId)
  }
}

function clearSelection() {
  selectedClients.value = []
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
                <Users class="h-8 w-8 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <h1 class="text-4xl font-bold devnity-text-gradient">
                  Clientes
                </h1>
                <p class="text-gray-600 dark:text-gray-400 text-lg">
                  Gerencie sua carteira de clientes de forma inteligente
                </p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <button
              @click="exportClients"
              class="flex items-center gap-2 px-6 py-3 text-gray-700 dark:text-gray-300 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border border-gray-300 dark:border-gray-600 rounded-xl hover:bg-white dark:hover:bg-gray-800 transition-all duration-200 hover:shadow-lg hover:scale-105"
            >
              <Download class="h-5 w-5" />
              Exportar
            </button>
            <Link
              href="/clients/create"
              class="devnity-button-primary flex items-center gap-2 px-6 py-3 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-200"
            >
              <Plus class="h-5 w-5" />
              Novo Cliente
            </Link>
          </div>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="stat in quickStats"
          :key="stat.label"
          class="devnity-card p-4"
        >
          <div class="flex items-center gap-3">
            <div :class="[
              'p-2 rounded-lg',
              stat.bgColor
            ]">
              <component :is="stat.icon" :class="['h-5 w-5', stat.color]" />
            </div>
            <div>
              <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                {{ stat.value }}
              </div>
              <div class="text-sm text-gray-600 dark:text-gray-400">
                {{ stat.label }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="devnity-card p-6">
        <div class="flex flex-col sm:flex-row gap-4">
          <!-- Search -->
          <div class="flex-1">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
              <input
                v-model="search"
                type="text"
                placeholder="Buscar por nome, email ou documento..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                @keyup.enter="applyFilters"
              />
            </div>
          </div>

          <!-- Status Filter -->
          <select
            v-model="status"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Todos os Status</option>
            <option value="ativo">Ativo</option>
            <option value="inativo">Inativo</option>
          </select>

          <!-- Type Filter -->
          <select
            v-model="type"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="">Todos os Tipos</option>
            <option value="Pessoa Física">Pessoa Física</option>
            <option value="Pessoa Jurídica">Pessoa Jurídica</option>
          </select>

          <!-- Action Buttons -->
          <div class="flex gap-2">
            <button
              @click="applyFilters"
              class="flex items-center gap-2 px-4 py-2 devnity-gradient text-white rounded-lg hover:opacity-90 transition-opacity"
            >
              <Filter class="h-4 w-4" />
              Filtrar
            </button>
            <button
              @click="clearFilters"
              class="px-4 py-2 text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            >
              Limpar
            </button>
          </div>
        </div>
      </div>

      <!-- Bulk Actions -->
      <BulkActions 
        :selected-clients="selectedClients" 
        @clear-selection="clearSelection"
      />

      <!-- Clients Table -->
      <div class="devnity-card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
              <tr>
                <th class="px-6 py-3 text-left">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    :indeterminate="isIndeterminate"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                </th>
                <th
                  @click="toggleSort('name')"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Nome
                    <span v-if="sortBy === 'name'" class="text-blue-500">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Contato
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Localização
                </th>
                <th
                  @click="toggleSort('status')"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Status
                    <span v-if="sortBy === 'status'" class="text-blue-500">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th
                  @click="toggleSort('created_at')"
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                >
                  <div class="flex items-center gap-1">
                    Cadastro
                    <span v-if="sortBy === 'created_at'" class="text-blue-500">
                      {{ sortDirection === 'asc' ? '↑' : '↓' }}
                    </span>
                  </div>
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
              <tr
                v-for="client in clients.data"
                :key="client.id"
                class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    :checked="selectedClients.includes(client.id)"
                    @change="toggleClientSelection(client.id)"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ client.name }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      {{ client.formatted_document }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    getTypeColor(client.type)
                  ]">
                    {{ client.type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div v-if="client.email" class="text-sm text-gray-900 dark:text-gray-100">
                      {{ client.email }}
                    </div>
                    <div v-if="client.phone" class="text-sm text-gray-500 dark:text-gray-400">
                      {{ client.formatted_phone }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">
                    <div v-if="client.city">{{ client.city }}</div>
                    <div v-if="client.state" class="text-xs text-gray-500 dark:text-gray-400">
                      {{ client.state }}
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    getStatusColor(client.status)
                  ]">
                    {{ client.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                    <Calendar class="h-4 w-4" />
                    {{ new Date(client.created_at).toLocaleDateString('pt-BR') }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <Link
                      :href="`/clients/${client.id}`"
                      class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                      title="Visualizar"
                    >
                      <Eye class="h-4 w-4" />
                    </Link>
                    <Link
                      :href="`/clients/${client.id}/edit`"
                      class="p-1 text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors"
                      title="Editar"
                    >
                      <Edit class="h-4 w-4" />
                    </Link>
                    <button
                      class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                      title="Mais opções"
                    >
                      <MoreHorizontal class="h-4 w-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="!clients.data.length" class="text-center py-12">
          <Users class="h-16 w-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
            Nenhum cliente encontrado
          </h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6">
            Comece adicionando seu primeiro cliente ao sistema.
          </p>
          <Link
            href="/clients/create"
            class="devnity-button-primary inline-flex items-center gap-2"
          >
            <Plus class="h-4 w-4" />
            Adicionar Cliente
          </Link>
        </div>

        <!-- Pagination -->
        <div v-if="clients.data.length && clients.links?.length" class="px-6 py-4 border-t border-gray-200 dark:border-gray-800">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Mostrando {{ clients.meta?.from || 1 }} a {{ clients.meta?.to || clients.data.length }} de {{ clients.meta?.total || clients.data.length }} resultados
            </div>
            <div class="flex items-center gap-1">
              <Link
                v-for="link in clients.links"
                :key="link.label"
                :href="link.url || '#'"
                v-html="link.label"
                :class="[
                  'px-3 py-2 text-sm border rounded transition-colors',
                  link.active
                    ? 'bg-blue-50 dark:bg-blue-950/20 border-blue-300 dark:border-blue-700 text-blue-600 dark:text-blue-400'
                    : 'border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800',
                  !link.url && 'opacity-50 cursor-not-allowed'
                ]"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
