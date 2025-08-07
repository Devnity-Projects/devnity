<script setup lang="ts">
import { ref, computed } from 'vue'
import TaskCardAdvanced from '@/components/Tasks/TaskCardAdvanced.vue'
import { Plus } from 'lucide-vue-next'

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
  title: string
  color: string
  status: string
  tasks: Task[]
  draggedTask: Task | null
}

interface Emits {
  (event: 'task-move', taskId: number, newStatus: string, newOrder: number): void
  (event: 'task-click', task: Task): void
  (event: 'drag-start', task: Task): void
  (event: 'drag-end'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const isDragOver = ref(false)

// Computed
const colorClasses = computed(() => {
  const colorMap: Record<string, { header: string; badge: string; border: string }> = {
    blue: {
      header: 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800',
      badge: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
      border: 'border-blue-300 dark:border-blue-600'
    },
    yellow: {
      header: 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800',
      badge: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
      border: 'border-yellow-300 dark:border-yellow-600'
    },
    purple: {
      header: 'bg-purple-50 dark:bg-purple-900/20 border-purple-200 dark:border-purple-800',
      badge: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
      border: 'border-purple-300 dark:border-purple-600'
    },
    green: {
      header: 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800',
      badge: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
      border: 'border-green-300 dark:border-green-600'
    }
  }
  return colorMap[props.color] || colorMap.blue
})

// Drag and Drop Methods
function handleDragStart(event: DragEvent, task: Task) {
  if (!event.dataTransfer) return
  
  emit('drag-start', task)
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', task.id.toString())
  
  // Add dragging class to the element
  if (event.target instanceof HTMLElement) {
    event.target.classList.add('dragging')
  }
}

function handleDragEnd(event: DragEvent) {
  emit('drag-end')
  isDragOver.value = false
  
  if (event.target instanceof HTMLElement) {
    event.target.classList.remove('dragging')
  }
}

function handleDragOver(event: DragEvent) {
  event.preventDefault()
  if (!event.dataTransfer) return
  
  event.dataTransfer.dropEffect = 'move'
  isDragOver.value = true
}

function handleDragEnter(event: DragEvent) {
  event.preventDefault()
  isDragOver.value = true
}

function handleDragLeave(event: DragEvent) {
  // Only hide drag over if we're actually leaving the drop zone
  if (!event.currentTarget || !event.relatedTarget) return
  
  const dropZone = event.currentTarget as HTMLElement
  const relatedTarget = event.relatedTarget as Node
  
  if (!dropZone.contains(relatedTarget)) {
    isDragOver.value = false
  }
}

function handleDrop(event: DragEvent) {
  event.preventDefault()
  isDragOver.value = false
  
  if (!props.draggedTask || !event.dataTransfer) {
    return
  }
  
  const taskId = parseInt(event.dataTransfer.getData('text/plain'))
  
  // If dropping in the same column, don't do anything special for now
  if (props.draggedTask.status === props.status) {
    return
  }
  
  // Calculate new order (add to end of column)
  const newOrder = props.tasks.length
  
  emit('task-move', taskId, props.status, newOrder)
}
</script>

<template>
  <div class="bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 flex flex-col h-fit min-h-[500px]">
    <!-- Column Header -->
    <div :class="['px-4 py-3 border-b rounded-t-lg', colorClasses.header]">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-gray-900 dark:text-white">
          {{ title }}
        </h3>
        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', colorClasses.badge]">
          {{ tasks.length }}
        </span>
      </div>
    </div>
    
    <!-- Drop Zone -->
    <div
      class="flex-1 p-4 space-y-3 min-h-[400px] transition-colors"
      :class="[
        isDragOver ? 'bg-blue-50 dark:bg-blue-900/20' : '',
        isDragOver ? colorClasses.border : ''
      ]"
      @dragover="handleDragOver"
      @dragenter="handleDragEnter"
      @dragleave="handleDragLeave"
      @drop="handleDrop"
    >
      <!-- Empty State -->
      <div 
        v-if="tasks.length === 0" 
        class="flex flex-col items-center justify-center py-12 text-gray-400 dark:text-gray-600"
      >
        <Plus class="h-8 w-8 mb-2" />
        <p class="text-sm font-medium">Nenhuma tarefa</p>
        <p class="text-xs">Arraste tarefas para cá</p>
      </div>
      
      <!-- Tasks -->
      <TaskCardAdvanced
        v-for="task in tasks"
        :key="task.id"
        :task="task"
        :is-dragging="props.draggedTask?.id === task.id"
        @task-click="$emit('task-click', task)"
        @dragstart="handleDragStart"
        @dragend="handleDragEnd"
        class="transition-transform hover:scale-[1.02]"
      />
      
      <!-- Drag Over Indicator -->
      <div 
        v-if="isDragOver" 
        :class="['border-2 border-dashed rounded-lg p-4 text-center text-sm text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800', colorClasses.border]"
      >
        Solte a tarefa aqui
      </div>
    </div>
  </div>
</template>

<style scoped>
.dragging {
  opacity: 0.5;
  transform: rotate(5deg);
}
</style>
