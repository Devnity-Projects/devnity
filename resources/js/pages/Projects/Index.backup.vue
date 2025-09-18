<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ProjectStats from '@/components/Projects/ProjectStats.vue'
import ProjectCard from '@/components/Projects/ProjectCard.vue'
import BulkActions from '@/components/Projects/BulkActions.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import { 
  FolderKanban,
  Plus,
  Search,
  Filter,
  Download,
  ArrowUpDown,
  ArrowUp,
  ArrowDown,
  RotateCcw
} from 'lucide-vue-next'

interface Client {
  id: number
  name: string
}

interface Project {
  id: number
  name: string
  description: string | null
  client: Client
  status: string
  priority: string
  type: string
  budget: number | null
  hours_estimated: number | null
  hours_worked: number | null
  start_date: string | null
  end_date: string | null
  deadline: string | null
  created_at: string
  status_label: string
  priority_label: string
  type_label: string
  progress_percentage: string
  is_overdue: boolean
  technologies: string[]
  tasks_count?: number
}

interface Props {
  projects: {
    data: Project[]
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
  clients: Client[]
  filters: {
    search?: string
    status?: string
    priority?: string
    client_id?: string
    type?: string
    sort_by?: string
    sort_direction?: string
  }
  stats: {
    total: number
    active: number
    completed: number
    overdue: number
    planning: number
    in_progress: number
  }
  statuses: Record<string, string>
  priorities: Record<string, string>
  types: Record<string, string>
}

const props = defineProps<Props>()

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const priority = ref(props.filters.priority || '')
const type = ref(props.filters.type || '')
const clientId = ref(props.filters.client_id || '')
const sortBy = ref(props.filters.sort_by || 'created_at')
const sortDirection = ref(props.filters.sort_direction || 'desc')
const selectedProjects = ref<number[]>([])
const showDeleteModal = ref(false)
const projectToDelete = ref<Project | null>(null)
const showFilters = ref(false)

const statusConfig: Record<string, { icon: any, color: string, bg: string }> = {
  planning: { icon: Clock, color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-100 dark:bg-blue-900/20' },
  in_progress: { icon: PlayCircle, color: 'text-yellow-600 dark:text-yellow-400', bg: 'bg-yellow-100 dark:bg-yellow-900/20' },
  completed: { icon: CheckCircle, color: 'text-green-600 dark:text-green-400', bg: 'bg-green-100 dark:bg-green-900/20' },
  cancelled: { icon: X, color: 'text-red-600 dark:text-red-400', bg: 'bg-red-100 dark:bg-red-900/20' },
  on_hold: { icon: Pause, color: 'text-gray-600 dark:text-gray-400', bg: 'bg-gray-100 dark:bg-gray-800' }
}

const priorityConfig: Record<string, { color: string, bg: string }> = {
  low: { color: 'text-green-600 dark:text-green-400', bg: 'bg-green-100 dark:bg-green-900/20' },
  medium: { color: 'text-yellow-600 dark:text-yellow-400', bg: 'bg-yellow-100 dark:bg-yellow-900/20' },
  high: { color: 'text-orange-600 dark:text-orange-400', bg: 'bg-orange-100 dark:bg-orange-900/20' },
  urgent: { color: 'text-red-600 dark:text-red-400', bg: 'bg-red-100 dark:bg-red-900/20' }
}

// Computed for select all functionality
const isAllSelected = computed(() => 
  props.projects.data.length > 0 && selectedProjects.value.length === props.projects.data.length
)

const isIndeterminate = computed(() => 
  selectedProjects.value.length > 0 && selectedProjects.value.length < props.projects.data.length
)

// Quick stats
const quickStats = computed(() => [
  {
    label: 'Total',
    value: props.stats.total,
    icon: FolderKanban,
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
    label: 'Concluídos',
    value: props.stats.completed,
    icon: CheckCircle,
    color: 'text-purple-600 dark:text-purple-400',
    bgColor: 'bg-purple-50 dark:bg-purple-950/20'
  },
  {
    label: 'Atrasados',
    value: props.stats.overdue,
    icon: AlertCircle,
    color: 'text-red-600 dark:text-red-400',
    bgColor: 'bg-red-50 dark:bg-red-950/20'
  }
])

// Check if there are active filters
const hasActiveFilters = computed(() => {
  return !!(search.value || status.value || priority.value || type.value || clientId.value)
})

function applyFilters() {
  router.get('/projects', {
    search: search.value || undefined,
    status: status.value || undefined,
    priority: priority.value || undefined,
    type: type.value || undefined,
    client_id: clientId.value || undefined,
    sort_by: sortBy.value,
    sort_direction: sortDirection.value,
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

function clearFilters() {
  search.value = ''
  status.value = ''
  priority.value = ''
  type.value = ''
  clientId.value = ''
  applyFilters()
}

function sortByField(field: string) {
  if (sortBy.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortDirection.value = 'asc'
  }
  applyFilters()
}

function getSortIcon(field: string) {
  if (sortBy.value !== field) return ArrowUpDown
  return sortDirection.value === 'asc' ? ArrowUp : ArrowDown
}

function toggleSelectAll() {
  if (isAllSelected.value) {
    selectedProjects.value = []
  } else {
    selectedProjects.value = props.projects.data.map(project => project.id)
  }
}

function toggleProjectSelection(projectId: number) {
  const index = selectedProjects.value.indexOf(projectId)
  if (index > -1) {
    selectedProjects.value.splice(index, 1)
  } else {
    selectedProjects.value.push(projectId)
  }
}

function clearSelection() {
  selectedProjects.value = []
}

function deleteProject(project: Project) {
  projectToDelete.value = project
  showDeleteModal.value = true
}

function confirmDelete() {
  if (projectToDelete.value) {
    router.delete(`/projects/${projectToDelete.value.id}`, {
      preserveScroll: true,
      onFinish: () => {
        showDeleteModal.value = false
        projectToDelete.value = null
      }
    })
  }
}

function bulkDelete() {
  if (selectedProjects.value.length > 0) {
    router.delete('/projects/bulk-destroy', {
      data: { ids: selectedProjects.value },
      preserveScroll: true,
      onSuccess: () => {
        selectedProjects.value = []
      }
    })
  }
}

function formatDate(dateString: string | null): string {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('pt-BR')
}

function formatCurrency(value: number | null): string {
  if (!value) return '-'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}
</script>

<template>
  <AppLayout title="Projetos">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <FolderKanban class="h-8 w-8 text-blue-600 dark:text-blue-400" />
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
              Projetos
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
              Gerencie seus projetos e acompanhe o progresso
            </p>
          </div>
        </div>
        
        <div class="flex items-center gap-3">
          <button
            @click="showFilters = !showFilters"
            :class="[
              'devnity-button-secondary inline-flex items-center gap-2',
              hasActiveFilters ? 'ring-2 ring-blue-500 ring-offset-2' : ''
            ]"
          >
            <Filter class="h-4 w-4" />
            Filtros
          </button>
          
          <Link
            href="/projects/create"
            class="devnity-button-primary inline-flex items-center gap-2"
          >
            <Plus class="h-4 w-4" />
            Novo Projeto
          </Link>
        </div>
      </div>

      <!-- Quick Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div
          v-for="stat in quickStats"
          :key="stat.label"
          :class="[
            'flex items-center gap-3 p-4 rounded-lg border border-gray-200 dark:border-gray-700',
            stat.bgColor
          ]"
        >
          <div class="p-2 rounded-lg bg-white dark:bg-gray-800">
            <component :is="stat.icon" :class="['h-5 w-5', stat.color]" />
          </div>
          <div>
            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
              {{ stat.value }}
            </div>
            <div class="text-xs text-gray-600 dark:text-gray-400">
              {{ stat.label }}
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div v-if="showFilters" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid md:grid-cols-5 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Buscar
            </label>
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
              <input
                v-model="search"
                type="text"
                placeholder="Nome ou descrição..."
                class="devnity-input pl-10"
                @keyup.enter="applyFilters"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Status
            </label>
            <select v-model="status" class="devnity-input">
              <option value="">Todos os status</option>
              <option v-for="(label, value) in props.statuses" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Prioridade
            </label>
            <select v-model="priority" class="devnity-input">
              <option value="">Todas as prioridades</option>
              <option v-for="(label, value) in props.priorities" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Cliente
            </label>
            <select v-model="clientId" class="devnity-input">
              <option value="">Todos os clientes</option>
              <option v-for="client in props.clients" :key="client.id" :value="client.id.toString()">
                {{ client.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tipo
            </label>
            <select v-model="type" class="devnity-input">
              <option value="">Todos os tipos</option>
              <option v-for="(label, value) in props.types" :key="value" :value="value">
                {{ label }}
              </option>
            </select>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <button @click="applyFilters" class="devnity-button-primary">
            Aplicar Filtros
          </button>
          <button @click="clearFilters" class="devnity-button-secondary">
            Limpar
          </button>
          <div v-if="hasActiveFilters" class="text-sm text-gray-600 dark:text-gray-400">
            {{ props.projects.meta?.total || 0 }} projeto(s) encontrado(s)
          </div>
        </div>
      </div>

      <!-- Projects Grid -->
      <div class="grid lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <div
          v-for="project in props.projects.data"
          :key="project.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md dark:hover:shadow-gray-900/25 transition-shadow duration-200"
        >
          <!-- Project Header -->
          <div class="p-6">
            <div class="flex items-start justify-between mb-4">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                  {{ project.name }}
                </h3>
                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-2">
                  <Building class="h-4 w-4" />
                  {{ project.client.name }}
                </div>
              </div>
              
              <div class="flex items-center gap-2">
                <input
                  type="checkbox"
                  :checked="selectedProjects.includes(project.id)"
                  @change="toggleProjectSelection(project.id)"
                  class="devnity-checkbox"
                />
                <div class="relative">
                  <button class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <MoreVertical class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>

            <!-- Status e Prioridade -->
            <div class="flex items-center gap-2 mb-4">
              <span :class="[
                'inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium',
                statusConfig[project.status]?.bg || 'bg-gray-100 dark:bg-gray-800',
                statusConfig[project.status]?.color || 'text-gray-600 dark:text-gray-400'
              ]">
                <component :is="statusConfig[project.status]?.icon || Clock" class="h-3 w-3" />
                {{ project.status_label }}
              </span>
              <span :class="[
                'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                priorityConfig[project.priority]?.bg || 'bg-gray-100 dark:bg-gray-800',
                priorityConfig[project.priority]?.color || 'text-gray-600 dark:text-gray-400'
              ]">
                {{ project.priority_label }}
              </span>
              <span v-if="project.is_overdue" class="inline-flex items-center gap-1 px-2 py-1 bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-400 rounded-full text-xs font-medium">
                <AlertCircle class="h-3 w-3" />
                Atrasado
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
              <div v-if="project.deadline" class="flex items-center gap-2">
                <Calendar class="h-4 w-4 text-gray-400" />
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  {{ formatDate(project.deadline) }}
                </span>
              </div>
            </div>

            <!-- Progress bar -->
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

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
              <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ formatDate(project.created_at) }}
              </div>
              
