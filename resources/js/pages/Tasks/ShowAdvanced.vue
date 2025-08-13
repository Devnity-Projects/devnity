<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import ConfirmationModal from '@/components/ConfirmationModal.vue'
import {
  ArrowLeft,
  Calendar,
  Clock,
  User,
  Flag,
  Edit,
  Trash2,
  MessageCircle,
  Paperclip,
  CheckSquare,
  Plus,
  Send,
  Download,
  X,
  Activity,
  Eye,
  EyeOff,
  FolderOpen,
  Hash,
  Bug,
  Lightbulb,
  FileText,
  TestTube,
  Wrench,
  AlertTriangle
} from 'lucide-vue-next'

interface User {
  id: number
  name: string
  email: string
}

interface Project {
  id: number
  name: string
}

interface TaskComment {
  id: number
  content: string
  user: User
  is_internal: boolean
  created_at: string
  updated_at: string
}

interface TaskAttachment {
  id: number
  filename: string
  original_name: string
  size: number
  path: string
  mime_type: string
  user: User
  created_at: string
}

interface TaskChecklist {
  id: number
  title: string
  description?: string | null
  is_completed: boolean
  assigned_to?: number | null
  assignedTo?: User | null
  due_date?: string | null
  order: number
  created_at: string
}

interface TaskActivity {
  id: number
  user: User
  type: string
  description: string
  changes: Record<string, any> | null
  created_at: string
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
  is_overdue: boolean
  status_label: string
  priority_label: string
  type_label: string
  time_spent: string
  created_at: string
  updated_at: string
  comments: TaskComment[]
  attachments: TaskAttachment[]
  checklist: TaskChecklist[]
  activities: TaskActivity[]
}

interface Props {
  task: Task
  projects: Project[]
  users: User[]
  statuses: Record<string, string>
  priorities: Record<string, string>
  types: Record<string, string>
}

const props = defineProps<Props>()

const showDeleteModal = ref(false)
const showActivities = ref(false)
const activeTab = ref<'details' | 'comments' | 'attachments' | 'checklist'>('details')

// Comment form
const commentForm = useForm({
  content: '',
  is_internal: false
})

// Checklist form
const checklistForm = useForm({
  title: ''
})

// File upload
const fileInput = ref<HTMLInputElement>()
const uploadingFile = ref(false)

const priorityColors = computed(() => {
  const colors: Record<string, { bg: string; text: string; border: string }> = {
    low: { bg: 'bg-green-100 dark:bg-green-900/20', text: 'text-green-800 dark:text-green-300', border: 'border-green-200 dark:border-green-800' },
    medium: { bg: 'bg-yellow-100 dark:bg-yellow-900/20', text: 'text-yellow-800 dark:text-yellow-300', border: 'border-yellow-200 dark:border-yellow-800' },
    high: { bg: 'bg-orange-100 dark:bg-orange-900/20', text: 'text-orange-800 dark:text-orange-300', border: 'border-orange-200 dark:border-orange-800' },
    urgent: { bg: 'bg-red-100 dark:bg-red-900/20', text: 'text-red-800 dark:text-red-300', border: 'border-red-200 dark:border-red-800' }
  }
  return colors[props.task.priority] || colors.medium
})

