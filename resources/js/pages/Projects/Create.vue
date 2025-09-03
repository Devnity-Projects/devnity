<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { 
  Save, 
  X, 
  FolderKanban,
  User,
  Calendar,
  DollarSign,
  Clock,
  AlertTriangle,
  Plus,
  Trash2
} from 'lucide-vue-next'

interface Client {
  id: number
  name: string
}

interface Props {
  clients: Client[]
  statuses: Record<string, string>
  priorities: Record<string, string>
  types: Record<string, string>
}

const props = defineProps<Props>()

const form = ref({
  name: '',
  description: '',
  client_id: '',
  status: 'planning',
  priority: 'medium',
  type: 'development',
  budget: null as number | null,
  hours_estimated: null as number | null,
  start_date: '',
  end_date: '',
  deadline: '',
  technologies: [] as string[],
  repository_url: '',
  demo_url: '',
  production_url: '',
  notes: '',
})

const processing = ref(false)
const errors = ref<Record<string, string>>({})
const newTechnology = ref('')

const isFormValid = computed(() => {
  return form.value.name && form.value.client_id && form.value.status && form.value.priority && form.value.type
})

function addTechnology() {
  if (newTechnology.value.trim() && !form.value.technologies.includes(newTechnology.value.trim())) {
    form.value.technologies.push(newTechnology.value.trim())
    newTechnology.value = ''
  }
}

function removeTechnology(index: number) {
  form.value.technologies.splice(index, 1)
}

function submitForm() {
  if (!isFormValid.value) return

  processing.value = true
  errors.value = {}

  router.post('/projects', form.value, {
    preserveScroll: true,
    onError: (pageErrors) => {
      errors.value = pageErrors
      processing.value = false
    },
    onFinish: () => {
      processing.value = false
    }
  })
}

function goBack() {
  router.get('/projects')
}
</script>

<template>
  <AppLayout title="Criar Projeto">
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header Simples -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <FolderKanban class="h-6 w-6 text-gray-600" />
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Novo Projeto</h1>
        </div>
        <button
          @click="goBack"
          type="button"
          class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors"
        >
          <X class="h-4 w-4" />
          Voltar
        </button>
      </div>

      <!-- Formulário -->
      <form @submit.prevent="submitForm" class="space-y-6">
        <!-- Informações Básicas -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Nome do Projeto *
              </label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Digite o nome do projeto"
                required
              />
              <p v-if="errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.name }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Cliente *
              </label>
              <select
                v-model="form.client_id"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option value="">Selecione um cliente</option>
                <option v-for="client in props.clients" :key="client.id" :value="client.id">
                  {{ client.name }}
                </option>
              </select>
              <p v-if="errors.client_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.client_id }}
              </p>
            </div>
          </div>

          <!-- Descrição -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Descrição
            </label>
            <textarea
              v-model="form.description"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              rows="3"
              placeholder="Descreva os objetivos e escopo do projeto"
            />
            <p v-if="errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.description }}
            </p>
          </div>

          <!-- Status, Prioridade e Tipo -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Status *
              </label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option v-for="(label, value) in props.statuses" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="errors.status" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.status }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Prioridade *
              </label>
              <select
                v-model="form.priority"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option v-for="(label, value) in props.priorities" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="errors.priority" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.priority }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tipo *
              </label>
              <select
                v-model="form.type"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                required
              >
                <option v-for="(label, value) in props.types" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <p v-if="errors.type" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.type }}
              </p>
            </div>
          </div>
        </div>

        <!-- Detalhes Financeiros -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Detalhes Financeiros</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Orçamento (R$)
              </label>
              <input
                v-model.number="form.budget"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="0,00"
              />
              <p v-if="errors.budget" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.budget }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Horas Estimadas
              </label>
              <input
                v-model.number="form.hours_estimated"
                type="number"
                step="0.5"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="0"
              />
              <p v-if="errors.hours_estimated" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.hours_estimated }}
              </p>
            </div>
          </div>
        </div>

        <!-- Cronograma -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Cronograma</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Data de Início
              </label>
              <input
                v-model="form.start_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <p v-if="errors.start_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.start_date }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Data de Fim
              </label>
              <input
                v-model="form.end_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <p v-if="errors.end_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.end_date }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Prazo Final
              </label>
              <input
                v-model="form.deadline"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
              <p v-if="errors.deadline" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.deadline }}
              </p>
            </div>
          </div>
        </div>

        <!-- Tecnologias e URLs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Tecnologias e URLs</h3>
          
          <!-- Tecnologias -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Tecnologias
            </label>
            <div class="flex gap-2 mb-3">
              <input
                v-model="newTechnology"
                type="text"
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Digite uma tecnologia"
                @keyup.enter="addTechnology"
              />
              <button
                type="button"
                @click="addTechnology"
                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
              >
                <Plus class="h-4 w-4" />
                Adicionar
              </button>
            </div>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="(tech, index) in form.technologies"
                :key="index"
                class="inline-flex items-center gap-2 px-3 py-1 bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-300 rounded-full text-sm"
              >
                {{ tech }}
                <button
                  type="button"
                  @click="removeTechnology(index)"
                  class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  <X class="h-3 w-3" />
                </button>
              </span>
            </div>
            <p v-if="errors.technologies" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.technologies }}
            </p>
          </div>

          <!-- URLs -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                URL do Repositório
              </label>
              <input
                v-model="form.repository_url"
                type="url"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="https://github.com/..."
              />
              <p v-if="errors.repository_url" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.repository_url }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                URL de Demo
              </label>
              <input
                v-model="form.demo_url"
                type="url"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="https://demo.example.com"
              />
              <p v-if="errors.demo_url" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.demo_url }}
              </p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                URL de Produção
              </label>
              <input
                v-model="form.production_url"
                type="url"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="https://example.com"
              />
              <p v-if="errors.production_url" class="mt-1 text-sm text-red-600 dark:text-red-400">
                {{ errors.production_url }}
              </p>
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Observações</h3>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Notas do Projeto
            </label>
            <textarea
              v-model="form.notes"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              rows="4"
              placeholder="Observações gerais sobre o projeto"
            />
            <p v-if="errors.notes" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ errors.notes }}
            </p>
          </div>
        </div>

        <!-- Botões -->
        <div class="flex justify-end gap-4">
          <button
            type="button"
            @click="goBack"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
            :disabled="processing"
          >
            <X class="h-4 w-4 mr-2 inline" />
            Cancelar
          </button>
          
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors disabled:opacity-50"
            :disabled="!isFormValid || processing"
          >
            <Save class="h-4 w-4 mr-2 inline" />
            {{ processing ? 'Salvando...' : 'Criar Projeto' }}
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
