<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { X, Plus, Loader2 } from 'lucide-vue-next'

interface Project {
  id: number
  name: string
}

interface Props {
  projects: Project[]
  statuses: Record<string, string>
  priorities: Record<string, string>
}

interface Emits {
  (event: 'close'): void
  (event: 'created', task: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Form data
const form = ref({
  title: '',
  project_id: '',
  status: 'todo',
  priority: 'medium'
})

const isLoading = ref(false)
const errors = ref<Record<string, string>>({})

// Methods
function handleSubmit() {
  if (!form.value.title.trim()) {
    errors.value.title = 'O título é obrigatório'
    return
  }
  
  if (!form.value.project_id) {
    errors.value.project_id = 'O projeto é obrigatório'
    return
  }
  
  errors.value = {}
  isLoading.value = true
  
  router.post(route('kanban.quick-create'), form.value, {
    onSuccess: (page) => {
      // Extract the created task from response
      const response = page.props.flash as any
      if (response?.task) {
        emit('created', response.task)
      }
      resetForm()
      emit('close')
    },
    onError: (pageErrors) => {
      errors.value = pageErrors as Record<string, string>
    },
    onFinish: () => {
      isLoading.value = false
    }
  })
}

function resetForm() {
  form.value = {
    title: '',
    project_id: '',
    status: 'todo',
    priority: 'medium'
  }
  errors.value = {}
}

function handleClose() {
  resetForm()
  emit('close')
}
</script>

<template>
  <!-- Modal Backdrop -->
  <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div 
      class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md"
      @click.stop
    >
      <!-- Header -->
      <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
          Nova Tarefa Rápida
        </h2>
        <button
          @click="handleClose"
          class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
        >
          <X class="h-5 w-5" />
        </button>
      </div>
      
      <!-- Form -->
      <form @submit.prevent="handleSubmit" class="p-6 space-y-4">
        <!-- Title -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Título *
          </label>
          <input
            v-model="form.title"
            type="text"
            placeholder="Ex: Implementar login com Google"
            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            :class="{ 'border-red-500 dark:border-red-400': errors.title }"
          />
          <p v-if="errors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ errors.title }}
          </p>
        </div>
        
        <!-- Project -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Projeto *
          </label>
          <select
            v-model="form.project_id"
            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            :class="{ 'border-red-500 dark:border-red-400': errors.project_id }"
          >
            <option value="">Selecione um projeto</option>
            <option v-for="project in projects" :key="project.id" :value="project.id">
              {{ project.name }}
            </option>
          </select>
          <p v-if="errors.project_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
            {{ errors.project_id }}
          </p>
        </div>
        
        <!-- Status -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Status
          </label>
          <select
            v-model="form.status"
            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option v-for="(label, value) in statuses" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
        </div>
        
        <!-- Priority -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Prioridade
          </label>
          <select
            v-model="form.priority"
            class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
          >
            <option v-for="(label, value) in priorities" :key="value" :value="value">
              {{ label }}
            </option>
          </select>
        </div>
        
        <!-- Actions -->
        <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
          <button
            type="button"
            @click="handleClose"
            class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            :disabled="isLoading"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="isLoading"
          >
            <Loader2 v-if="isLoading" class="h-4 w-4 animate-spin" />
            <Plus v-else class="h-4 w-4" />
            {{ isLoading ? 'Criando...' : 'Criar Tarefa' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
