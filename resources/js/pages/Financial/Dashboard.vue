<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { useAbility } from '@/composables/useAbility'
import { 
  TrendingUp, 
  TrendingDown, 
  DollarSign, 
  Calendar, 
  AlertTriangle,
  Plus,
  Download,
  Eye,
  Filter,
  ArrowUpRight,
  ArrowDownRight
} from 'lucide-vue-next'

interface Props {
  stats: {
    total_income: number
    total_expenses: number
    net_income: number
    pending_income: number
    pending_expenses: number
    overdue_amount: number
    income_growth: number
    expenses_growth: number
    overdue_count: number
    formatted: {
      total_income: string
      total_expenses: string
      net_income: string
      pending_income: string
      pending_expenses: string
      overdue_amount: string
    }
  }
  recentTransactions: any[]
  overdueTransactions: any[]
  upcomingTransactions: any[]
  chartData: any[]
  categoriesPerformance: any[]
  budgetAlerts: any[]
  period: string
  filters: any
  periodOptions: Record<string, string>
}

const props = defineProps<Props>()

// Reactive data
const selectedPeriod = ref(props.period)

// Computed
const isPositiveBalance = computed(() => props.stats.net_income >= 0)
const { can } = useAbility()
const canManageFinance = computed(() => can('financial.manage'))

