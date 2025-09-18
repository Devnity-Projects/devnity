<script setup lang="ts">
import { reactive } from 'vue'
import { router, useForm, Link } from '@inertiajs/vue3'
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
  ArrowLeft,
  Save
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
  project_id: number
  assigned_to: number | null
  status: string
  priority: string
  type: string
  hours_estimated: number | null
  hours_worked: number | null
  due_date: string | null
  labels: string[]
  acceptance_criteria: string | null
  notes: string | null
}

interface Props {
  task: Task
  projects: Project[]
  users: User[]
  priorities: Record<string, string>
  types: Record<string, string>
  statuses: Record<string, string>
}

const props = defineProps<Props>()

const form = useForm({
  title: props.task.title,
  description: props.task.description || '',
  project_id: props.task.project_id,
  assigned_to: props.task.assigned_to || '',
  status: props.task.status,
  priority: props.task.priority,
  type: props.task.type,
  hours_estimated: props.task.hours_estimated?.toString() || '',
  hours_worked: props.task.hours_worked?.toString() || '',
  due_date: props.task.due_date || '',
  labels: [...props.task.labels],
  acceptance_criteria: props.task.acceptance_criteria || '',
  notes: props.task.notes || ''
})

const labelInput = reactive({ value: '' })

function submit() {
  form.put(route('tasks.update', props.task.id))
}

function addLabel() {
  if (labelInput.value && !form.labels.includes(labelInput.value)) {
    form.labels.push(labelInput.value)
    labelInput.value = ''
  }
}

function removeLabel(index: number) {
  form.labels.splice(index, 1)
}
</script>

