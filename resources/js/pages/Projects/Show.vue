<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import { 
  FolderKanban,
  Edit,
  Trash2,
  ExternalLink,
  Calendar,
  Clock,
  DollarSign,
  User,
  Building,
  AlertCircle,
  CheckCircle,
  Pause,
  X,
  ArrowLeft,
  TrendingUp,
  ListTodo,
  CheckSquare,
  PlayCircle
} from 'lucide-vue-next'

interface Client {
  id: number
  name: string
  email?: string
}

interface Task {
  id: number
  name: string
  status: string
  priority: string
  deadline: string | null
  assigned_user?: {
    id: number
    name: string
  }
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
  technologies: string[]
  repository_url: string | null
  demo_url: string | null
  production_url: string | null
  notes: string | null
  created_at: string
  status_label: string
  priority_label: string
  type_label: string
  progress_percentage: string
  is_overdue: boolean
  tasks?: Task[]
}

interface TaskStats {
  total: number
  completed: number
  in_progress: number
  overdue: number
}

interface Props {
  project: Project
  taskStats: TaskStats
}

const props = defineProps<Props>()

const showDeleteModal = ref(false)

const statusConfig: Record<string, { icon: any, color: string, bg: string }> = {
  planning: { icon: Clock, color: 'text-blue-600', bg: 'bg-blue-100' },
  in_progress: { icon: PlayCircle, color: 'text-yellow-600', bg: 'bg-yellow-100' },
  completed: { icon: CheckCircle, color: 'text-green-600', bg: 'bg-green-100' },
  cancelled: { icon: X, color: 'text-red-600', bg: 'bg-red-100' },
  on_hold: { icon: Pause, color: 'text-gray-600', bg: 'bg-gray-100' }
}

const priorityConfig: Record<string, { color: string, bg: string }> = {
  low: { color: 'text-green-600', bg: 'bg-green-100' },
  medium: { color: 'text-yellow-600', bg: 'bg-yellow-100' },
  high: { color: 'text-orange-600', bg: 'bg-orange-100' },
  urgent: { color: 'text-red-600', bg: 'bg-red-100' }
}

const progressPercentage = computed(() => {
  if (!props.project.hours_estimated || props.project.hours_estimated === 0) {
    return 0
  }
  return Math.min(100, ((props.project.hours_worked || 0) / props.project.hours_estimated) * 100)
})

const quickStats = computed(() => [
  {
    label: 'Total de Tarefas',
    value: props.taskStats.total,
    icon: ListTodo,
    color: 'text-blue-600',
    bgColor: 'bg-blue-50'
  },
  {
    label: 'Concluídas',
    value: props.taskStats.completed,
    icon: CheckSquare,
    color: 'text-green-600',
    bgColor: 'bg-green-50'
  },
  {
    label: 'Em Progresso',
    value: props.taskStats.in_progress,
    icon: PlayCircle,
    color: 'text-yellow-600',
    bgColor: 'bg-yellow-50'
  },
  {
    label: 'Atrasadas',
    value: props.taskStats.overdue,
    icon: AlertCircle,
    color: 'text-red-600',
    bgColor: 'bg-red-50'
  }
])

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

function confirmDelete() {
  showDeleteModal.value = true
}

function deleteProject() {
  router.delete(`/projects/${props.project.id}`, {
    onFinish: () => {
      showDeleteModal.value = false
    }
  })
}

function goBack() {
  router.get('/projects')
}
</script>