// Methods
const changePeriod = (period: string) => {
  selectedPeriod.value = period
  router.get(route('financial.dashboard'), { period }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const exportData = () => {
  window.open(route('financial.export', { period: selectedPeriod.value }))
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
  <Head title="Dashboard Financeiro" />

  <AppLayout>
    <div class="py-6">
      <!-- Header -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Dashboard Financeiro
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
              Visão geral das finanças da empresa
            </p>
          </div>
          
          <div class="flex items-center gap-3">
            <!-- Period Filter -->
            <select
              v-model="selectedPeriod"
              @change="changePeriod(selectedPeriod)"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500"
            >
              <option
                v-for="(label, value) in periodOptions"
                :key="value"
                :value="value"
              >
                {{ label }}
              </option>
            </select>

            <button
              v-if="canManageFinance"
              @click="exportData"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Download class="h-4 w-4" />
              Exportar
            </button>

            <button
              v-if="canManageFinance"
              @click="router.visit(route('financial.transactions.create'))"
              class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
            >
              <Plus class="h-4 w-4" />
              Nova Transação
            </button>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <!-- Total Income -->
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Receitas</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ stats.formatted.total_income }}
                </p>
              </div>
              <div class="p-3 bg-green-100 dark:bg-green-900/20 rounded-lg">
                <TrendingUp class="h-6 w-6 text-green-600" />
              </div>
            </div>
            <div class="flex items-center mt-4 gap-1">
              <ArrowUpRight 
                v-if="stats.income_growth >= 0" 
                class="h-4 w-4 text-green-500" 
              />
              <ArrowDownRight 
                v-else 
                class="h-4 w-4 text-red-500" 
              />
              <span 
                :class="stats.income_growth >= 0 ? 'text-green-600' : 'text-red-600'"
                class="text-sm font-medium"
              >
                {{ Math.abs(stats.income_growth) }}%
              </span>
              <span class="text-sm text-gray-500">vs período anterior</span>
            </div>
          </div>

          <!-- Total Expenses -->
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Despesas</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">
                  {{ stats.formatted.total_expenses }}
                </p>
              </div>
              <div class="p-3 bg-red-100 dark:bg-red-900/20 rounded-lg">
                <TrendingDown class="h-6 w-6 text-red-600" />
              </div>
            </div>
            <div class="flex items-center mt-4 gap-1">
              <ArrowUpRight 
                v-if="stats.expenses_growth >= 0" 
                class="h-4 w-4 text-red-500" 
              />
              <ArrowDownRight 
                v-else 
                class="h-4 w-4 text-green-500" 
              />
              <span 
                :class="stats.expenses_growth >= 0 ? 'text-red-600' : 'text-green-600'"
                class="text-sm font-medium"
              >
                {{ Math.abs(stats.expenses_growth) }}%
              </span>
              <span class="text-sm text-gray-500">vs período anterior</span>
            </div>
          </div>

          <!-- Net Income -->
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Lucro Líquido</p>
                <p 
                  class="text-2xl font-bold"
                  :class="isPositiveBalance ? 'text-green-600' : 'text-red-600'"
                >
                  {{ stats.formatted.net_income }}
                </p>
              </div>
              <div 
                class="p-3 rounded-lg"
                :class="isPositiveBalance ? 'bg-green-100 dark:bg-green-900/20' : 'bg-red-100 dark:bg-red-900/20'"
              >
                <DollarSign 
                  class="h-6 w-6"
                  :class="isPositiveBalance ? 'text-green-600' : 'text-red-600'"
                />
              </div>
            </div>
            <div class="mt-4">
              <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                <div 
                  class="h-2 rounded-full transition-all duration-300"
                  :class="isPositiveBalance ? 'bg-green-500' : 'bg-red-500'"
                  :style="{ width: `${Math.min(100, Math.abs(stats.net_income / (stats.total_income || 1)) * 100)}%` }"
                ></div>
              </div>
            </div>
          </div>

          <!-- Overdue Amount -->
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Em Atraso</p>
                <p class="text-2xl font-bold text-orange-600">
                  {{ stats.formatted.overdue_amount }}
                </p>
              </div>
              <div class="p-3 bg-orange-100 dark:bg-orange-900/20 rounded-lg">
                <AlertTriangle class="h-6 w-6 text-orange-600" />
              </div>
            </div>
            <div class="flex items-center mt-4">
              <Calendar class="h-4 w-4 text-gray-400 mr-1" />
              <span class="text-sm text-gray-500">
                {{ stats.overdue_count }} transações
              </span>
            </div>
          </div>
        </div>

        <!-- Budget Alerts -->
        <div v-if="budgetAlerts.length > 0" class="mb-8">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Alertas de Orçamento
          </h2>
          <div class="space-y-3">
            <div
              v-for="alert in budgetAlerts"
              :key="alert.budget_id"
              class="p-4 rounded-lg border"
              :class="{
                'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800': alert.type === 'warning',
                'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800': alert.type === 'error'
              }"
            >
              <div class="flex items-start gap-3">
                <AlertTriangle 
                  class="h-5 w-5 mt-0.5"
                  :class="{
                    'text-yellow-600': alert.type === 'warning',
                    'text-red-600': alert.type === 'error'
                  }"
                />
                <div class="flex-1">
                  <h3 
                    class="font-medium"
                    :class="{
                      'text-yellow-800 dark:text-yellow-200': alert.type === 'warning',
                      'text-red-800 dark:text-red-200': alert.type === 'error'
                    }"
                  >
                    {{ alert.title }}
                  </h3>
                  <p 
                    class="text-sm mt-1"
                    :class="{
                      'text-yellow-700 dark:text-yellow-300': alert.type === 'warning',
                      'text-red-700 dark:text-red-300': alert.type === 'error'
                    }"
                  >
                    {{ alert.message }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Recent Transactions -->
          <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                  <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Transações Recentes
                  </h2>
                  <button
                    @click="router.visit(route('financial.transactions.index'))"
                    class="text-blue-600 hover:text-blue-700 text-sm font-medium"
                  >
                    Ver todas
                  </button>
                </div>
              </div>
              
              <div class="divide-y divide-gray-200 dark:divide-gray-700">
                <div
                  v-for="transaction in recentTransactions"
                  :key="transaction.id"
                  class="p-6 hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
                >
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <div 
                        class="w-2 h-2 rounded-full"
                        :style="{ backgroundColor: transaction.category?.color || '#6366f1' }"
                      ></div>
                      <div>
                        <p class="font-medium text-gray-900 dark:text-white">
                          {{ transaction.title }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                          {{ transaction.category?.name }} • {{ formatDate(transaction.due_date) }}
                        </p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p 
                        class="font-semibold"
                        :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'"
                      >
                        {{ transaction.type === 'income' ? '+' : '-' }}{{ transaction.formatted_amount }}
                      </p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ transaction.status_label }}
                      </p>
                    </div>
                  </div>
                </div>
                
                <div v-if="recentTransactions.length === 0" class="p-6 text-center">
                  <p class="text-gray-500 dark:text-gray-400">
                    Nenhuma transação encontrada
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Sidebar -->
          <div class="space-y-6">
            <!-- Overdue Transactions -->
            <div v-if="overdueTransactions.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-red-600">
                  Transações em Atraso
                </h3>
              </div>
              <div class="p-6 space-y-4">
                <div
                  v-for="transaction in overdueTransactions"
                  :key="transaction.id"
                  class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg"
                >
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white text-sm">
                      {{ transaction.title }}
                    </p>
                    <p class="text-xs text-gray-500">
                      Venceu em {{ formatDate(transaction.due_date) }}
                    </p>
                  </div>
                  <p class="font-semibold text-red-600 text-sm">
                    {{ transaction.formatted_amount }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Upcoming Transactions -->
            <div v-if="upcomingTransactions.length > 0" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Próximos Vencimentos
                </h3>
              </div>
              <div class="p-6 space-y-4">
                <div
                  v-for="transaction in upcomingTransactions"
                  :key="transaction.id"
                  class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg"
                >
                  <div>
                    <p class="font-medium text-gray-900 dark:text-white text-sm">
                      {{ transaction.title }}
                    </p>
                    <p class="text-xs text-gray-500">
                      Vence em {{ formatDate(transaction.due_date) }}
                    </p>
                  </div>
                  <p class="font-semibold text-blue-600 text-sm">
                    {{ transaction.formatted_amount }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
              <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Ações Rápidas
                </h3>
              </div>
              <div class="p-6 space-y-3">
                <button
                  @click="router.visit(route('financial.transactions.create'))"
                  class="w-full flex items-center gap-3 p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                >
                  <Plus class="h-5 w-5 text-green-600" />
                  <span class="text-gray-900 dark:text-white">Nova Transação</span>
                </button>
                
                <button
                  @click="router.visit(route('financial.categories.index'))"
                  class="w-full flex items-center gap-3 p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                >
                  <Filter class="h-5 w-5 text-blue-600" />
                  <span class="text-gray-900 dark:text-white">Gerenciar Categorias</span>
                </button>
                
                <button
                  @click="router.visit(route('financial.transactions.index'))"
                  class="w-full flex items-center gap-3 p-3 text-left hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors"
                >
                  <Eye class="h-5 w-5 text-purple-600" />
                  <span class="text-gray-900 dark:text-white">Ver Relatórios</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
