<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import KanbanColumn from '@/components/Tasks/KanbanColumn.vue'
import KanbanStats from '@/components/Tasks/KanbanStats.vue'
import TaskQuickCreate from '@/components/Tasks/TaskQuickCreate.vue'
import { 
  Kanban,
  Plus,
  Search,
  Filter,
  Users,
  FolderKanban,
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

interface Column {
  title: string
  color: string
  tasks: Task[]
}

interface Props {
  columns: Record<string, Column>
  stats: {
    total: number
    todo: number
    in_progress: number
    review: number
    completed: number
    overdue: number
  }
  projects: Project[]
  users: User[]
  filters: {
    project_id?: string
    assigned_to?: string
    search?: string
    priority?: string
    type?: string
    overdue?: boolean
  }
  statuses: Record<string, string>
  priorities: Record<string, string>
  types: Record<string, string>
}

const props = defineProps<Props>()

// Reactive filters
const search = ref(props.filters.search || '')
const projectId = ref(props.filters.project_id || '')
const assignedTo = ref(props.filters.assigned_to || '')
const priority = ref(props.filters.priority || '')
const type = ref(props.filters.type || '')
const overdue = ref(props.filters.overdue || false)
const showFilters = ref(false)
const showQuickCreate = ref(false)

// Notification system
const notification = ref({
  show: false,
  message: '',
  type: 'success'
})

// Reactive columns for drag and drop
const columns = ref(props.columns)
const draggedTask = ref<Task | null>(null)

// Watch for changes in props.columns to update local state
watch(() => props.columns, (newColumns) => {
  columns.value = newColumns
}, { deep: true })

// Computed
const hasActiveFilters = computed(() => 
  search.value || projectId.value || assignedTo.value || priority.value || type.value || overdue.value
)

const columnKeys = computed(() => Object.keys(props.columns))

const activeFiltersDisplay = computed(() => {
  const filters = []
  if (search.value) filters.push(`Busca: "${search.value}"`)
  if (projectId.value) {
    const project = props.projects.find(p => p.id.toString() === projectId.value)
    filters.push(`Projeto: ${project?.name}`)
  }
  if (assignedTo.value) {
    if (assignedTo.value === 'unassigned') {
      filters.push('Responsável: Sem atribuição')
    } else {
      const user = props.users.find(u => u.id.toString() === assignedTo.value)
      filters.push(`Responsável: ${user?.name}`)
    }
  }
  if (priority.value) {
    filters.push(`Prioridade: ${props.priorities[priority.value]}`)
  }
  if (type.value) {
    filters.push(`Tipo: ${props.types[type.value]}`)
  }
  if (overdue.value) filters.push('Apenas em atraso')
  return filters
})

// Methods
function applyFilters() {
  router.get(route('kanban.index'), {
    search: search.value,
    project_id: projectId.value,
    assigned_to: assignedTo.value,
    priority: priority.value,
    type: type.value,
    overdue: overdue.value ? '1' : '',
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

function clearFilters() {
  search.value = ''
  projectId.value = ''
  assignedTo.value = ''
  priority.value = ''
  type.value = ''
  overdue.value = false
  applyFilters()
}

function handleTaskMove(taskId: number, newStatus: string, newOrder: number) {
  // Optimistic update - update the task in the UI immediately
  const task = findTaskById(taskId)
  
  if (task) {
    // Remove from old column
    const oldColumn = columns.value[task.status]
    const taskIndex = oldColumn.tasks.findIndex(t => t.id === taskId)
    
    if (taskIndex > -1) {
      oldColumn.tasks.splice(taskIndex, 1)
    }

    // Add to new column
    task.status = newStatus
    task.order = newOrder
    const newColumn = columns.value[newStatus]
    newColumn.tasks.splice(newOrder, 0, task)
  }

  // Send update to server
  router.patch(route('kanban.update-status', taskId), {
    status: newStatus,
    order: newOrder
  }, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
      // Show success notification
      showSuccessNotification('Tarefa movida com sucesso!')
    },
    onError: (errors) => {
      console.log('❌ Server update failed:', errors)
      // If error, reload the page to get correct state
      router.reload()
    }
  })
}

function findTaskById(taskId: number): Task | null {
  for (const column of Object.values(columns.value)) {
    const task = column.tasks.find(t => t.id === taskId)
    if (task) return task
  }
  return null
}

function handleTaskCreated(newTask: Task) {
  // Add task to appropriate column
  const column = columns.value[newTask.status]
  if (column) {
    column.tasks.unshift(newTask)
  }
  showQuickCreate.value = false
}

// Auto-apply filters with debouncing for search
let debounceTimer: number

// Notification functions
function showSuccessNotification(message: string) {
  notification.value = {
    show: true,
    message,
    type: 'success'
  }
  
  // Auto-hide after 3 seconds
  setTimeout(() => {
    notification.value.show = false
  }, 3000)
}

// Drag and Drop handlers
function handleDragStart(task: Task) {
  draggedTask.value = task
}

function handleDragEnd() {
  draggedTask.value = null
}
watch([search], () => {
  clearTimeout(debounceTimer)
  debounceTimer = setTimeout(() => {
    applyFilters()
  }, 300)
})

// Auto-apply filters immediately for select fields
watch([projectId, assignedTo, priority, type, overdue], () => {
  applyFilters()
})
</script>

<template>
  <AppLayout title="Kanban - Tarefas">
    <div class="py-6">
      <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
          <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
              <Kanban class="inline-block mr-3 h-8 w-8 text-blue-600 dark:text-blue-400" />
              Kanban Board
            </h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
              <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                <FolderKanban class="mr-1.5 h-5 w-5 flex-shrink-0" />
                {{ stats.total }} tarefa(s) no board
              </div>
            </div>
          </div>
          <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
            <button
              @click="showQuickCreate = true"
              class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors"
            >
              <Plus class="h-4 w-4" />
              Nova Tarefa
            </button>
          </div>
        </div>

        <!-- Stats -->
        <KanbanStats :stats="stats" class="mb-8" />

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
                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                :class="{ 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600 text-blue-700 dark:text-blue-300': showFilters || hasActiveFilters }"
              >
                <Filter class="h-4 w-4" />
                Filtros
                <span v-if="hasActiveFilters" class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-blue-600 dark:bg-blue-500 rounded-full">
                  {{ [search, projectId, assignedTo, priority, type, overdue].filter(Boolean).length }}
                </span>
              </button>

              <button
                v-if="hasActiveFilters"
                @click="clearFilters"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                title="Limpar todos os filtros"
              >
                <RotateCcw class="h-4 w-4" />
                Limpar
              </button>
            </div>
          </div>

          <!-- Filter Panel -->
          <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Projeto</label>
              <select 
                v-model="projectId"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm transition-colors"
              >
                <option value="">Todos os projetos</option>
                <option v-for="project in projects" :key="project.id" :value="project.id.toString()">
                  {{ project.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Responsável</label>
              <select 
                v-model="assignedTo"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm transition-colors"
              >
                <option value="">👥 Todos os usuários</option>
                <option value="unassigned">❌ Sem atribuição</option>
                <option v-for="user in users" :key="user.id" :value="user.id.toString()">
                  👤 {{ user.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prioridade</label>
              <select 
                v-model="priority"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm transition-colors"
              >
                <option value="">Todas as prioridades</option>
                <option v-for="(label, value) in priorities" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tipo</label>
              <select 
                v-model="type"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm transition-colors"
              >
                <option value="">Todos os tipos</option>
                <option v-for="(label, value) in types" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>

            <div class="flex flex-col justify-center">
              <label class="flex items-center cursor-pointer">
                <input 
                  type="checkbox"
                  v-model="overdue"
                  class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 transition-colors"
                />
                <span class="ml-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                  Apenas atrasadas
                </span>
              </label>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 ml-7">
                Mostrar somente tarefas vencidas
              </p>
            </div>
          </div>
        </div>

        <!-- Kanban Board -->
        <div class="flex gap-6 overflow-x-auto pb-6">
          <KanbanColumn
            v-for="(column, status) in columns"
            :key="status"
            :title="column.title"
            :color="column.color"
            :status="status"
            :tasks="column.tasks"
            :dragged-task="draggedTask"
            @task-move="handleTaskMove"
            @drag-start="handleDragStart"
            @drag-end="handleDragEnd"
            class="flex-shrink-0 w-80"
          />
        </div>
      </div>
    </div>

    <!-- Quick Create Modal -->
    <TaskQuickCreate
      v-if="showQuickCreate"
      :projects="projects"
      :statuses="statuses"
      :priorities="priorities"
      @close="showQuickCreate = false"
      @created="handleTaskCreated"
    />
    
    <!-- Success Notification -->
    <Transition
      enter-active-class="transition ease-out duration-300"
      enter-from-class="opacity-0 transform translate-y-2"
      enter-to-class="opacity-100 transform translate-y-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100 transform translate-y-0"
      leave-to-class="opacity-0 transform translate-y-2"
    >
      <div
        v-if="notification.show"
        class="fixed top-4 right-4 z-50 max-w-sm w-full bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3"
      >
        <div class="flex-shrink-0">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="flex-1">
          <p class="text-sm font-medium">{{ notification.message }}</p>
        </div>
        <button
          @click="notification.show = false"
          class="flex-shrink-0 text-green-200 hover:text-white transition-colors"
        >
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </Transition>
  </AppLayout>
</template>

<style scoped>
/* Custom scrollbar for horizontal scroll */
.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

.dark .overflow-x-auto::-webkit-scrollbar-track {
  background: #1e293b;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb {
  background: #475569;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #64748b;
}
</style>