              <div class="flex items-center gap-2">
                <Link
                  :href="`/projects/${project.id}`"
                  class="p-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                  title="Visualizar"
                >
                  <Eye class="h-4 w-4" />
                </Link>
                
                <Link
                  :href="`/projects/${project.id}/edit`"
                  class="p-2 text-gray-400 hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors"
                  title="Editar"
                >
                  <Edit class="h-4 w-4" />
                </Link>
                
                <button
                  @click="deleteProject(project)"
                  class="p-2 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors"
                  title="Excluir"
                >
                  <Trash2 class="h-4 w-4" />
                </button>
              </div>
            </div>
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
      <div v-if="props.projects.meta && props.projects.meta.last_page && props.projects.meta.last_page > 1" class="flex justify-center">
        <div class="flex items-center gap-2">
          <Link
            v-for="page in Array.from({ length: props.projects.meta.last_page }, (_, i) => i + 1)"
            :key="page"
            :href="`/projects?page=${page}`"
            :class="[
              'px-3 py-2 rounded-lg text-sm',
              page === props.projects.meta.current_page
                ? 'bg-blue-600 text-white'
                : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
            ]"
          >
            {{ page }}
          </Link>
        </div>
      </div>

      <!-- Bulk actions -->
      <div v-if="selectedProjects.length > 0" class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-4">
          <div class="flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ selectedProjects.length }} projeto(s) selecionado(s)
            </span>
            
            <div class="flex items-center gap-2">
              <button
                @click="bulkDelete"
                class="devnity-button-danger inline-flex items-center gap-2 text-sm"
              >
                <Trash2 class="h-4 w-4" />
                Excluir
              </button>
              
              <button
                @click="clearSelection"
                class="devnity-button-secondary text-sm"
              >
                Cancelar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmação -->
    <ConfirmationModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
      title="Excluir Projeto"
      :message="projectToDelete ? `Tem certeza que deseja excluir o projeto '${projectToDelete.name}'? Esta ação não pode ser desfeita.` : ''"
      confirmText="Sim, excluir"
      cancelText="Cancelar"
      variant="danger"
    />
  </AppLayout>
</template>
