<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { 
  Save, 
  ArrowLeft, 
  DollarSign,
  Calendar,
  Tag,
  User,
  FolderOpen,
  CreditCard,
  FileText,
  AlertCircle
} from 'lucide-vue-next'

interface Props {
  categories: any[]
  clients: any[]
  projects: any[]
  typeOptions: Record<string, string>
  recurrenceOptions: Record<string, string>
}

const props = defineProps<Props>()

// Form data
const form = useForm({
  title: '',
  description: '',
  type: 'expense',
  amount: '',
  due_date: '',
  category_id: '',
  client_id: '',
  project_id: '',
  payment_method: '',
  notes: '',
  recurrence: 'none',
  installments: 1,
})

// Computed
const filteredCategories = computed(() => {
  return props.categories.filter(category => category.type === form.type)
})

const showInstallments = computed(() => {
  return form.recurrence !== 'none' && form.installments > 1
})

// Methods
const submit = () => {
  form.post(route('financial.transactions.store'), {
    onSuccess: () => {
      // Redirect handled by controller
    }
  })
}

const formatCurrency = (value: string) => {
  // Remove non-numeric characters
  const numericValue = value.replace(/\D/g, '')
  
  // Convert to decimal
  const decimalValue = parseInt(numericValue) / 100
  
  // Format as currency
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(decimalValue)
}

const handleAmountInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  const value = target.value
  
  // Remove non-numeric characters
  const numericValue = value.replace(/\D/g, '')
  
  // Convert to decimal and update form
  form.amount = (parseInt(numericValue || '0') / 100).toString()
  
  // Update display value
  target.value = formatCurrency(value)
}

const getTodayDate = () => {
  return new Date().toISOString().split('T')[0]
}

// Set default due date to today
if (!form.due_date) {
  form.due_date = getTodayDate()
}
</script>