<template>
  <AppLayout title="Editar Tarefa">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <div class="flex items-center gap-4 mb-4">
            <Link
              :href="route('tasks.show', task.id)"
              class="flex items-center gap-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
            >
              <ArrowLeft class="h-4 w-4" />
              Voltar para Tarefa
            </Link>
          </div>
          
          <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
              Editar Tarefa
            </h2>
            <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
              <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                <Flag class="mr-1.5 h-5 w-5 flex-shrink-0" />
                Modificar informações da tarefa
              </div>
            </div>
          </div>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
          <form @submit.prevent="submit" class="space-y-6 p-6">
            <!-- Basic Information -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- Title -->
              <div class="lg:col-span-2">
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Título da Tarefa *
                </label>
                <input
                  id="title"
                  v-model="form.title"
                  type="text"
                  required
                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                  :class="{ 'border-red-500': form.errors.title }"
                />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.title }}
                </p>
              </div>

              <!-- Project -->
              <div>
                <label for="project_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Projeto *
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <FolderOpen class="h-5 w-5 text-gray-400" />
                  </div>
                  <select
                    id="project_id"
                    v-model="form.project_id"
                    required
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                    :class="{ 'border-red-500': form.errors.project_id }"
                  >
                    <option value="">Selecione um projeto</option>
                    <option v-for="project in projects" :key="project.id" :value="project.id">
                      {{ project.name }}
                    </option>
                  </select>
                </div>
                <p v-if="form.errors.project_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.project_id }}
                </p>
              </div>

              <!-- Assigned User -->
              <div>
                <label for="assigned_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Responsável
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <User class="h-5 w-5 text-gray-400" />
                  </div>
                  <select
                    id="assigned_to"
                    v-model="form.assigned_to"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                    :class="{ 'border-red-500': form.errors.assigned_to }"
                  >
                    <option value="">Não atribuído</option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                      {{ user.name }}
                    </option>
                  </select>
                </div>
                <p v-if="form.errors.assigned_to" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.assigned_to }}
                </p>
              </div>

              <!-- Status -->
              <div>
                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Status
                </label>
                <select
                  id="status"
                  v-model="form.status"
                  class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                  :class="{ 'border-red-500': form.errors.status }"
                >
                  <option v-for="(label, value) in statuses" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <p v-if="form.errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.status }}
                </p>
              </div>

              <!-- Priority -->
              <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Prioridade
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Flag class="h-5 w-5 text-gray-400" />
                  </div>
                  <select
                    id="priority"
                    v-model="form.priority"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                    :class="{ 'border-red-500': form.errors.priority }"
                  >
                    <option v-for="(label, value) in priorities" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </select>
                </div>
                <p v-if="form.errors.priority" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.priority }}
                </p>
              </div>

              <!-- Type -->
              <div>
                <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Tipo
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Type class="h-5 w-5 text-gray-400" />
                  </div>
                  <select
                    id="type"
                    v-model="form.type"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                    :class="{ 'border-red-500': form.errors.type }"
                  >
                    <option v-for="(label, value) in types" :key="value" :value="value">
                      {{ label }}
                    </option>
                  </select>
                </div>
                <p v-if="form.errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.type }}
                </p>
              </div>

              <!-- Hours Estimated -->
              <div>
                <label for="hours_estimated" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Horas Estimadas
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Clock class="h-5 w-5 text-gray-400" />
                  </div>
                  <input
                    id="hours_estimated"
                    v-model="form.hours_estimated"
                    type="number"
                    step="0.5"
                    min="0"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                    :class="{ 'border-red-500': form.errors.hours_estimated }"
                  />
                </div>
                <p v-if="form.errors.hours_estimated" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.hours_estimated }}
                </p>
              </div>

              <!-- Hours Worked -->
              <div>
                <label for="hours_worked" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Horas Trabalhadas
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Clock class="h-5 w-5 text-gray-400" />
                  </div>
                  <input
                    id="hours_worked"
                    v-model="form.hours_worked"
                    type="number"
                    step="0.5"
                    min="0"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                    :class="{ 'border-red-500': form.errors.hours_worked }"
                  />
                </div>
                <p v-if="form.errors.hours_worked" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.hours_worked }}
                </p>
              </div>

              <!-- Due Date -->
              <div>
                <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Data de Entrega
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <Calendar class="h-5 w-5 text-gray-400" />
                  </div>
                  <input
                    id="due_date"
                    v-model="form.due_date"
                    type="date"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                    :class="{ 'border-red-500': form.errors.due_date }"
                  />
                </div>
                <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.due_date }}
                </p>
              </div>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Descrição
              </label>
              <div class="relative">
                <div class="absolute top-3 left-3 pointer-events-none">
                  <FileText class="h-5 w-5 text-gray-400" />
                </div>
                <textarea
                  id="description"
                  v-model="form.description"
                  rows="4"
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white resize-none"
                  :class="{ 'border-red-500': form.errors.description }"
                  placeholder="Descreva os detalhes da tarefa..."
                />
              </div>
              <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.description }}
              </p>
            </div>

            <!-- Labels -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Labels/Tags
              </label>
              <div class="flex flex-wrap gap-2 mb-2">
                <span
                  v-for="(label, index) in form.labels"
                  :key="index"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400"
                >
                  <Tag class="w-3 h-3 mr-1" />
                  {{ label }}
                  <button
                    @click="removeLabel(index)"
                    type="button"
                    class="ml-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                  >
                    ×
                  </button>
                </span>
              </div>
              <div class="flex gap-2">
                <input
                  v-model="labelInput.value"
                  @keydown.enter.prevent="addLabel"
                  type="text"
                  placeholder="Digite uma label e pressione Enter"
                  class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white text-sm"
                />
                <button
                  @click="addLabel"
                  type="button"
                  class="px-3 py-2 bg-blue-100 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 rounded-md hover:bg-blue-200 dark:hover:bg-blue-900/40 transition-colors text-sm"
                >
                  Adicionar
                </button>
              </div>
            </div>

            <!-- Acceptance Criteria -->
            <div>
              <label for="acceptance_criteria" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Critérios de Aceitação
              </label>
              <div class="relative">
                <div class="absolute top-3 left-3 pointer-events-none">
                  <CheckCircle class="h-5 w-5 text-gray-400" />
                </div>
                <textarea
                  id="acceptance_criteria"
                  v-model="form.acceptance_criteria"
                  rows="3"
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white resize-none"
                  :class="{ 'border-red-500': form.errors.acceptance_criteria }"
                  placeholder="Defina os critérios que devem ser atendidos para considerar a tarefa como concluída..."
                />
              </div>
              <p v-if="form.errors.acceptance_criteria" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.acceptance_criteria }}
              </p>
            </div>

            <!-- Notes -->
            <div>
              <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Observações
              </label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-800 text-gray-900 dark:text-white resize-none"
                :class="{ 'border-red-500': form.errors.notes }"
                placeholder="Observações adicionais sobre a tarefa..."
              />
              <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ form.errors.notes }}
              </p>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
              <Link
                :href="route('tasks.show', task.id)"
                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
              >
                Cancelar
              </Link>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex items-center gap-2 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                <Save class="h-4 w-4" />
                <span v-if="form.processing">Salvando...</span>
                <span v-else>Salvar Alterações</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
