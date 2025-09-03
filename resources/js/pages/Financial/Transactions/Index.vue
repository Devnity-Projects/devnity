<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { 
  Plus, 
  Filter, 
  Download, 
  Search, 
  MoreHorizontal,
  Check,
  X,
  Clock,
  AlertTriangle,
  Eye,
  Edit,
  Trash2,
  DollarSign
} from 'lucide-vue-next'

interface Transaction {
  id: number
  title: string
  description?: string
  type: 'income' | 'expense'
  type_label: string
  amount: number
  formatted_amount: string
  due_date: string
  due_date_formatted: string
  payment_date?: string
  payment_date_formatted?: string
  status: string
  status_label: string
  is_overdue: boolean
  category?: {
    id: number
    name: string
    color: string
    icon?: string
  }
  client?: {
    id: number
    name: string
  }
  project?: {
    id: number
    name: string
  }
}

interface Props {
  transactions: {
    data: Transaction[]
    links: any[]
    meta: any
  }
  categories: any[]
  clients: any[]
  projects: any[]
  stats: {
    total_income: number
    total_expenses: number
    pending_income: number
    pending_expenses: number
    overdue_count: number
  }
  filters: any
  typeOptions: Record<string, string>
  statusOptions: Record<string, string>
}

const props = defineProps<Props>()

// Reactive data
const showFilters = ref(false)
const selectedTransactions = ref<number[]>([])
const showBulkActions = ref(false)
const searchQuery = ref(props.filters.search || '')
const activeFilters = ref({
  type: props.filters.type || '',
  status: props.filters.status || '',
  category: props.filters.category || '',
  client: props.filters.client || '',
  project: props.filters.project || '',
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || '',
})

// Computed
const isAllSelected = computed(() => {
  return props.transactions.data.length > 0 && 
         selectedTransactions.value.length === props.transactions.data.length
})

const isIndeterminate = computed(() => {
  return selectedTransactions.value.length > 0 && 
         selectedTransactions.value.length < props.transactions.data.length
})

// Methods
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedTransactions.value = []
  } else {
    selectedTransactions.value = props.transactions.data.map(t => t.id)
  }
  showBulkActions.value = selectedTransactions.value.length > 0
}

const toggleTransaction = (id: number) => {
  const index = selectedTransactions.value.indexOf(id)
  if (index > -1) {
    selectedTransactions.value.splice(index, 1)
  } else {
    selectedTransactions.value.push(id)
  }
  showBulkActions.value = selectedTransactions.value.length > 0
}

