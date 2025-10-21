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
  client: Client | null
  status: string
  priority: string
  type: string
  budget: number | null
  hours_estimated: number | null
  hours_worked: number | null
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
const clientId = ref(props.filters.client_id || '')
const type = ref(props.filters.type || '')
const currentSortBy = ref(props.filters.sort_by || 'created_at')
const sortDirection = ref(props.filters.sort_direction || 'desc')
const showFilters = ref(false)
const selectedProjects = ref<number[]>([])
const showDeleteModal = ref(false)
const projectToDelete = ref<Project | null>(null)

// Computed for select all functionality
const isAllSelected = computed(() => 
  props.projects.data.length > 0 && selectedProjects.value.length === props.projects.data.length
)

const isIndeterminate = computed(() => 
  selectedProjects.value.length > 0 && selectedProjects.value.length < props.projects.data.length
)

const hasActiveFilters = computed(() => 
  search.value || status.value || priority.value || clientId.value || type.value
)

function applyFilters() {
  router.get(route('projects.index'), {
    search: search.value,
    status: status.value,
    priority: priority.value,
    client_id: clientId.value,
    type: type.value,
    sort_by: currentSortBy.value,
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
  clientId.value = ''
  type.value = ''
  applyFilters()
}

function sortByField(field: string) {
  if (currentSortBy.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    currentSortBy.value = field
    sortDirection.value = 'asc'
  }
  applyFilters()
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
    router.delete(route('projects.destroy', projectToDelete.value.id), {
      preserveScroll: true,
      onFinish: () => {
        showDeleteModal.value = false
        projectToDelete.value = null
        clearSelection()
      }
    })
  }
}
</script>

<template>
  <AppLayout title="Projetos">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
          <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
              Projetos
            </h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
              <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                <FolderKanban class="mr-1.5 h-5 w-5 flex-shrink-0" />
                {{ props.projects.meta?.total || 0 }} projeto(s) encontrado(s)
              </div>
            </div>
          </div>
          <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
            <Link
              :href="route('projects.create')"
              class="devnity-button-primary flex items-center gap-2"
            >
              <Plus class="h-4 w-4" />
              Novo Projeto
            </Link>
          </div>
        </div>

        <!-- Stats -->
        <ProjectStats :stats="props.stats" class="mb-8" />

        <!-- Filters and Actions -->
        <div class="mb-6">
          <!-- Search and Filter Toggle -->
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4">
            <div class="flex-1 max-w-md">
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <Search class="h-5 w-5 text-gray-400" />
                </div>
                <input
                  v-model="search"
                  @keyup.enter="applyFilters"
                  type="text"
                  placeholder="Buscar projetos..."
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500"
                />
              </div>
            </div>
            
            <div class="flex items-center gap-2">
              <button
                @click="showFilters = !showFilters"
                class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
                :class="{ 'bg-gray-50 dark:bg-gray-700': showFilters }"
              >
                <Filter class="h-4 w-4" />
                Filtros
                <span v-if="hasActiveFilters" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                  Ativos
                </span>
              </button>

              <button
                v-if="hasActiveFilters"
                @click="clearFilters"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors"
              >
                <RotateCcw class="h-4 w-4" />
                Limpar
              </button>
            </div>
          </div>

          <!-- Filter Panel -->
          <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
              <select 
                v-model="status"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
              >
                <option value="">Todos</option>
                <option v-for="(label, value) in props.statuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prioridade</label>
              <select 
                v-model="priority"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
              >
                <option value="">Todas</option>
                <option v-for="(label, value) in props.priorities" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cliente</label>
              <select 
                v-model="clientId"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
              >
                <option value="">Todos</option>
                <option value="personal">Projetos Pessoais</option>
                <option v-for="client in props.clients" :key="client.id" :value="client.id.toString()">
                  {{ client.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tipo</label>
              <select 
                v-model="type"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-primary-500 focus:border-primary-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
              >
                <option value="">Todos</option>
                <option v-for="(label, value) in props.types" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                @click="applyFilters"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
              >
                Aplicar Filtros
              </button>
            </div>
          </div>
        </div>

        <!-- Bulk Actions -->
        <BulkActions
          v-if="selectedProjects.length > 0"
          :selected-ids="selectedProjects"
          @clear-selection="clearSelection"
          class="mb-6"
        />

        <!-- Projects Grid -->
        <div v-if="props.projects.data.length > 0">
          <!-- Selection Header -->
          <div class="mb-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
              <label class="flex items-center">
                <input
                  :checked="isAllSelected"
                  :indeterminate="isIndeterminate"
                  @change="toggleSelectAll"
                  type="checkbox"
                  class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 dark:border-gray-600 rounded"
                />
                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                  {{ selectedProjects.length > 0 ? `${selectedProjects.length} selecionado(s)` : 'Selecionar todos' }}
                </span>
              </label>
            </div>

            <!-- Sort Options -->
            <div class="flex items-center space-x-2">
              <span class="text-sm text-gray-500 dark:text-gray-400">Ordenar por:</span>
              <button
                @click="sortByField('name')"
                class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                :class="{ 'text-primary-600 dark:text-primary-400': currentSortBy === 'name' }"
              >
                Nome
                <ArrowUp v-if="currentSortBy === 'name' && sortDirection === 'asc'" class="ml-1 h-3 w-3" />
                <ArrowDown v-else-if="currentSortBy === 'name' && sortDirection === 'desc'" class="ml-1 h-3 w-3" />
                <ArrowUpDown v-else class="ml-1 h-3 w-3 opacity-50" />
              </button>
              <button
                @click="sortByField('created_at')"
                class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                :class="{ 'text-primary-600 dark:text-primary-400': currentSortBy === 'created_at' }"
              >
                Data
                <ArrowUp v-if="currentSortBy === 'created_at' && sortDirection === 'asc'" class="ml-1 h-3 w-3" />
                <ArrowDown v-else-if="currentSortBy === 'created_at' && sortDirection === 'desc'" class="ml-1 h-3 w-3" />
                <ArrowUpDown v-else class="ml-1 h-3 w-3 opacity-50" />
              </button>
              <button
                @click="sortByField('status')"
                class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white"
                :class="{ 'text-primary-600 dark:text-primary-400': currentSortBy === 'status' }"
              >
                Status
                <ArrowUp v-if="currentSortBy === 'status' && sortDirection === 'asc'" class="ml-1 h-3 w-3" />
                <ArrowDown v-else-if="currentSortBy === 'status' && sortDirection === 'desc'" class="ml-1 h-3 w-3" />
                <ArrowUpDown v-else class="ml-1 h-3 w-3 opacity-50" />
              </button>
            </div>
          </div>

          <!-- Projects Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <ProjectCard
              v-for="project in props.projects.data"
              :key="project.id"
              :project="project"
              :is-selected="selectedProjects.includes(project.id)"
              @toggle-select="toggleProjectSelection"
              @delete="deleteProject"
            />
          </div>

          <!-- Pagination -->
          <div v-if="props.projects.links && props.projects.links.length > 3" class="mt-8">
            <nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 sm:px-0">
              <div class="-mt-px flex w-0 flex-1">
                <Link
                  v-for="link in props.projects.links"
                  :key="link.label"
                  :href="link.url || ''"
                  preserve-scroll
                  preserve-state
                  :class="[
                    'inline-flex items-center border-t-2 pt-4 pr-1 text-sm font-medium transition-colors',
                    link.active
                      ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                      : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'
                  ]"
                  v-html="link.label"
                />
              </div>
            </nav>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <FolderKanban class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nenhum projeto encontrado</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ hasActiveFilters ? 'Tente ajustar os filtros ou limpe-os para ver todos os projetos.' : 'Comece criando seu primeiro projeto.' }}
          </p>
          <div class="mt-6">
            <Link
              v-if="!hasActiveFilters"
              :href="route('projects.create')"
              class="devnity-button-primary flex items-center gap-2"
            >
              <Plus class="h-4 w-4" />
              Novo Projeto
            </Link>
            <button
              v-else
              @click="clearFilters"
              class="devnity-button-secondary flex items-center gap-2"
            >
              <RotateCcw class="h-4 w-4" />
              Limpar Filtros
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
      title="Excluir Projeto"
      :message="`Tem certeza que deseja excluir o projeto '${projectToDelete?.name}'? Esta ação não pode ser desfeita.`"
      confirmText="Excluir"
      cancelText="Cancelar"
      type="danger"
    />
  </AppLayout>
</template>
