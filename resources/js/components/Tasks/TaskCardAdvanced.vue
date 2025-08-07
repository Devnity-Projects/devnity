<script setup lang="ts">
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
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
  Wrench,
  MessageCircle,
  Paperclip,
  CheckSquare,
  Eye,
  GripVertical,
  MoreHorizontal
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
  comments_count?: number
  attachments_count?: number
  checklist_count?: number
  checklist_completed?: number
}

interface Props {
  task: Task
  isDragging?: boolean
}

const props = defineProps<Props>()
const emit = defineEmits<{
  taskClick: [task: Task]
  dragstart: [event: DragEvent, task: Task]
  dragend: [event: DragEvent]
}>()

const showMenu = ref(false)

const priorityColors = computed(() => {
  const colors: Record<string, string> = {
    low: 'border-l-green-500 bg-green-50 dark:bg-green-900/10',
    medium: 'border-l-yellow-500 bg-yellow-50 dark:bg-yellow-900/10',
    high: 'border-l-orange-500 bg-orange-50 dark:bg-orange-900/10',
    urgent: 'border-l-red-500 bg-red-50 dark:bg-red-900/10'
  }
  return colors[props.task.priority] || colors.medium
})

const priorityTextColors = computed(() => {
  const colors: Record<string, string> = {
    low: 'text-green-700 dark:text-green-300',
    medium: 'text-yellow-700 dark:text-yellow-300',
    high: 'text-orange-700 dark:text-orange-300',
    urgent: 'text-red-700 dark:text-red-300'
  }
  return colors[props.task.priority] || colors.medium
})

const typeIcon = computed(() => {
  const icons: Record<string, any> = {
    feature: Lightbulb,
    bug: Bug,
    enhancement: Wrench,
    documentation: FileText,
    testing: TestTube
  }
  return icons[props.task.type] || Lightbulb
})

const checklistProgress = computed(() => {
  if (!props.task.checklist_count || props.task.checklist_count === 0) return 0
  return (props.task.checklist_completed || 0) / props.task.checklist_count * 100
})

const getUserInitials = (name: string): string => {
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .join('')
    .substring(0, 2)
}

const formatDate = (date: string | null): string => {
  if (!date) return ''
  return new Intl.DateTimeFormat('pt-BR', { 
    day: '2-digit', 
    month: '2-digit' 
  }).format(new Date(date))
}

const handleClick = () => {
  emit('taskClick', props.task)
}

const openTask = () => {
  router.visit(route('tasks.show', props.task.id))
}

const cardClasses = computed(() => [
  'group relative bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer',
  'border-l-4',
  priorityColors.value,
  {
    'opacity-60 rotate-2 scale-105': props.isDragging,
    'ring-2 ring-red-400 dark:ring-red-500': props.task.is_overdue
  }
])
</script>