const applyFilters = () => {
  const params: Record<string, any> = {
    search: searchQuery.value,
    ...activeFilters.value
  }
  
  // Remove empty values
  Object.keys(params).forEach(key => {
    if (!params[key]) {
      delete params[key]
    }
  })

  router.get(route('financial.transactions.index'), params, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  searchQuery.value = ''
  activeFilters.value = {
    type: '',
    status: '',
    category: '',
    client: '',
    project: '',
    date_from: '',
    date_to: '',
  }
  applyFilters()
}

const markAsPaid = (transaction: Transaction) => {
  router.patch(route('financial.transactions.mark-as-paid', transaction.id), {
    payment_date: new Date().toISOString().split('T')[0]
  }, {
    preserveState: true,
  })
}

const markAsPending = (transaction: Transaction) => {
  router.patch(route('financial.transactions.mark-as-pending', transaction.id), {}, {
    preserveState: true,
  })
}

const cancelTransaction = (transaction: Transaction) => {
  if (confirm('Tem certeza que deseja cancelar esta transação?')) {
    router.patch(route('financial.transactions.cancel', transaction.id), {}, {
      preserveState: true,
    })
  }
}

const deleteTransaction = (transaction: Transaction) => {
  if (confirm('Tem certeza que deseja excluir esta transação?')) {
    router.delete(route('financial.transactions.destroy', transaction.id), {
      preserveState: true,
    })
  }
}

const bulkMarkAsPaid = () => {
  router.patch(route('financial.transactions.bulk-update-status'), {
    ids: selectedTransactions.value,
    status: 'paid',
    payment_date: new Date().toISOString().split('T')[0]
  }, {
    preserveState: true,
    onSuccess: () => {
      selectedTransactions.value = []
      showBulkActions.value = false
    }
  })
}

const bulkDelete = () => {
  if (confirm(`Tem certeza que deseja excluir ${selectedTransactions.value.length} transações?`)) {
    router.delete(route('financial.transactions.bulk-destroy'), {
      data: { ids: selectedTransactions.value },
      preserveState: true,
      onSuccess: () => {
        selectedTransactions.value = []
        showBulkActions.value = false
      }
    })
  }
}

const getStatusColor = (status: string) => {
  switch (status) {
    case 'paid': return 'text-green-600 bg-green-100 dark:bg-green-900/20'
    case 'pending': return 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/20'
    case 'overdue': return 'text-red-600 bg-red-100 dark:bg-red-900/20'
    case 'cancelled': return 'text-gray-600 bg-gray-100 dark:bg-gray-900/20'
    default: return 'text-gray-600 bg-gray-100 dark:bg-gray-900/20'
  }
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR')
}
</script>

<template>
  <Head title="Transações Financeiras" />

  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Transações Financeiras
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
              Gerencie todas as suas receitas e despesas
            </p>
          </div>
          
          <div class="flex items-center gap-3">
            <button
              @click="showFilters = !showFilters"
              class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              <Filter class="h-4 w-4" />
              Filtros
            </button>

            <button
              @click="router.visit(route('financial.transactions.create'))"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Plus class="h-4 w-4" />
              Nova Transação
            </button>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
          <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
                <DollarSign class="h-5 w-5 text-green-600" />
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Receitas</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                  {{ formatCurrency(stats.total_income) }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
                <DollarSign class="h-5 w-5 text-red-600" />
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Despesas</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                  {{ formatCurrency(stats.total_expenses) }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-yellow-100 dark:bg-yellow-900/20 rounded-lg">
                <Clock class="h-5 w-5 text-yellow-600" />
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">A Receber</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                  {{ formatCurrency(stats.pending_income) }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
                <Clock class="h-5 w-5 text-purple-600" />
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">A Pagar</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                  {{ formatCurrency(stats.pending_expenses) }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center gap-3">
              <div class="p-2 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                <AlertTriangle class="h-5 w-5 text-orange-600" />
              </div>
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Em Atraso</p>
                <p class="font-semibold text-gray-900 dark:text-white">
                  {{ stats.overdue_count }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div v-if="showFilters" class="bg-white dark:bg-gray-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
            <!-- Search -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Buscar
              </label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Título, descrição..."
                  class="w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>

            <!-- Type -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Tipo
              </label>
              <select
                v-model="activeFilters.type"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Todos os tipos</option>
                <option
                  v-for="(label, value) in typeOptions"
                  :key="value"
                  :value="value"
                >
                  {{ label }}
                </option>
              </select>
            </div>

            <!-- Status -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Status
              </label>
              <select
                v-model="activeFilters.status"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Todos os status</option>
                <option
                  v-for="(label, value) in statusOptions"
                  :key="value"
                  :value="value"
                >
                  {{ label }}
                </option>
                <option value="overdue">Vencido</option>
              </select>
            </div>

            <!-- Category -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Categoria
              </label>
              <select
                v-model="activeFilters.category"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Todas as categorias</option>
                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>

            <!-- Date From -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Data inicial
              </label>
              <input
                v-model="activeFilters.date_from"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <!-- Date To -->
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Data final
              </label>
              <input
                v-model="activeFilters.date_to"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <div class="flex items-center gap-3">
            <button
              @click="applyFilters"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              Aplicar Filtros
            </button>
            <button
              @click="clearFilters"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
            >
              Limpar
            </button>
          </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="showBulkActions" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
          <div class="flex items-center justify-between">
            <p class="text-blue-800 dark:text-blue-200">
              {{ selectedTransactions.length }} transações selecionadas
            </p>
            <div class="flex items-center gap-3">
              <button
                @click="bulkMarkAsPaid"
                class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
              >
                Marcar como Pago
              </button>
              <button
                @click="bulkDelete"
                class="px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors"
              >
                Excluir
              </button>
              <button
                @click="selectedTransactions = []; showBulkActions = false"
                class="text-gray-500 hover:text-gray-700"
              >
                <X class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                  <th class="px-6 py-3 text-left">
                    <input
                      type="checkbox"
                      :checked="isAllSelected"
                      :indeterminate="isIndeterminate"
                      @change="toggleSelectAll"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Transação
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Categoria
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Valor
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Vencimento
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Ações
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="transaction in transactions.data"
                  :key="transaction.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
                  :class="{ 'bg-red-50 dark:bg-red-900/10': transaction.is_overdue }"
                >
                  <td class="px-6 py-4">
                    <input
                      type="checkbox"
                      :checked="selectedTransactions.includes(transaction.id)"
                      @change="toggleTransaction(transaction.id)"
                      class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                    />
                  </td>
                  <td class="px-6 py-4">
                    <div>
                      <p class="font-medium text-gray-900 dark:text-white">
                        {{ transaction.title }}
                      </p>
                      <p v-if="transaction.description" class="text-sm text-gray-500 dark:text-gray-400">
                        {{ transaction.description }}
                      </p>
                      <p v-if="transaction.client" class="text-xs text-gray-500 dark:text-gray-400">
                        Cliente: {{ transaction.client.name }}
                      </p>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div v-if="transaction.category" class="flex items-center gap-2">
                      <div 
                        class="w-3 h-3 rounded-full"
                        :style="{ backgroundColor: transaction.category.color }"
                      ></div>
                      <span class="text-sm text-gray-900 dark:text-white">
                        {{ transaction.category.name }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-1">
                      <span 
                        class="font-semibold"
                        :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'"
                      >
                        {{ transaction.type === 'income' ? '+' : '-' }}{{ transaction.formatted_amount }}
                      </span>
                      <span class="text-xs text-gray-500 dark:text-gray-400">
                        {{ transaction.type_label }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div>
                      <p class="text-sm text-gray-900 dark:text-white">
                        {{ transaction.due_date_formatted }}
                      </p>
                      <p v-if="transaction.payment_date_formatted" class="text-xs text-gray-500 dark:text-gray-400">
                        Pago em {{ transaction.payment_date_formatted }}
                      </p>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <span 
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getStatusColor(transaction.status)"
                    >
                      {{ transaction.status_label }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <div class="flex items-center justify-end gap-2">
                      <button
                        v-if="transaction.status === 'pending'"
                        @click="markAsPaid(transaction)"
                        class="p-1.5 text-green-600 hover:bg-green-100 dark:hover:bg-green-900/20 rounded-lg transition-colors"
                        title="Marcar como pago"
                      >
                        <Check class="h-4 w-4" />
                      </button>
                      
                      <button
                        v-if="transaction.status === 'paid'"
                        @click="markAsPending(transaction)"
                        class="p-1.5 text-yellow-600 hover:bg-yellow-100 dark:hover:bg-yellow-900/20 rounded-lg transition-colors"
                        title="Marcar como pendente"
                      >
                        <Clock class="h-4 w-4" />
                      </button>

                      <button
                        @click="router.visit(route('financial.transactions.show', transaction.id))"
                        class="p-1.5 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                        title="Visualizar"
                      >
                        <Eye class="h-4 w-4" />
                      </button>

                      <button
                        @click="router.visit(route('financial.transactions.edit', transaction.id))"
                        class="p-1.5 text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
                        title="Editar"
                      >
                        <Edit class="h-4 w-4" />
                      </button>

                      <button
                        @click="deleteTransaction(transaction)"
                        class="p-1.5 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                        title="Excluir"
                      >
                        <Trash2 class="h-4 w-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div v-if="transactions.data.length === 0" class="p-12 text-center">
            <DollarSign class="h-12 w-12 text-gray-400 mx-auto mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
              Nenhuma transação encontrada
            </h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">
              Comece criando sua primeira transação financeira.
            </p>
            <button
              @click="router.visit(route('financial.transactions.create'))"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Plus class="h-4 w-4" />
              Nova Transação
            </button>
          </div>

          <!-- Pagination -->
          <div v-if="transactions.data.length > 0" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700 dark:text-gray-300">
                Mostrando {{ transactions.meta.from }} a {{ transactions.meta.to }} 
                de {{ transactions.meta.total }} resultados
              </div>
              
              <div class="flex items-center gap-2">
                <template v-for="link in transactions.links" :key="link.label">
                  <button
                    v-if="link.url"
                    @click="router.get(link.url)"
                    class="px-3 py-1.5 text-sm border rounded-lg transition-colors"
                    :class="link.active 
                      ? 'bg-blue-600 text-white border-blue-600' 
                      : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
                    v-html="link.label"
                  ></button>
                  <span
                    v-else
                    class="px-3 py-1.5 text-sm text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg"
                    v-html="link.label"
                  ></span>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
