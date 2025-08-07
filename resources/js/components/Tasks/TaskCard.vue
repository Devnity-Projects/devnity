<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import {
  User,
  Calendar,
  Clock,
  AlertCircle,
  Flag,
  FolderOpen,
  Bug,
  Lightbulb,
  FileText,
  TestTube,
  Wrench
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
  task: Task
  draggable?: boolean
}

const props = defineProps<Props>()

// Computed
const priorityConfig = computed(() => {
  const config: Record<string, { color: string; bg: string; icon: any }> = {
    low: { color: 'text-green-600 dark:text-green-400', bg: 'bg-green-100 dark:bg-green-900/20', icon: Flag },
    medium: { color: 'text-yellow-600 dark:text-yellow-400', bg: 'bg-yellow-100 dark:bg-yellow-900/20', icon: Flag },
    high: { color: 'text-orange-600 dark:text-orange-400', bg: 'bg-orange-100 dark:bg-orange-900/20', icon: Flag },
    urgent: { color: 'text-red-600 dark:text-red-400', bg: 'bg-red-100 dark:bg-red-900/20', icon: AlertCircle }
  }
  return config[props.task.priority] || config.medium
})

const typeConfig = computed(() => {
  const config: Record<string, { color: string; bg: string; icon: any }> = {
    feature: { color: 'text-blue-600 dark:text-blue-400', bg: 'bg-blue-100 dark:bg-blue-900/20', icon: Lightbulb },
    bug: { color: 'text-red-600 dark:text-red-400', bg: 'bg-red-100 dark:bg-red-900/20', icon: Bug },
    enhancement: { color: 'text-purple-600 dark:text-purple-400', bg: 'bg-purple-100 dark:bg-purple-900/20', icon: Wrench },
    documentation: { color: 'text-gray-600 dark:text-gray-400', bg: 'bg-gray-100 dark:bg-gray-900/20', icon: FileText },
    testing: { color: 'text-green-600 dark:text-green-400', bg: 'bg-green-100 dark:bg-green-900/20', icon: TestTube }
  }
  return config[props.task.type] || config.feature
})

const formatDate = (date: string | null): string => {
  if (!date) return ''
  return new Intl.DateTimeFormat('pt-BR', { 
    day: '2-digit', 
    month: '2-digit' 
  }).format(new Date(date))
}

const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(word => word.charAt(0))
    .slice(0, 2)
    .join('')
    .toUpperCase()
}
</script>

<template>
  <div 
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 shadow-sm hover:shadow-md transition-all duration-200"
    :class="{ 'ring-1 ring-red-200 dark:ring-red-800': task.is_overdue }"
    :draggable="draggable"
  >
    <!-- Task Header -->
    <div class="flex items-start justify-between mb-3">
      <div class="flex-1 min-w-0">
        <h4 class="font-medium text-gray-900 dark:text-white line-clamp-2 text-sm leading-5">
          {{ task.title }}
        </h4>
      </div>
      
      <!-- Priority Flag -->
      <div :class="['ml-2 flex-shrink-0 inline-flex items-center px-2 py-1 rounded-full text-xs font-medium', priorityConfig.bg, priorityConfig.color]">
        <component :is="priorityConfig.icon" class="h-3 w-3 mr-1" />
        {{ task.priority_label }}
      </div>
    </div>
    
    <!-- Description -->
    <p v-if="task.description" class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
      {{ task.description }}
    </p>
    
    <!-- Meta Information -->
    <div class="space-y-2 mb-3">
      <!-- Project -->
      <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
        <FolderOpen class="h-3 w-3" />
        <span>{{ task.project.name }}</span>
      </div>
      
      <!-- Type -->
      <div class="flex items-center gap-2">
        <span :class="['inline-flex items-center px-2 py-1 rounded text-xs font-medium', typeConfig.bg, typeConfig.color]">
          <component :is="typeConfig.icon" class="h-3 w-3 mr-1" />
          {{ task.type_label }}
        </span>
      </div>
      
      <!-- Due Date & Time Tracking -->
      <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
        <div v-if="task.due_date" class="flex items-center gap-1">
          <Calendar class="h-3 w-3" />
          <span :class="{ 'text-red-600 dark:text-red-400 font-medium': task.is_overdue }">
            {{ formatDate(task.due_date) }}
          </span>
        </div>
        
        <div v-if="task.hours_estimated" class="flex items-center gap-1">
          <Clock class="h-3 w-3" />
          <span>{{ task.time_spent }} / {{ task.hours_estimated }}h</span>
        </div>
      </div>
    </div>
    
    <!-- Labels -->
    <div v-if="task.labels && task.labels.length > 0" class="mb-3">
      <div class="flex flex-wrap gap-1">
        <span 
          v-for="label in task.labels.slice(0, 3)" 
          :key="label"
          class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded"
        >
          {{ label }}
        </span>
        <span 
          v-if="task.labels.length > 3"
          class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400 rounded"
        >
          +{{ task.labels.length - 3 }}
        </span>
      </div>
    </div>
    
    <!-- Footer -->
    <div class="flex items-center justify-between">
      <!-- Assigned User -->
      <div v-if="task.assigned_user" class="flex items-center gap-2">
        <div class="w-6 h-6 bg-blue-100 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-full flex items-center justify-center text-xs font-medium">
          {{ getInitials(task.assigned_user.name) }}
        </div>
        <span class="text-xs text-gray-600 dark:text-gray-400 truncate max-w-20">
          {{ task.assigned_user.name }}
        </span>
      </div>
      <div v-else class="flex items-center gap-2 text-gray-400 dark:text-gray-600">
        <User class="h-4 w-4" />
        <span class="text-xs">Sem responsável</span>
      </div>
      
      <!-- Overdue indicator -->
      <div v-if="task.is_overdue" class="flex items-center gap-1 text-red-600 dark:text-red-400">
        <AlertCircle class="h-3 w-3" />
        <span class="text-xs font-medium">Atrasado</span>
      </div>
    </div>
    
    <!-- Click overlay to view task -->
    <Link 
      :href="route('tasks.show', task.id)"
      class="absolute inset-0 rounded-lg"
      preserve-scroll
    />
  </div>
</template>

<style scoped>
.line-clamp-2 {
  overflow: hidden;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
}
</style>