<template>
  <div 
    :class="cardClasses"
    @click="handleClick"
    draggable="true"
    @dragstart="(event) => $emit('dragstart', event, task)"
    @dragend="(event) => $emit('dragend', event)"
  >
    <!-- Drag Handle -->
    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
      <GripVertical class="h-4 w-4 text-gray-400 dark:text-gray-500" />
    </div>

    <div class="p-4">
      <!-- Header with type icon and priority -->
      <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-2">
          <component :is="typeIcon" class="h-4 w-4 text-gray-500 dark:text-gray-400" />
          <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
            {{ task.type_label }}
          </span>
        </div>
        <div class="flex items-center gap-1">
          <Flag :class="['h-3 w-3', priorityTextColors]" />
        </div>
      </div>

      <!-- Task Title -->
      <h3 class="font-medium text-gray-900 dark:text-white mb-2 line-clamp-2 leading-5">
        {{ task.title }}
      </h3>

      <!-- Description Preview -->
      <p v-if="task.description" class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2">
        {{ task.description }}
      </p>

      <!-- Project Info -->
      <div class="flex items-center gap-2 mb-3">
        <FolderOpen class="h-3 w-3 text-gray-400" />
        <span class="text-xs text-gray-500 dark:text-gray-400 truncate">
          {{ task.project.name }}
        </span>
      </div>

      <!-- Labels -->
      <div v-if="task.labels.length > 0" class="flex flex-wrap gap-1 mb-3">
        <span
          v-for="label in task.labels.slice(0, 3)"
          :key="label"
          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
        >
          {{ label }}
        </span>
        <span
          v-if="task.labels.length > 3"
          class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400"
        >
          +{{ task.labels.length - 3 }}
        </span>
      </div>

      <!-- Checklist Progress -->
      <div v-if="task.checklist_count && task.checklist_count > 0" class="mb-3">
        <div class="flex items-center justify-between mb-1">
          <div class="flex items-center gap-1">
            <CheckSquare class="h-3 w-3 text-gray-400" />
            <span class="text-xs text-gray-600 dark:text-gray-400">
              {{ task.checklist_completed || 0 }}/{{ task.checklist_count }}
            </span>
          </div>
          <span class="text-xs text-gray-500 dark:text-gray-400">
            {{ Math.round(checklistProgress) }}%
          </span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
          <div
            class="bg-green-500 dark:bg-green-600 h-1.5 rounded-full transition-all duration-300"
            :style="{ width: checklistProgress + '%' }"
          />
        </div>
      </div>

      <!-- Time Tracking -->
      <div v-if="task.hours_estimated" class="flex items-center gap-2 mb-3">
        <Clock class="h-3 w-3 text-gray-400" />
        <div class="flex-1">
          <div class="flex items-center justify-between mb-1">
            <span class="text-xs text-gray-600 dark:text-gray-400">
              {{ task.time_spent }} de {{ task.hours_estimated }}h
            </span>
          </div>
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1">
            <div
              class="bg-blue-500 dark:bg-blue-600 h-1 rounded-full transition-all duration-300"
              :style="{ 
                width: Math.min(100, (task.hours_worked || 0) / task.hours_estimated * 100) + '%' 
              }"
            />
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="flex items-center justify-between">
        <!-- Due Date -->
        <div v-if="task.due_date" class="flex items-center gap-1">
          <Calendar class="h-3 w-3" :class="task.is_overdue ? 'text-red-500' : 'text-gray-400'" />
          <span 
            class="text-xs"
            :class="task.is_overdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-500 dark:text-gray-400'"
          >
            {{ formatDate(task.due_date) }}
          </span>
          <AlertCircle v-if="task.is_overdue" class="h-3 w-3 text-red-500" />
        </div>
        <div v-else></div>

        <!-- Footer Right: Stats and Avatar -->
        <div class="flex items-center gap-2">
          <!-- Comments, Attachments indicators -->
          <div class="flex items-center gap-2">
            <div v-if="task.comments_count && task.comments_count > 0" class="flex items-center gap-1">
              <MessageCircle class="h-3 w-3 text-gray-400" />
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ task.comments_count }}</span>
            </div>
            <div v-if="task.attachments_count && task.attachments_count > 0" class="flex items-center gap-1">
              <Paperclip class="h-3 w-3 text-gray-400" />
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ task.attachments_count }}</span>
            </div>
          </div>

          <!-- Assigned User Avatar -->
          <div v-if="task.assigned_user" class="flex-shrink-0">
            <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-xs font-medium text-white">
              {{ getUserInitials(task.assigned_user.name) }}
            </div>
          </div>
          <div v-else class="flex-shrink-0">
            <div class="w-6 h-6 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
              <User class="h-3 w-3 text-gray-500 dark:text-gray-400" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions on Hover -->
    <div class="absolute top-2 left-2 opacity-0 group-hover:opacity-100 transition-opacity">
      <button
        @click.stop="openTask"
        class="p-1 rounded bg-gray-800/80 text-white hover:bg-gray-800 transition-colors"
        title="Abrir tarefa"
      >
        <Eye class="h-3 w-3" />
      </button>
    </div>
  </div>
</template>