<template>
  <AppLayout :title="`Projeto: ${project.name}`">
    <div class="max-w-6xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-4">
            <button
              @click="goBack"
              class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition-colors"
            >
              <ArrowLeft class="h-5 w-5" />
            </button>
            
            <div class="flex items-center gap-3">
              <FolderKanban class="h-8 w-8 text-blue-600 dark:text-blue-400" />
              <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                  {{ project.name }}
                </h1>
                <div class="flex items-center gap-2 mt-1">
                  <User class="h-4 w-4 text-gray-400" />
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ project.client.name }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3">
            <Link
              :href="`/projects/${project.id}/edit`"
              class="devnity-button-secondary inline-flex items-center gap-2"
            >
              <Edit class="h-4 w-4" />
              Editar
            </Link>
            
            <button
              @click="confirmDelete"
              class="devnity-button-danger inline-flex items-center gap-2"
            >
              <Trash2 class="h-4 w-4" />
              Excluir
            </button>
          </div>
        </div>

        <!-- Status e badges -->
        <div class="flex items-center gap-3 mb-6">
          <span :class="[
            'inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium',
            statusConfig[project.status]?.bg || 'bg-gray-100',
            statusConfig[project.status]?.color || 'text-gray-600'
          ]">
            <component :is="statusConfig[project.status]?.icon || Clock" class="h-4 w-4" />
            {{ project.status_label }}
          </span>
          
          <span :class="[
            'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
            priorityConfig[project.priority]?.bg || 'bg-gray-100',
            priorityConfig[project.priority]?.color || 'text-gray-600'
          ]">
            {{ project.priority_label }}
          </span>
          
          <span class="inline-flex items-center px-3 py-1 bg-purple-100 dark:bg-purple-900/20 text-purple-800 dark:text-purple-300 rounded-full text-sm font-medium">
            {{ project.type_label }}
          </span>

          <span v-if="project.is_overdue" class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 dark:bg-red-900/20 text-red-800 dark:text-red-300 rounded-full text-sm font-medium">
            <AlertCircle class="h-4 w-4" />
            Atrasado
          </span>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div
            v-for="stat in quickStats"
            :key="stat.label"
            :class="[
              'flex items-center gap-3 p-4 rounded-lg border',
              stat.bgColor,
              'border-gray-200 dark:border-gray-700'
            ]"
          >
            <div :class="['p-2 rounded-lg', stat.color.replace('text-', 'bg-').replace('-600', '-100')]">
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
      </div>

      <div class="grid lg:grid-cols-3 gap-8">
        <!-- Informações Principais -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Descrição -->
          <div v-if="project.description" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
              Descrição
            </h2>
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
              {{ project.description }}
            </p>
          </div>

          <!-- Progresso -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Progresso
              </h2>
              <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                {{ Math.round(progressPercentage) }}%
              </span>
            </div>
            
            <div class="mb-4">
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                <div 
                  class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-300"
                  :style="{ width: `${progressPercentage}%` }"
                ></div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Horas Trabalhadas:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                  {{ project.hours_worked || 0 }}h
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Horas Estimadas:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                  {{ project.hours_estimated || 0 }}h
                </span>
              </div>
            </div>
          </div>

          <!-- Tecnologias -->
          <div v-if="project.technologies && project.technologies.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
              Tecnologias
            </h2>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="tech in project.technologies"
                :key="tech"
                class="inline-flex items-center px-3 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 rounded-full text-sm font-medium"
              >
                {{ tech }}
              </span>
            </div>
          </div>

          <!-- URLs -->
          <div v-if="project.repository_url || project.demo_url || project.production_url" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
              Links
            </h2>
            <div class="space-y-3">
              <div v-if="project.repository_url" class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Repositório:</span>
                <a
                  :href="project.repository_url"
                  target="_blank"
                  class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                >
                  Visualizar
                  <ExternalLink class="h-4 w-4" />
                </a>
              </div>
              
              <div v-if="project.demo_url" class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Demo:</span>
                <a
                  :href="project.demo_url"
                  target="_blank"
                  class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                >
                  Visualizar
                  <ExternalLink class="h-4 w-4" />
                </a>
              </div>
              
              <div v-if="project.production_url" class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Produção:</span>
                <a
                  :href="project.production_url"
                  target="_blank"
                  class="inline-flex items-center gap-2 text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                >
                  Visualizar
                  <ExternalLink class="h-4 w-4" />
                </a>
              </div>
            </div>
          </div>

          <!-- Observações -->
          <div v-if="project.notes" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
              Observações
            </h2>
            <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-wrap">
              {{ project.notes }}
            </p>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Informações do Projeto -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
              Detalhes
            </h2>
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Cliente:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                  {{ project.client.name }}
                </span>
              </div>
              
              <div v-if="project.budget" class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Orçamento:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                  {{ formatCurrency(project.budget) }}
                </span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Criado em:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                  {{ formatDate(project.created_at) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Datas -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
              Cronograma
            </h2>
            <div class="space-y-4">
              <div v-if="project.start_date" class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Início:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                  {{ formatDate(project.start_date) }}
                </span>
              </div>
              
              <div v-if="project.end_date" class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Fim:</span>
                <span class="font-medium text-gray-900 dark:text-gray-100">
                  {{ formatDate(project.end_date) }}
                </span>
              </div>
              
              <div v-if="project.deadline" class="flex items-center justify-between">
                <span class="text-gray-600 dark:text-gray-400">Prazo:</span>
                <span 
                  :class="[
                    'font-medium',
                    project.is_overdue 
                      ? 'text-red-600 dark:text-red-400' 
                      : 'text-gray-900 dark:text-gray-100'
                  ]"
                >
                  {{ formatDate(project.deadline) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Ações Rápidas -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
              Ações
            </h2>
            <div class="space-y-3">
              <Link
                href="/tasks/create"
                class="w-full devnity-button-primary inline-flex items-center justify-center gap-2"
              >
                <ListTodo class="h-4 w-4" />
                Nova Tarefa
              </Link>
              
              <Link
                href="/tasks"
                class="w-full devnity-button-secondary inline-flex items-center justify-center gap-2"
              >
                <TrendingUp class="h-4 w-4" />
                Ver Tarefas
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de confirmação -->
    <ConfirmationModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="deleteProject"
      title="Excluir Projeto"
      :message="`Tem certeza que deseja excluir o projeto '${project.name}'? Esta ação não pode ser desfeita.`"
      confirmText="Sim, excluir"
      cancelText="Cancelar"
      variant="danger"
    />
  </AppLayout>
</template>
