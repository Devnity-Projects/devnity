<script setup lang="ts">
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { 
  Calendar,
  Clock,
  User,
  FolderOpen,
  Flag,
  Type,
  FileText,
  Tag,
  CheckCircle,
  Edit,
  Trash2,
  ArrowLeft,
  Play,
  Pause,
  CheckSquare
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
  labels: string[]
  acceptance_criteria: string | null
  notes: string | null
  is_overdue: boolean
  status_label: string
  priority_label: string
  type_label: string
  created_at: string
  updated_at: string
  started_at: string | null
  completed_at: string | null
}

interface Props {
  task: Task
}

const props = defineProps<Props>()

const progressPercentage = computed(() => {
  if (!props.task.hours_estimated || props.task.hours_estimated === 0) {
    return 0
  }
  
  const worked = props.task.hours_worked || 0
  const estimated = props.task.hours_estimated
  
  return Math.min(100, (worked / estimated) * 100)
})

function formatDate(date: string | null): string {
  if (!date) return 'N/A'
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit', 
    year: 'numeric'
  }).format(new Date(date))
}

function formatDateTime(date: string | null): string {
  if (!date) return 'N/A'
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit', 
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
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

function deleteTask() {
  if (confirm(`Tem certeza que deseja excluir a tarefa "${props.task.title}"?`)) {
    router.delete(route('tasks.destroy', props.task.id), {
      onSuccess: () => {
        router.get(route('tasks.index'))
      }
    })
  }
}

function updateStatus(newStatus: string) {
  router.patch(route('tasks.update-status', props.task.id), {
    status: newStatus
  })
}
</script>

<template>
  <AppLayout title="Visualizar Tarefa">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center gap-4 mb-4">
            <Link
              :href="route('tasks.index')"
              class="flex items-center gap-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
            >
              <ArrowLeft class="h-4 w-4" />
              Voltar para Tarefas
            </Link>
          </div>
          
          <div class="flex items-start justify-between">
            <div class="min-w-0 flex-1">
              <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                {{ task.title }}
              </h1>
              <div class="flex flex-wrap items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1">
                  <Calendar class="h-4 w-4" />
                  Criado em {{ formatDate(task.created_at) }}
                </span>
                <span v-if="task.updated_at !== task.created_at" class="flex items-center gap-1">
                  <Clock class="h-4 w-4" />
                  Atualizado em {{ formatDate(task.updated_at) }}
                </span>
              </div>
            </div>
            
            <div class="flex items-center gap-3">
              <Link
                :href="route('tasks.edit', task.id)"
                class="inline-flex items-center gap-2 px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
              >
                <Edit class="h-4 w-4" />
                Editar
              </Link>
              <button
                @click="deleteTask"
                class="inline-flex items-center gap-2 px-3 py-2 border border-red-300 dark:border-red-600 shadow-sm text-sm font-medium rounded-md text-red-700 dark:text-red-400 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
              >
                <Trash2 class="h-4 w-4" />
                Excluir
              </button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <FileText class="h-5 w-5" />
                Descrição
              </h2>
              <div class="prose dark:prose-invert max-w-none">
                <p v-if="task.description" class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                  {{ task.description }}
                </p>
                <p v-else class="text-gray-500 dark:text-gray-400 italic">
                  Nenhuma descrição fornecida.
                </p>
              </div>
            </div>

            <!-- Acceptance Criteria -->
            <div v-if="task.acceptance_criteria" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <CheckCircle class="h-5 w-5" />
                Critérios de Aceitação
              </h2>
              <div class="prose dark:prose-invert max-w-none">
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                  {{ task.acceptance_criteria }}
                </p>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="task.notes" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <FileText class="h-5 w-5" />
                Observações
              </h2>
              <div class="prose dark:prose-invert max-w-none">
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                  {{ task.notes }}
                </p>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Status & Quick Actions -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Status e Ações
              </h2>
              
              <!-- Current Status -->
              <div class="mb-4">
                <span :class="['inline-flex items-center px-3 py-1 rounded-full text-sm font-medium', getStatusColor(task.status)]">
                  {{ task.status_label }}
                </span>
              </div>

              <!-- Quick Status Actions -->
              <div class="space-y-2">
                <button
                  v-if="task.status === 'todo'"
                  @click="updateStatus('in_progress')"
                  class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors"
                >
                  <Play class="h-4 w-4" />
                  Iniciar Tarefa
                </button>
                
                <button
                  v-if="task.status === 'in_progress'"
                  @click="updateStatus('review')"
                  class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-md transition-colors"
                >
                  <Pause class="h-4 w-4" />
                  Enviar para Revisão
                </button>
                
                <button
                  v-if="['review', 'in_progress'].includes(task.status)"
                  @click="updateStatus('completed')"
                  class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors"
                >
                  <CheckSquare class="h-4 w-4" />
                  Marcar como Concluída
                </button>
              </div>
            </div>

            <!-- Task Details -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                Detalhes
              </h2>
              
              <dl class="space-y-4">
                <!-- Project -->
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <FolderOpen class="h-4 w-4" />
                    Projeto
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ task.project.name }}
                  </dd>
                </div>

                <!-- Assigned User -->
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <User class="h-4 w-4" />
                    Responsável
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ task.assigned_user?.name || 'Não atribuído' }}
                  </dd>
                </div>

                <!-- Priority -->
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <Flag class="h-4 w-4" />
                    Prioridade
                  </dt>
                  <dd class="mt-1">
                    <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getPriorityColor(task.priority)]">
                      {{ task.priority_label }}
                    </span>
                  </dd>
                </div>

                <!-- Type -->
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <Type class="h-4 w-4" />
                    Tipo
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ task.type_label }}
                  </dd>
                </div>

                <!-- Due Date -->
                <div>
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <Calendar class="h-4 w-4" />
                    Data de Entrega
                  </dt>
                  <dd class="mt-1 text-sm" :class="task.is_overdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-900 dark:text-white'">
                    {{ formatDate(task.due_date) }}
                    <span v-if="task.is_overdue" class="ml-1 text-xs">
                      (Atrasado)
                    </span>
                  </dd>
                </div>

                <!-- Time Tracking -->
                <div v-if="task.hours_estimated">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <Clock class="h-4 w-4" />
                    Tempo
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    <div class="flex justify-between items-center mb-2">
                      <span>{{ task.hours_worked || 0 }}h de {{ task.hours_estimated }}h</span>
                      <span class="text-xs text-gray-500">{{ Math.round(progressPercentage) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                      <div 
                        class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        :style="{ width: progressPercentage + '%' }"
                      />
                    </div>
                  </dd>
                </div>

                <!-- Timeline -->
                <div v-if="task.started_at || task.completed_at">
                  <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Timeline
                  </dt>
                  <dd class="mt-1 space-y-1 text-xs text-gray-600 dark:text-gray-400">
                    <div v-if="task.started_at">
                      Iniciado: {{ formatDateTime(task.started_at) }}
                    </div>
                    <div v-if="task.completed_at">
                      Concluído: {{ formatDateTime(task.completed_at) }}
                    </div>
                  </dd>
                </div>
              </dl>
            </div>

            <!-- Labels -->
            <div v-if="task.labels.length > 0" class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
              <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <Tag class="h-5 w-5" />
                Labels
              </h2>
              <div class="flex flex-wrap gap-2">
                <span
                  v-for="label in task.labels"
                  :key="label"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                >
                  {{ label }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
