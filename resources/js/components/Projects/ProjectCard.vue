<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  Building,
  Calendar,
  Clock,
  DollarSign,
  Eye,
  Edit,
  Trash2,
  MoreVertical,
  AlertCircle,
  CheckCircle,
  Pause,
  X,
  PlayCircle,
  User
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
  project: Project
  isSelected: boolean
  onToggleSelect: (id: number) => void
  onDelete: (project: Project) => void
}

const props = defineProps<Props>()

// Estado do dropdown
const showDropdown = ref(false)

const statusConfig: Record<string, { icon: any, color: string, bg: string }> = {
  planning: { icon: Clock, color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-100 dark:bg-blue-900/20' },
  in_progress: { icon: PlayCircle, color: 'text-yellow-600 dark:text-yellow-400', bg: 'bg-yellow-100 dark:bg-yellow-900/20' },
  completed: { icon: CheckCircle, color: 'text-green-600 dark:text-green-400', bg: 'bg-green-100 dark:bg-green-900/20' },
  cancelled: { icon: X, color: 'text-red-600 dark:text-red-400', bg: 'bg-red-100 dark:bg-red-900/20' },
  on_hold: { icon: Pause, color: 'text-gray-600 dark:text-gray-400', bg: 'bg-gray-100 dark:bg-gray-900/20' }
}

const priorityConfig: Record<string, { color: string, bg: string }> = {
  low: { color: 'text-green-600 dark:text-green-400', bg: 'bg-green-100 dark:bg-green-900/20' },
  medium: { color: 'text-yellow-600 dark:text-yellow-400', bg: 'bg-yellow-100 dark:bg-yellow-900/20' },
  high: { color: 'text-orange-600 dark:text-orange-400', bg: 'bg-orange-100 dark:bg-orange-900/20' },
  urgent: { color: 'text-red-600 dark:text-red-400', bg: 'bg-red-100 dark:bg-red-900/20' }
}

const progressPercentage = computed(() => {
  if (!props.project.hours_estimated || props.project.hours_estimated === 0) {
    return 0
  }
  return Math.min(100, ((props.project.hours_worked || 0) / props.project.hours_estimated) * 100)
})

const formatCurrency = (value: number | null): string => {
  if (!value) return 'N/A'
  return new Intl.NumberFormat('pt-BR', { 
    style: 'currency', 
    currency: 'BRL' 
  }).format(value)
}

const formatDate = (date: string | null): string => {
  if (!date) return 'N/A'
  return new Intl.DateTimeFormat('pt-BR').format(new Date(date))
}

const handleDelete = () => {
  showDropdown.value = false
  props.onDelete(props.project)
}

// Fechar dropdown com Escape
const handleKeydown = (event: KeyboardEvent) => {
  if (event.key === 'Escape' && showDropdown.value) {
    showDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleKeydown)
})
</script>

<template>
  <div 
    :class="[
      'bg-white dark:bg-gray-800 rounded-lg shadow-sm border transition-all duration-200 hover:shadow-lg hover:border-gray-300 dark:hover:border-gray-600 group',
      isSelected 
        ? 'border-blue-500 dark:border-blue-400 ring-2 ring-blue-200 dark:ring-blue-800' 
        : 'border-gray-200 dark:border-gray-700',
      project.is_overdue ? 'ring-1 ring-red-200 dark:ring-red-800' : ''
    ]"
  >
    <!-- Cabeçalho do cartão -->
    <div class="p-6">
      <div class="flex items-start justify-between mb-4">
        <div class="flex items-start gap-3 flex-1 min-w-0">
          <input
            type="checkbox"
            :checked="isSelected"
            @change="onToggleSelect(project.id)"
            class="mt-1 rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
          />
          
          <div class="flex-1 min-w-0">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate">
              {{ project.name }}
            </h3>
            
            <div class="flex items-center gap-2 mt-1">
              <User class="h-4 w-4 text-gray-400" />
              <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ project.client.name }}
              </span>
            </div>
          </div>
        </div>

        <!-- Menu de ações -->
        <div class="relative">
          <button 
            @click="showDropdown = !showDropdown"
            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 opacity-60 group-hover:opacity-100"
            :class="{ 'bg-gray-100 dark:bg-gray-700 opacity-100': showDropdown }"
            aria-label="Menu de ações do projeto"
            aria-expanded="false"
          >
            <MoreVertical class="h-5 w-5 text-gray-500 dark:text-gray-400 transition-transform duration-200" :class="{ 'rotate-90': showDropdown }" />
          </button>
          
          <!-- Backdrop para fechar o dropdown -->
          <div 
            v-if="showDropdown"
            class="fixed inset-0 z-10"
            @click="showDropdown = false"
          ></div>
          
          <!-- Menu dropdown -->
          <div 
            v-if="showDropdown"
            class="absolute right-0 top-full mt-1 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 py-1 z-20 animate-in"
          >
            <Link 
              :href="route('projects.show', project.id)"
              class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              @click="showDropdown = false"
            >
              <Eye class="h-4 w-4 text-blue-500" />
              Visualizar
            </Link>
            <Link 
              :href="route('projects.edit', project.id)"
              class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
              @click="showDropdown = false"
            >
              <Edit class="h-4 w-4 text-green-500" />
              Editar
            </Link>
            <hr class="my-1 border-gray-200 dark:border-gray-600" />
            <button 
              @click="handleDelete"
              class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 w-full text-left transition-colors"
            >
              <Trash2 class="h-4 w-4" />
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
          {{ project.status_label }}
        </span>
        
        <span :class="[
          'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
          priorityConfig[project.priority]?.bg || 'bg-gray-100',
          priorityConfig[project.priority]?.color || 'text-gray-600'
        ]">
          {{ project.priority_label }}
        </span>

        <span v-if="project.is_overdue" class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
          <AlertCircle class="h-3 w-3" />
          Atrasado
        </span>
      </div>

      <!-- Descrição -->
      <p v-if="project.description" class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
        {{ project.description }}
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
            :style="{ width: `${progressPercentage}%` }"
          ></div>
        </div>
      </div>

      <!-- Tecnologias -->
      <div v-if="project.technologies && project.technologies.length > 0" class="mb-4">
        <div class="flex flex-wrap gap-1">
          <span 
            v-for="tech in project.technologies.slice(0, 3)" 
            :key="tech"
            class="inline-block px-2 py-1 text-xs bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 rounded"
          >
            {{ tech }}
          </span>
          <span 
            v-if="project.technologies.length > 3"
            class="inline-block px-2 py-1 text-xs bg-gray-50 text-gray-700 dark:bg-gray-800 dark:text-gray-400 rounded"
          >
            +{{ project.technologies.length - 3 }}
          </span>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <span>{{ project.type_label }}</span>
        <span>Criado em {{ formatDate(project.created_at) }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}

@keyframes slideInFromTop {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-in {
  animation: slideInFromTop 0.2s ease-out;
}
</style>