const statusColors = computed(() => {
  const colors: Record<string, { bg: string; text: string }> = {
    todo: { bg: 'bg-gray-100 dark:bg-gray-800', text: 'text-gray-800 dark:text-gray-300' },
    in_progress: { bg: 'bg-blue-100 dark:bg-blue-900/20', text: 'text-blue-800 dark:text-blue-300' },
    review: { bg: 'bg-purple-100 dark:bg-purple-900/20', text: 'text-purple-800 dark:text-purple-300' },
    completed: { bg: 'bg-green-100 dark:bg-green-900/20', text: 'text-green-800 dark:text-green-300' }
  }
  return colors[props.task.status] || colors.todo
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

const formatDate = (date: string): string => {
  return new Intl.DateTimeFormat('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(new Date(date))
}

const formatFileSize = (bytes: number): string => {
  const sizes = ['B', 'KB', 'MB', 'GB']
  if (bytes === 0) return '0 B'
  const i = Math.floor(Math.log(bytes) / Math.log(1024))
  return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
}

const getUserInitials = (name: string): string => {
  return name
    .split(' ')
    .map(word => word.charAt(0).toUpperCase())
    .join('')
    .substring(0, 2)
}

const checklistProgress = computed(() => {
  if (props.task.checklist.length === 0) return 0
  const completed = props.task.checklist.filter(item => item.is_completed).length
  return (completed / props.task.checklist.length) * 100
})

// Methods
const goBack = () => {
  window.history.back()
}

const editTask = () => {
  router.visit(route('tasks.edit', props.task.id))
}

const deleteTask = () => {
  router.delete(route('tasks.destroy', props.task.id), {
    onSuccess: () => router.visit(route('tasks.index'))
  })
  showDeleteModal.value = false
}

const submitComment = () => {
  commentForm.post(route('tasks.comments.store', props.task.id), {
    onSuccess: () => {
      commentForm.reset()
    }
  })
}

const submitChecklistItem = () => {
  checklistForm.post(route('tasks.checklist.store', props.task.id), {
    onSuccess: () => {
      checklistForm.reset()
    }
  })
}

const toggleChecklistItem = (item: TaskChecklist) => {
  router.patch(route('tasks.checklist.update', [props.task.id, item.id]), {
    is_completed: !item.is_completed
  })
}

const deleteChecklistItem = (item: TaskChecklist) => {
  router.delete(route('tasks.checklist.destroy', [props.task.id, item.id]))
}

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    uploadingFile.value = true
    const formData = new FormData()
    formData.append('file', file)
    
    router.post(route('tasks.attachments.store', props.task.id), formData, {
      onFinish: () => {
        uploadingFile.value = false
        if (fileInput.value) {
          fileInput.value.value = ''
        }
      }
    })
  }
}

const downloadAttachment = (attachment: TaskAttachment) => {
  window.open(route('tasks.attachments.download', [props.task.id, attachment.id]))
}

const deleteAttachment = (attachment: TaskAttachment) => {
  router.delete(route('tasks.attachments.destroy', [props.task.id, attachment.id]))
}

const deleteComment = (comment: TaskComment) => {
  router.delete(route('tasks.comments.destroy', [props.task.id, comment.id]))
}
</script>

<template>
  <AppLayout title="Detalhes da Tarefa">
    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <button
                @click="goBack"
                class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
              >
                <ArrowLeft class="h-5 w-5" />
                Voltar
              </button>
              
              <div class="flex items-center gap-2">
                <component :is="typeIcon" class="h-6 w-6 text-gray-500" />
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                  {{ task.title }}
                </h1>
              </div>
            </div>
            
            <div class="flex items-center gap-2">
              <button
                @click="editTask"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                <Edit class="h-4 w-4" />
                Editar
              </button>
              
              <button
                @click="showDeleteModal = true"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
              >
                <Trash2 class="h-4 w-4" />
                Excluir
              </button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Main Content -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Task Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <!-- Status -->
              <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-2">
                  <Hash class="h-4 w-4 text-gray-400" />
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</span>
                </div>
                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium', statusColors.bg, statusColors.text]">
                  {{ task.status_label }}
                </span>
              </div>

              <!-- Priority -->
              <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-2">
                  <Flag class="h-4 w-4 text-gray-400" />
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Prioridade</span>
                </div>
                <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium', priorityColors.bg, priorityColors.text]">
                  {{ task.priority_label }}
                </span>
                <AlertTriangle v-if="task.is_overdue" class="h-4 w-4 text-red-500 ml-2" />
              </div>

              <!-- Project -->
              <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-2 mb-2">
                  <FolderOpen class="h-4 w-4 text-gray-400" />
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Projeto</span>
                </div>
                <p class="text-sm text-gray-900 dark:text-white font-medium">{{ task.project.name }}</p>
              </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700">
              <nav class="-mb-px flex space-x-8">
                <button
                  @click="activeTab = 'details'"
                  :class="[
                    'py-2 px-1 border-b-2 font-medium text-sm',
                    activeTab === 'details'
                      ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                      : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'
                  ]"
                >
                  Detalhes
                </button>
                
                <button
                  @click="activeTab = 'comments'"
                  :class="[
                    'py-2 px-1 border-b-2 font-medium text-sm flex items-center gap-2',
                    activeTab === 'comments'
                      ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                      : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'
                  ]"
                >
                  <MessageCircle class="h-4 w-4" />
                  Comentários
                  <span v-if="task.comments.length > 0" class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full text-xs">
                    {{ task.comments.length }}
                  </span>
                </button>
                
                <button
                  @click="activeTab = 'attachments'"
                  :class="[
                    'py-2 px-1 border-b-2 font-medium text-sm flex items-center gap-2',
                    activeTab === 'attachments'
                      ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                      : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'
                  ]"
                >
                  <Paperclip class="h-4 w-4" />
                  Anexos
                  <span v-if="task.attachments.length > 0" class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full text-xs">
                    {{ task.attachments.length }}
                  </span>
                </button>
                
                <button
                  @click="activeTab = 'checklist'"
                  :class="[
                    'py-2 px-1 border-b-2 font-medium text-sm flex items-center gap-2',
                    activeTab === 'checklist'
                      ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                      : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 hover:border-gray-300'
                  ]"
                >
                  <CheckSquare class="h-4 w-4" />
                  Checklist
                  <span v-if="task.checklist.length > 0" class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-2 py-0.5 rounded-full text-xs">
                    {{ task.checklist.filter(item => item.is_completed).length }}/{{ task.checklist.length }}
                  </span>
                </button>
              </nav>
            </div>

            <!-- Tab Content -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
              <!-- Details Tab -->
              <div v-if="activeTab === 'details'" class="p-6">
                <div v-if="task.description" class="mb-6">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Descrição</h3>
                  <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ task.description }}</p>
                </div>

                <!-- Labels -->
                <div v-if="task.labels.length > 0" class="mb-6">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Labels</h3>
                  <div class="flex flex-wrap gap-2">
                    <span
                      v-for="label in task.labels"
                      :key="label"
                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                    >
                      {{ label }}
                    </span>
                  </div>
                </div>

                <!-- Time Tracking -->
                <div v-if="task.hours_estimated" class="mb-6">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tempo</h3>
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <p class="text-sm text-gray-600 dark:text-gray-400">Estimado</p>
                      <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ task.hours_estimated }}h</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-600 dark:text-gray-400">Trabalhado</p>
                      <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ task.time_spent }}</p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-600 dark:text-gray-400">Progresso</p>
                      <div class="flex items-center gap-2">
                        <div class="flex-1 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                          <div
                            class="bg-blue-500 dark:bg-blue-600 h-2 rounded-full"
                            :style="{ width: Math.min(100, (task.hours_worked || 0) / task.hours_estimated * 100) + '%' }"
                          />
                        </div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">
                          {{ Math.round(Math.min(100, (task.hours_worked || 0) / task.hours_estimated * 100)) }}%
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Comments Tab -->
              <div v-if="activeTab === 'comments'" class="p-6">
                <!-- Comment Form -->
                <form @submit.prevent="submitComment" class="mb-6">
                  <div class="flex items-start gap-4">
                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-sm font-medium text-white">
                      {{ getUserInitials($page.props.auth.user.name) }}
                    </div>
                    <div class="flex-1">
                      <textarea
                        v-model="commentForm.content"
                        rows="3"
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        placeholder="Adicione um comentário..."
                      ></textarea>
                      
                      <div class="flex items-center justify-between mt-3">
                        <label class="flex items-center">
                          <input
                            v-model="commentForm.is_internal"
                            type="checkbox"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700"
                          />
                          <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Comentário interno</span>
                        </label>
                        
                        <button
                          type="submit"
                          :disabled="!commentForm.content.trim() || commentForm.processing"
                          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                        >
                          <Send class="h-4 w-4" />
                          Comentar
                        </button>
                      </div>
                    </div>
                  </div>
                </form>

                <!-- Comments List -->
                <div class="space-y-4">
                  <div
                    v-for="comment in task.comments"
                    :key="comment.id"
                    class="flex items-start gap-4"
                  >
                    <div class="w-8 h-8 rounded-full bg-gray-500 flex items-center justify-center text-sm font-medium text-white">
                      {{ getUserInitials(comment.user.name) }}
                    </div>
                    
                    <div class="flex-1">
                      <div :class="['rounded-lg p-4', comment.is_internal ? 'bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800' : 'bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700']">
                        <div class="flex items-center justify-between mb-2">
                          <div class="flex items-center gap-2">
                            <span class="font-medium text-gray-900 dark:text-white">{{ comment.user.name }}</span>
                            <span v-if="comment.is_internal" class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                              <EyeOff class="h-3 w-3 mr-1" />
                              Interno
                            </span>
                          </div>
                          
                          <div class="flex items-center gap-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                              {{ formatDate(comment.created_at) }}
                            </span>
                            <button
                              @click="deleteComment(comment)"
                              class="text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                            >
                              <X class="h-4 w-4" />
                            </button>
                          </div>
                        </div>
                        
                        <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ comment.content }}</p>
                      </div>
                    </div>
                  </div>
                  
                  <div v-if="task.comments.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <MessageCircle class="h-8 w-8 mx-auto mb-2" />
                    <p>Nenhum comentário ainda</p>
                    <p class="text-sm">Seja o primeiro a comentar nesta tarefa</p>
                  </div>
                </div>
              </div>

              <!-- Attachments Tab -->
              <div v-if="activeTab === 'attachments'" class="p-6">
                <!-- Upload Form -->
                <div class="mb-6">
                  <input
                    ref="fileInput"
                    type="file"
                    @change="handleFileUpload"
                    class="hidden"
                  />
                  
                  <button
                    @click="fileInput?.click()"
                    :disabled="uploadingFile"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                  >
                    <Plus class="h-4 w-4" />
                    {{ uploadingFile ? 'Enviando...' : 'Adicionar Arquivo' }}
                  </button>
                </div>

                <!-- Attachments List -->
                <div class="space-y-3">
                  <div
                    v-for="attachment in task.attachments"
                    :key="attachment.id"
                    class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700"
                  >
                    <div class="flex items-center gap-3">
                      <Paperclip class="h-5 w-5 text-gray-400" />
                      <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ attachment.original_name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                          {{ formatFileSize(attachment.size) }} • {{ formatDate(attachment.created_at) }} • {{ attachment.user.name }}
                        </p>
                      </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                      <button
                        @click="downloadAttachment(attachment)"
                        class="inline-flex items-center gap-1 px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                      >
                        <Download class="h-3 w-3" />
                        Download
                      </button>
                      
                      <button
                        @click="deleteAttachment(attachment)"
                        class="inline-flex items-center gap-1 px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                      >
                        <Trash2 class="h-3 w-3" />
                        Excluir
                      </button>
                    </div>
                  </div>
                  
                  <div v-if="task.attachments.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <Paperclip class="h-8 w-8 mx-auto mb-2" />
                    <p>Nenhum arquivo anexado</p>
                    <p class="text-sm">Clique em "Adicionar Arquivo" para enviar arquivos</p>
                  </div>
                </div>
              </div>

              <!-- Checklist Tab -->
              <div v-if="activeTab === 'checklist'" class="p-6">
                <!-- Progress Bar -->
                <div v-if="task.checklist.length > 0" class="mb-6">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progresso</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                      {{ task.checklist.filter(item => item.is_completed).length }}/{{ task.checklist.length }} ({{ Math.round(checklistProgress) }}%)
                    </span>
                  </div>
                  <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div
                      class="bg-green-500 dark:bg-green-600 h-2 rounded-full transition-all duration-300"
                      :style="{ width: checklistProgress + '%' }"
                    />
                  </div>
                </div>

                <!-- Add Item Form -->
                <form @submit.prevent="submitChecklistItem" class="mb-6">
                  <div class="flex gap-2">
                    <input
                      v-model="checklistForm.title"
                      type="text"
                      class="flex-1 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500"
                      placeholder="Adicionar item..."
                    />
                    <button
                      type="submit"
                      :disabled="!checklistForm.title.trim() || checklistForm.processing"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
                    >
                      <Plus class="h-4 w-4" />
                      Adicionar
                    </button>
                  </div>
                </form>

                <!-- Checklist Items -->
                <div class="space-y-2">
                  <div
                    v-for="item in task.checklist"
                    :key="item.id"
                    class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                  >
                    <input
                      :checked="item.is_completed"
                      @change="toggleChecklistItem(item)"
                      type="checkbox"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 dark:bg-gray-900 dark:border-gray-700"
                    />
                    
                    <div class="flex-1">
                      <p :class="['text-sm', item.is_completed ? 'line-through text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-white']">
                        {{ item.title }}
                      </p>
                      <p v-if="item.assignedTo" class="text-xs text-gray-500 dark:text-gray-400">
                        Atribuído para {{ item.assignedTo.name }}
                      </p>
                    </div>
                    
                    <button
                      @click="deleteChecklistItem(item)"
                      class="text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                    >
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                  
                  <div v-if="task.checklist.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <CheckSquare class="h-8 w-8 mx-auto mb-2" />
                    <p>Nenhum item na lista</p>
                    <p class="text-sm">Adicione itens para organizar melhor sua tarefa</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Task Meta -->
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 border border-gray-200 dark:border-gray-700">
              <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informações</h3>
              
              <dl class="space-y-4">
                <div v-if="task.assigned_user">
                  <dt class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    <User class="h-4 w-4" />
                    Responsável
                  </dt>
                  <dd class="mt-1">
                    <div class="flex items-center gap-2">
                      <div class="w-6 h-6 rounded-full bg-blue-500 flex items-center justify-center text-xs font-medium text-white">
                        {{ getUserInitials(task.assigned_user.name) }}
                      </div>
                      <span class="text-sm text-gray-900 dark:text-white">{{ task.assigned_user.name }}</span>
                    </div>
                  </dd>
                </div>
                
                <div v-if="task.due_date">
                  <dt class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    <Calendar class="h-4 w-4" />
                    Prazo
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ formatDate(task.due_date) }}
                  </dd>
                </div>
                
                <div>
                  <dt class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    <Clock class="h-4 w-4" />
                    Criado em
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ formatDate(task.created_at) }}
                  </dd>
                </div>
                
                <div>
                  <dt class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400">
                    <Clock class="h-4 w-4" />
                    Atualizado em
                  </dt>
                  <dd class="mt-1 text-sm text-gray-900 dark:text-white">
                    {{ formatDate(task.updated_at) }}
                  </dd>
                </div>
              </dl>
            </div>

            <!-- Activity Feed -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
              <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="text-lg font-medium text-gray-900 dark:text-white">Atividade</h3>
                  <button
                    @click="showActivities = !showActivities"
                    class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300"
                  >
                    {{ showActivities ? 'Ocultar' : 'Ver todas' }}
                  </button>
                </div>
                
                <div :class="['space-y-3', showActivities ? '' : 'max-h-60 overflow-hidden']">
                  <div
                    v-for="activity in task.activities"
                    :key="activity.id"
                    class="flex items-start gap-3"
                  >
                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                      <Activity class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                      <p class="text-sm text-gray-900 dark:text-white">
                        <span class="font-medium">{{ activity.user.name }}</span>
                        {{ activity.description }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(activity.created_at) }}</p>
                    </div>
                  </div>
                  
                  <div v-if="task.activities.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
                    <Activity class="h-6 w-6 mx-auto mb-2" />
                    <p class="text-sm">Nenhuma atividade registrada</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="deleteTask"
      title="Excluir Tarefa"
      content="Tem certeza que deseja excluir esta tarefa? Esta ação não pode ser desfeita."
      confirm-text="Excluir"
      cancel-text="Cancelar"
    />
  </AppLayout>
</template>
