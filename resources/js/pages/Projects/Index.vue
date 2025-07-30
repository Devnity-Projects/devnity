<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import {
  FolderKanban,
  Plus,
  Search,
  Filter,
  MoreVertical,
  Eye,
  Edit,
  Trash2,
  Users,
  Calendar,
  DollarSign,
  Clock,
  AlertCircle,
  CheckCircle,
  Pause,
  X
} from 'lucide-vue-next'

interface Client {
  id: number
  name: string
}

interface Project {
  id: number
  name: string
  description: string
  client: Client
  status: string
  priority: string
  type: string
  budget: number
  hours_estimated: number
  hours_worked: number
  start_date: string
  end_date: string
  deadline: string
  created_at: string
  tasks_count?: number
}

interface PaginatedProjects {
  data: Project[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

const props = defineProps<{
  projects: PaginatedProjects
  clients: Client[]
  filters: Record<string, any>
  statuses: Record<string, string>
  priorities: Record<string, string>
}>()

const searchTerm = ref(props.filters.search || '')
const selectedStatus = ref(props.filters.status || '')
const selectedPriority = ref(props.filters.priority || '')
const selectedClient = ref(props.filters.client_id || '')
const showFilters = ref(false)

const statusConfig: Record<string, { icon: any, color: string, bg: string, label: string }> = {
  planning: { icon: Clock, color: 'text-blue-600', bg: 'bg-blue-100', label: 'Planejamento' },
  in_progress: { icon: AlertCircle, color: 'text-yellow-600', bg: 'bg-yellow-100', label: 'Em Progresso' },
  completed: { icon: CheckCircle, color: 'text-green-600', bg: 'bg-green-100', label: 'Concluído' },
  cancelled: { icon: X, color: 'text-red-600', bg: 'bg-red-100', label: 'Cancelado' },
  on_hold: { icon: Pause, color: 'text-gray-600', bg: 'bg-gray-100', label: 'Em Espera' }
}

const priorityConfig: Record<string, { color: string, bg: string, label: string }> = {
  low: { color: 'text-green-600', bg: 'bg-green-100', label: 'Baixa' },
  medium: { color: 'text-yellow-600', bg: 'bg-yellow-100', label: 'Média' },
  high: { color: 'text-orange-600', bg: 'bg-orange-100', label: 'Alta' },
  urgent: { color: 'text-red-600', bg: 'bg-red-100', label: 'Urgente' }
}

const applyFilters = () => {
  router.get('/projects', {
    search: searchTerm.value,
    status: selectedStatus.value,
    priority: selectedPriority.value,
    client_id: selectedClient.value,
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  searchTerm.value = ''
  selectedStatus.value = ''
  selectedPriority.value = ''
  selectedClient.value = ''
  router.get('/projects')
}

const deleteProject = (project: Project) => {
  if (confirm(`Tem certeza que deseja excluir o projeto "${project.name}"?`)) {
    router.delete(`/projects/${project.id}`)
  }
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const formatDate = (date: string) => {
  return date ? new Date(date).toLocaleDateString('pt-BR') : '-'
}

const hasActiveFilters = computed(() => {
  return searchTerm.value || selectedStatus.value || selectedPriority.value || selectedClient.value
})
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 flex items-center gap-3">
            <FolderKanban class="h-8 w-8 text-blue-600" />
            Projetos
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">
            {{ props.projects.total }} projeto{{ props.projects.total !== 1 ? 's' : '' }} encontrado{{ props.projects.total !== 1 ? 's' : '' }}
          </p>
        </div>
        <Link 
          href="/projects/create"
          class="devnity-button-primary flex items-center gap-2"
        >
          <Plus class="h-4 w-4" />
          Novo Projeto
        </Link>
      </div>

      <!-- Filtros -->
      <div class="devnity-card p-6">
        <div class="flex flex-col lg:flex-row gap-4">
          <!-- Busca -->
          <div class="flex-1 relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              v-model="searchTerm"
              @keyup.enter="applyFilters"
              type="text"
              placeholder="Buscar projetos..."
              class="pl-10 w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>

          <!-- Toggle filtros -->
          <button
            @click="showFilters = !showFilters"
            class="flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
          >
            <Filter class="h-4 w-4" />
            Filtros
            <span v-if="hasActiveFilters" class="bg-blue-600 text-white text-xs px-2 py-0.5 rounded-full">
              Ativo
            </span>
          </button>

          <!-- Botões de ação -->
          <div class="flex gap-2">
            <button
              @click="applyFilters"
              class="devnity-button-primary"
            >
              Buscar
            </button>
            <button
              v-if="hasActiveFilters"
              @click="clearFilters"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
            >
              Limpar
            </button>
          </div>
        </div>

        <!-- Filtros expandidos -->
        <div v-if="showFilters" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Status
              </label>
              <select
                v-model="selectedStatus"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Todos os status</option>
                <option v-for="(label, key) in props.statuses" :key="key" :value="key">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Prioridade
              </label>
              <select
                v-model="selectedPriority"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Todas as prioridades</option>
                <option v-for="(label, key) in props.priorities" :key="key" :value="key">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Cliente
              </label>
              <select
                v-model="selectedClient"
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Todos os clientes</option>
                <option v-for="client in props.clients" :key="client.id" :value="client.id">
                  {{ client.name }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Lista de Projetos -->
      <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <div
          v-for="project in props.projects.data"
          :key="project.id"
          class="devnity-card p-6 hover:shadow-lg transition-all duration-200 group"
        >
          <!-- Header do card -->
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                <FolderKanban class="h-5 w-5 text-blue-600 dark:text-blue-400" />
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-gray-900 dark:text-gray-100 truncate">
                  {{ project.name }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1">
                  <Users class="h-3 w-3" />
                  {{ project.client.name }}
                </p>
              </div>
            </div>

            <!-- Menu de ações -->
            <div class="relative group/menu">
              <button class="p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 opacity-0 group-hover:opacity-100 transition-all">
                <MoreVertical class="h-4 w-4 text-gray-400" />
              </button>
              <div class="absolute right-0 top-8 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 min-w-[120px] opacity-0 invisible group-hover/menu:opacity-100 group-hover/menu:visible transition-all z-10">
                <Link
                  :href="`/projects/${project.id}`"
                  class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <Eye class="h-3 w-3" />
                  Ver
                </Link>
                <Link
                  :href="`/projects/${project.id}/edit`"
                  class="flex items-center gap-2 px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                >
                  <Edit class="h-3 w-3" />
                  Editar
                </Link>
                <button
                  @click="deleteProject(project)"
                  class="flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 w-full text-left"
                >
                  <Trash2 class="h-3 w-3" />
                  Excluir
                </button>
              </div>
            </div>
          </div>

          <!-- Status e Prioridade -->
          <div class="flex items-center gap-2 mb-4">
            <span :class="[
              'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium',
              statusConfig[project.status]?.bg || 'bg-gray-100',
              statusConfig[project.status]?.color || 'text-gray-600'
            ]">
              <component :is="statusConfig[project.status]?.icon || Clock" class="h-3 w-3" />
              {{ statusConfig[project.status]?.label || project.status }}
            </span>
            <span :class="[
              'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
              priorityConfig[project.priority]?.bg || 'bg-gray-100',
              priorityConfig[project.priority]?.color || 'text-gray-600'
            ]">
              {{ priorityConfig[project.priority]?.label || project.priority }}
            </span>
          </div>

          <!-- Descrição -->
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
            {{ project.description || 'Sem descrição' }}
          </p>

          <!-- Métricas -->
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div v-if="project.budget" class="flex items-center gap-2">
              <DollarSign class="h-4 w-4 text-gray-400" />
              <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ formatCurrency(project.budget) }}
              </span>
            </div>
            <div class="flex items-center gap-2">
              <Calendar class="h-4 w-4 text-gray-400" />
              <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ formatDate(project.deadline) }}
              </span>
            </div>
          </div>