<template>
  <Head title="Nova Transação" />

  <AppLayout>
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center gap-4 mb-8">
          <button
            @click="$inertia.visit(route('financial.transactions.index'))"
            class="p-2 rounded-lg border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
          >
            <ArrowLeft class="h-5 w-5" />
          </button>
          
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Nova Transação
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
              Crie uma nova receita ou despesa
            </p>
          </div>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-8">
          <!-- Transaction Type -->
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Tipo de Transação
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <label
                class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                :class="form.type === 'income' 
                  ? 'border-green-500 bg-green-50 dark:bg-green-900/20' 
                  : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
              >
                <input
                  v-model="form.type"
                  type="radio"
                  value="income"
                  class="sr-only"
                />
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                    <DollarSign class="h-6 w-6 text-green-600" />
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">Receita</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dinheiro que entra</p>
                  </div>
                </div>
              </label>

              <label
                class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-all"
                :class="form.type === 'expense' 
                  ? 'border-red-500 bg-red-50 dark:bg-red-900/20' 
                  : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500'"
              >
                <input
                  v-model="form.type"
                  type="radio"
                  value="expense"
                  class="sr-only"
                />
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-red-100 dark:bg-red-900/30 rounded-lg">
                    <DollarSign class="h-6 w-6 text-red-600" />
                  </div>
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white">Despesa</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Dinheiro que sai</p>
                  </div>
                </div>
              </label>
            </div>
          </div>

          <!-- Basic Information -->
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Informações Básicas
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Title -->
              <div class="md:col-span-2">
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  <FileText class="h-4 w-4" />
                  Título *
                </label>
                <input
                  v-model="form.title"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                  placeholder="Ex: Pagamento de serviços de desenvolvimento"
                />
                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.title }}
                </p>
              </div>

              <!-- Amount -->
              <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  <DollarSign class="h-4 w-4" />
                  Valor *
                </label>
                <input
                  type="text"
                  required
                  @input="handleAmountInput"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                  placeholder="R$ 0,00"
                />
                <p v-if="form.errors.amount" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.amount }}
                </p>
              </div>

              <!-- Due Date -->
              <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  <Calendar class="h-4 w-4" />
                  Data de Vencimento *
                </label>
                <input
                  v-model="form.due_date"
                  type="date"
                  required
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.due_date" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.due_date }}
                </p>
              </div>

              <!-- Category -->
              <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  <Tag class="h-4 w-4" />
                  Categoria *
                </label>
                <select
                  v-model="form.category_id"
                  required
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Selecione uma categoria</option>
                  <option
                    v-for="category in filteredCategories"
                    :key="category.id"
                    :value="category.id"
                  >
                    {{ category.name }}
                  </option>
                </select>
                <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.category_id }}
                </p>
              </div>

              <!-- Payment Method -->
              <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  <CreditCard class="h-4 w-4" />
                  Método de Pagamento
                </label>
                <input
                  v-model="form.payment_method"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                  placeholder="Ex: PIX, Cartão, Boleto, Dinheiro"
                />
                <p v-if="form.errors.payment_method" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.payment_method }}
                </p>
              </div>

              <!-- Description -->
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Descrição
                </label>
                <textarea
                  v-model="form.description"
                  rows="3"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                  placeholder="Descreva os detalhes da transação..."
                ></textarea>
                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.description }}
                </p>
              </div>
            </div>
          </div>

          <!-- Associations -->
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Vinculações (Opcional)
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Client -->
              <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  <User class="h-4 w-4" />
                  Cliente
                </label>
                <select
                  v-model="form.client_id"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Nenhum cliente</option>
                  <option
                    v-for="client in clients"
                    :key="client.id"
                    :value="client.id"
                  >
                    {{ client.name }}
                  </option>
                </select>
                <p v-if="form.errors.client_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.client_id }}
                </p>
              </div>

              <!-- Project -->
              <div>
                <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  <FolderOpen class="h-4 w-4" />
                  Projeto
                </label>
                <select
                  v-model="form.project_id"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                >
                  <option value="">Nenhum projeto</option>
                  <option
                    v-for="project in projects"
                    :key="project.id"
                    :value="project.id"
                  >
                    {{ project.name }}
                  </option>
                </select>
                <p v-if="form.errors.project_id" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.project_id }}
                </p>
              </div>
            </div>
          </div>

          <!-- Recurrence -->
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Recorrência e Parcelas
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Recurrence -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Recorrência
                </label>
                <select
                  v-model="form.recurrence"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                >
                  <option
                    v-for="(label, value) in recurrenceOptions"
                    :key="value"
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>
                <p v-if="form.errors.recurrence" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.recurrence }}
                </p>
              </div>

              <!-- Installments -->
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                  Número de Parcelas
                </label>
                <input
                  v-model.number="form.installments"
                  type="number"
                  min="1"
                  max="360"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                />
                <p v-if="form.errors.installments" class="mt-1 text-sm text-red-600 dark:text-red-400">
                  {{ form.errors.installments }}
                </p>
              </div>
            </div>

            <!-- Installments Preview -->
            <div v-if="showInstallments" class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
              <div class="flex items-center gap-2 mb-2">
                <AlertCircle class="h-4 w-4 text-blue-600" />
                <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
                  Informações sobre as Parcelas
                </p>
              </div>
              <p class="text-sm text-blue-700 dark:text-blue-300">
                Serão criadas {{ form.installments }} parcelas de {{ formatCurrency((parseFloat(form.amount || '0') / form.installments * 100).toString()) }} cada.
                As datas de vencimento serão calculadas baseadas na recorrência selecionada.
              </p>
            </div>
          </div>

          <!-- Notes -->
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
              Observações
            </h2>
            
            <textarea
              v-model="form.notes"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              placeholder="Adicione observações ou informações adicionais sobre esta transação..."
            ></textarea>
            <p v-if="form.errors.notes" class="mt-1 text-sm text-red-600 dark:text-red-400">
              {{ form.errors.notes }}
            </p>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-4">
            <button
              type="button"
              @click="$inertia.visit(route('financial.transactions.index'))"
              class="px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Cancelar
            </button>
            
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              <Save class="h-4 w-4" />
              {{ form.processing ? 'Salvando...' : 'Salvar Transação' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
