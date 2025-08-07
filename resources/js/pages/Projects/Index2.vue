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
const clientId = ref(props.filters.client_id || '')
const type = ref(props.filters.type || '')
const sortBy = ref(props.filters.sort_by || 'created_at')
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
  clientId.value = ''
  type.value = ''
  applyFilters()
}

function sortBy(field: string) {
  if (sortBy.value === field) {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
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
  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <div class="flex items-center gap-3">
          <FolderKanban class="h-8 w-8 text-blue-600 dark:text-blue-400" />
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Projetos</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Gerencie todos os seus projetos
            </p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <button
            @click="clearFilters"
            v-if="hasActiveFilters"
            class="devnity-button-secondary inline-flex items-center gap-2"
          >
            <RotateCcw class="h-4 w-4" />
            Limpar Filtros
          </button>

          <Link
            :href="route('projects.create')"
            class="devnity-button-primary inline-flex items-center gap-2"
          >
            <Plus class="h-4 w-4" />
            Novo Projeto
          </Link>
        </div>
      </div>
    </template>

    <div class="space-y-6">
      <!-- Estatísticas -->
      <ProjectStats :stats="props.stats" />

      <!-- Ações em lote -->
      <BulkActions
        v-if="selectedProjects.length > 0"
        :selected-ids="selectedProjects"
        :on-clear-selection="clearSelection"
      />

      <!-- Filtros e busca -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex flex-col lg:flex-row lg:items-center gap-4">
          <!-- Busca -->
          <div class="flex-1">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
              <input
                v-model="search"
                @input="applyFilters"
                type="text"
                placeholder="Buscar projetos..."
                class="devnity-input pl-10"
              />
            </div>
          </div>

          <!-- Filtros -->
          <div class="flex items-center gap-3">
            <button
              @click="showFilters = !showFilters"
              class="devnity-button-secondary inline-flex items-center gap-2"
            >
              <Filter class="h-4 w-4" />
              Filtros
            </button>

            <!-- Ordenação -->
            <div class="flex items-center gap-1 text-sm">
              <span class="text-gray-600 dark:text-gray-400">Ordenar:</span>
              <button
                @click="sortBy('name')"
                :class="[
                  'inline-flex items-center gap-1 px-2 py-1 rounded text-xs hover:bg-gray-100 dark:hover:bg-gray-700',
                  sortBy === 'name' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400'
                ]"
              >
                Nome
                <component 
                  :is="sortBy === 'name' && sortDirection === 'desc' ? ArrowDown : ArrowUp" 
                  class="h-3 w-3" 
                  v-if="sortBy === 'name'"
                />
              </button>
              <button
                @click="sortBy('deadline')"
                :class="[
                  'inline-flex items-center gap-1 px-2 py-1 rounded text-xs hover:bg-gray-100 dark:hover:bg-gray-700',
                  sortBy === 'deadline' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400' : 'text-gray-600 dark:text-gray-400'
                ]"
              >
                Prazo
                <component 
                  :is="sortBy === 'deadline' && sortDirection === 'desc' ? ArrowDown : ArrowUp" 
                  class="h-3 w-3" 
                  v-if="sortBy === 'deadline'"
                />
              </button>
            </div>
          </div>
        </div>

        <!-- Filtros expandidos -->
        <div v-show="showFilters" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Status
              </label>
              <select
                v-model="status"
                @change="applyFilters"
                class="devnity-select"
              >
                <option value="">Todos</option>
                <option v-for="(label, value) in statuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Prioridade
              </label>
              <select
                v-model="priority"
                @change="applyFilters"
                class="devnity-select"
              >
                <option value="">Todas</option>
                <option v-for="(label, value) in priorities" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Cliente
              </label>
              <select
                v-model="clientId"
                @change="applyFilters"
                class="devnity-select"
              >
                <option value="">Todos</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Tipo
              </label>
              <select
                v-model="type"
                @change="applyFilters"
                class="devnity-select"
              >
                <option value="">Todos</option>
                <option v-for="(label, value) in types" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Seleção em massa -->
      <div v-if="props.projects.data.length > 0" class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
        <label class="flex items-center gap-2">
          <input
            type="checkbox"
            :checked="isAllSelected"
            :indeterminate="isIndeterminate"
            @change="toggleSelectAll"
            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
          />
          Selecionar todos
        </label>
        <span v-if="selectedProjects.length > 0">
          {{ selectedProjects.length }} projeto(s) selecionado(s)
        </span>
      </div>

      <!-- Lista de projetos -->
      <div v-if="props.projects.data.length > 0" class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        <ProjectCard
          v-for="project in props.projects.data"
          :key="project.id"
          :project="project"
          :is-selected="selectedProjects.includes(project.id)"
          :on-toggle-select="toggleProjectSelection"
          :on-delete="deleteProject"
        />
      </div>

      <!-- Empty state -->
      <div v-else class="text-center py-12">
        <FolderKanban class="h-16 w-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
          Nenhum projeto encontrado
        </h3>
        <p class="text-gray-500 dark:text-gray-400 mb-6">
          {{ hasActiveFilters ? 'Tente ajustar os filtros ou' : 'Comece criando seu primeiro projeto.' }}
        </p>
        <Link
          :href="route('projects.create')"
          class="devnity-button-primary inline-flex items-center gap-2"
        >
          <Plus class="h-4 w-4" />
          Novo Projeto
        </Link>
      </div>

      <!-- Paginação -->
      <div v-if="props.projects.meta && props.projects.meta.last_page && props.projects.meta.last_page > 1" class="flex justify-center">
        <div class="flex items-center gap-2">
          <template v-for="link in props.projects.links" :key="link.label">
            <Link
              v-if="link.url"
              :href="link.url"
              :class="[
                'px-3 py-2 rounded-lg text-sm',
                link.active
                  ? 'bg-blue-600 text-white'
                  : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'
              ]"
              v-html="link.label"
            />
            <span
              v-else
              :class="[
                'px-3 py-2 rounded-lg text-sm text-gray-400 dark:text-gray-600 cursor-not-allowed'
              ]"
              v-html="link.label"
            />
          </template>
        </div>
      </div>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <ConfirmationModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="confirmDelete"
      title="Excluir Projeto"
      :message="`Tem certeza que deseja excluir o projeto '${projectToDelete?.name}'? Esta ação não pode ser desfeita.`"
      confirm-text="Excluir"
      cancel-text="Cancelar"
    />
  </AppLayout>
</template>