          <!-- Progress bar (exemplo) -->
          <div class="mb-4">
            <div class="flex justify-between items-center mb-1">
              <span class="text-xs text-gray-500 dark:text-gray-400">Progresso</span>
              <span class="text-xs text-gray-500 dark:text-gray-400">
                {{ project.hours_worked || 0 }}h / {{ project.hours_estimated || 0 }}h
              </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
              <div 
                class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                :style="{ 
                  width: project.hours_estimated ? 
                    `${Math.min(100, (project.hours_worked || 0) / project.hours_estimated * 100)}%` : 
                    '0%' 
                }"
              ></div>
            </div>
          </div>

          <!-- Footer -->
          <div class="text-xs text-gray-500 dark:text-gray-400">
            Criado em {{ formatDate(project.created_at) }}
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="props.projects.data.length === 0" class="text-center py-12">
        <FolderKanban class="h-16 w-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
          Nenhum projeto encontrado
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">
          {{ hasActiveFilters ? 'Tente ajustar os filtros ou' : 'Comece criando seu primeiro projeto.' }}
        </p>
        <Link
          href="/projects/create"
          class="devnity-button-primary inline-flex items-center gap-2"
        >
          <Plus class="h-4 w-4" />
          Novo Projeto
        </Link>
      </div>

      <!-- Paginação -->
      <div v-if="props.projects.last_page > 1" class="flex justify-center">
        <div class="flex items-center gap-2">
          <Link
            v-for="page in Array.from({ length: props.projects.last_page }, (_, i) => i + 1)"
            :key="page"
            :href="`/projects?page=${page}`"
            :class="[
              'px-3 py-2 rounded-lg text-sm',
              page === props.projects.current_page
                ? 'bg-blue-600 text-white'
                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            {{ page }}
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
