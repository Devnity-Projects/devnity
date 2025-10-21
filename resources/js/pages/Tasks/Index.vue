<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import { 
  ListTodo,
  Plus,
  Search,
  Filter,
  Eye,
  Edit,
  Trash2,
  Calendar,
  Clock,
  User,
  FolderOpen,
  AlertCircle,
  Flag,
  Kanban,
  RotateCcw
} from 'lucide-vue-next'

interface User {
  id: number
  name: string
}

interface Project {
  id: number
  name: string
}

interface Task {
  id: number
  title: string
  description: string | null
  project: Project
  assigned_user: User | null
  status: string
  priority: string
  type: string
  hours_estimated: number | null
  hours_worked: number | null
  due_date: string | null
  order: number
  labels: string[]
  is_overdue: boolean
  status_label: string
  priority_label: string
  type_label: string
  time_spent: string
  created_at: string
}

interface Props {
  tasks: {
    data: Task[]
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
  projects: Project[]
  users: User[]
  filters: {
    search?: string
    status?: string
    priority?: string
    project_id?: string
    assigned_to?: string
  }
  stats: {
    total: number
    todo: number
    in_progress: number
    review: number
    completed: number
    overdue: number
  }
  statuses: Record<string, string>
  priorities: Record<string, string>
  types: Record<string, string>
}

const props = defineProps<Props>()

// Reactive filters
const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const priority = ref(props.filters.priority || '')
const projectId = ref(props.filters.project_id || '')
const assignedTo = ref(props.filters.assigned_to || '')
const showFilters = ref(false)

// Modal states
const showDeleteModal = ref(false)
const taskToDelete = ref<Task | null>(null)

// Computed
const hasActiveFilters = computed(() => 
  search.value || status.value || priority.value || projectId.value || assignedTo.value
)

// Methods
function applyFilters() {
  router.get(route('tasks.index'), {
    search: search.value,
    status: status.value,
    priority: priority.value,
    project_id: projectId.value,
    assigned_to: assignedTo.value,
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

function clearFilters() {
  search.value = ''
  status.value = ''
  priority.value = ''
  projectId.value = ''
  assignedTo.value = ''
  applyFilters()
}

function deleteTask(task: Task) {
  taskToDelete.value = task
  showDeleteModal.value = true
}

function confirmDelete() {
  if (taskToDelete.value) {
    router.delete(route('tasks.destroy', taskToDelete.value.id), {
      preserveScroll: true,
      onFinish: () => {
        showDeleteModal.value = false
        taskToDelete.value = null
      }
    })
  }
}

function formatDate(date: string | null): string {
  if (!date) return 'N/A'
  return new Intl.DateTimeFormat('pt-BR').format(new Date(date))
}

function getPriorityColor(priority: string): string {
  const colors: Record<string, string> = {
    low: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
    urgent: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
  return colors[priority] || colors.medium
}

function getStatusColor(status: string): string {
  const colors: Record<string, string> = {
    todo: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    in_progress: 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
    review: 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
    completed: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
  }
  return colors[status] || colors.todo
}
</script>

<template>
  <AppLayout title="Tarefas">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
          <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
              Tarefas
            </h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
              <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                <ListTodo class="mr-1.5 h-5 w-5 flex-shrink-0" />
                {{ props.tasks.meta?.total || 0 }} tarefa(s) encontrada(s)
              </div>
            </div>
          </div>
          <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
            <Link
              :href="route('kanban.index')"
              class="inline-flex items-center gap-2 rounded-md bg-purple-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-purple-600 transition-colors"
            >
              <Kanban class="h-4 w-4" />
              Kanban Board
            </Link>
            <Link
              :href="route('tasks.create')"
              class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors"
            >
              <Plus class="h-4 w-4" />
              Nova Tarefa
            </Link>
          </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
              <div class="rounded-lg p-2 mr-3 bg-blue-50 dark:bg-blue-900/20">
                <ListTodo class="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Total</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ stats.total }}</p>
              </div>
            </div>
          </div>
          
          <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
              <div class="rounded-lg p-2 mr-3 bg-gray-50 dark:bg-gray-900/20">
                <Clock class="h-6 w-6 text-gray-600 dark:text-gray-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">A Fazer</p>
                <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ stats.todo }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
              <div class="rounded-lg p-2 mr-3 bg-yellow-50 dark:bg-yellow-900/20">
                <Clock class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Em Progresso</p>
                <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ stats.in_progress }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
              <div class="rounded-lg p-2 mr-3 bg-purple-50 dark:bg-purple-900/20">
                <Eye class="h-6 w-6 text-purple-600 dark:text-purple-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Em Revisão</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ stats.review }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
              <div class="rounded-lg p-2 mr-3 bg-green-50 dark:bg-green-900/20">
                <Eye class="h-6 w-6 text-green-600 dark:text-green-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Concluídas</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ stats.completed }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center">
              <div class="rounded-lg p-2 mr-3 bg-red-50 dark:bg-red-900/20">
                <AlertCircle class="h-6 w-6 text-red-600 dark:text-red-400" />
              </div>
              <div>
                <p class="text-sm font-medium text-gray-900 dark:text-white">Atrasadas</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ stats.overdue }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
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
                  placeholder="Buscar tarefas..."
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md leading-5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>
            
            <div class="flex items-center gap-2">
              <button
                @click="showFilters = !showFilters"
                class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                :class="{ 'bg-gray-50 dark:bg-gray-700': showFilters }"
              >
                <Filter class="h-4 w-4" />
                Filtros
                <span v-if="hasActiveFilters" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
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
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
              >
                <option value="">Todos</option>
                <option v-for="(label, value) in statuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prioridade</label>
              <select 
                v-model="priority"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
              >
                <option value="">Todas</option>
                <option v-for="(label, value) in priorities" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Projeto</label>
              <select 
                v-model="projectId"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
              >
                <option value="">Todos</option>
                <option v-for="project in projects" :key="project.id" :value="project.id.toString()">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Responsável</label>
              <select 
                v-model="assignedTo"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
              >
                <option value="">👥 Todos</option>
                <option value="unassigned">❌ Sem atribuição</option>
                <option v-for="user in users" :key="user.id" :value="user.id.toString()">
                  👤 {{ user.name }}
                </option>
              </select>
            </div>

            <div class="flex items-end">
              <button
                @click="applyFilters"
                class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
              >
                Aplicar Filtros
              </button>
            </div>
          </div>
        </div>

        <!-- Tasks Table -->
        <div v-if="tasks.data.length > 0" class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Tarefa
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Projeto
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Prioridade
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Responsável
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Prazo
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Ações
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr 
                  v-for="task in tasks.data" 
                  :key="task.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                  :class="{ 'bg-red-50 dark:bg-red-900/10': task.is_overdue }"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0">
                        <Flag class="h-5 w-5 text-gray-400" />
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ task.title }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                          {{ task.description }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <FolderOpen class="h-4 w-4 text-gray-400 mr-2" />
                      <span class="text-sm text-gray-900 dark:text-white">{{ task.project.name }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusColor(task.status)]">
                      {{ task.status_label }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getPriorityColor(task.priority)]">
                      {{ task.priority_label }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="task.assigned_user" class="flex items-center">
                      <User class="h-4 w-4 text-gray-400 mr-2" />
                      <span class="text-sm text-gray-900 dark:text-white">{{ task.assigned_user.name }}</span>
                    </div>
                    <div v-else class="flex items-center text-gray-400">
                      <User class="h-4 w-4 mr-2" />
                      <span class="text-sm">Não atribuído</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="task.due_date" class="flex items-center">
                      <Calendar class="h-4 w-4 mr-2" :class="task.is_overdue ? 'text-red-500' : 'text-gray-400'" />
                      <span class="text-sm" :class="task.is_overdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-900 dark:text-white'">
                        {{ formatDate(task.due_date) }}
                      </span>
                    </div>
                    <div v-else class="flex items-center text-gray-400">
                      <Calendar class="h-4 w-4 mr-2" />
                      <span class="text-sm">Sem prazo</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2">
                      <Link
                        :href="route('tasks.show', task.id)"
                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                        title="Visualizar"
                      >
                        <Eye class="h-4 w-4" />
                      </Link>
                      <Link
                        :href="route('tasks.edit', task.id)"
                        class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                        title="Editar"
                      >
                        <Edit class="h-4 w-4" />
                      </Link>
                      <button
                        @click="deleteTask(task)"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                        title="Excluir"
                      >
                        <Trash2 class="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div v-if="tasks.links && tasks.links.length > 3" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center text-sm text-gray-700 dark:text-gray-300">
                Mostrando {{ tasks.meta?.from || 0 }} a {{ tasks.meta?.to || 0 }} de {{ tasks.meta?.total || 0 }} resultados
              </div>
              <nav class="flex items-center space-x-1">
                <Link
                  v-for="link in tasks.links"
                  :key="link.label"
                  :href="link.url || ''"
                  preserve-scroll
                  preserve-state
                  :class="[
                    'px-3 py-2 text-sm font-medium rounded-md transition-colors',
                    link.active
                      ? 'bg-blue-600 text-white'
                      : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700'
                  ]"
                  v-html="link.label"
                />
              </nav>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-12">
          <ListTodo class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Nenhuma tarefa encontrada</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ hasActiveFilters ? 'Tente ajustar os filtros ou limpe-os para ver todas as tarefas.' : 'Comece criando sua primeira tarefa.' }}
          </p>
          <div class="mt-6">
            <Link
              v-if="!hasActiveFilters"
              :href="route('tasks.create')"
              class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors"
            >
              <Plus class="h-4 w-4" />
              Nova Tarefa
            </Link>
            <button
              v-else
              @click="clearFilters"
              class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
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
      title="Excluir Tarefa"
      :message="`Tem certeza que deseja excluir a tarefa '${taskToDelete?.title}'? Esta ação não pode ser desfeita.`"
      confirmText="Excluir"
      cancelText="Cancelar"
      type="danger"
    />
  </AppLayout>
</template>
