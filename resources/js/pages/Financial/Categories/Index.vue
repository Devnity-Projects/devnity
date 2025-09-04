<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { 
  Plus, 
  Search, 
  Filter, 
  MoreHorizontal,
  Edit,
  Trash2,
  Eye,
  ToggleLeft,
  Tag,
  TrendingUp,
  TrendingDown,
  X
} from 'lucide-vue-next'

interface PaginationLink {
  url?: string | null
  label?: string | null
  active?: boolean
}

interface PaginationMeta {
  current_page: number
  from: number
  last_page: number
  per_page: number
  to: number
  total: number
}

interface PaginatedData<T> {
  data: T[]
  links: PaginationLink[]
  meta: PaginationMeta
}

interface Category {
  id: number
  name: string
  description?: string
  type: 'income' | 'expense'
  type_label: string
  color: string
  icon?: string
  is_active: boolean
  status_label: string
  transactions_count?: number
  total_amount?: number
  created_at_formatted: string
}

interface Props {
  categories: PaginatedData<Category>
  filters: any
  typeOptions: Record<string, string>
}

const props = defineProps<Props>()

// Reactive data
const showFilters = ref(false)
const selectedCategories = ref<number[]>([])
const showBulkActions = ref(false)
const searchQuery = ref(props.filters.search || '')
const activeFilters = ref({
  type: props.filters.type || '',
  status: props.filters.status || '',
})

// Computed
const isAllSelected = computed(() => {
  return props.categories.data.length > 0 && 
         selectedCategories.value.length === props.categories.data.length
})

const isIndeterminate = computed(() => {
  return selectedCategories.value.length > 0 && 
         selectedCategories.value.length < props.categories.data.length
})

// Methods
const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedCategories.value = []
  } else {
    selectedCategories.value = props.categories.data.map(c => c.id)
  }
  showBulkActions.value = selectedCategories.value.length > 0
}

const toggleCategory = (id: number) => {
  const index = selectedCategories.value.indexOf(id)
  if (index > -1) {
    selectedCategories.value.splice(index, 1)
  } else {
    selectedCategories.value.push(id)
  }
  showBulkActions.value = selectedCategories.value.length > 0
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

  router.get(route('financial.categories.index'), params, {
    preserveState: true,
    preserveScroll: true,
  })
}

const clearFilters = () => {
  searchQuery.value = ''
  activeFilters.value = {
    type: '',
    status: '',
  }
  applyFilters()
}

const toggleStatus = (category: Category) => {
  router.post(route('financial.categories.toggle-status', category.id), {}, {
    preserveState: true,
  })
}

const deleteCategory = (category: Category) => {
  if (confirm('Tem certeza que deseja excluir esta categoria?')) {
    router.delete(route('financial.categories.destroy', category.id), {
      preserveState: true,
    })
  }
}

const bulkActivate = () => {
  router.patch(route('financial.categories.bulk-toggle-status'), {
    ids: selectedCategories.value,
    status: true
  }, {
    preserveState: true,
    onSuccess: () => {
      selectedCategories.value = []
      showBulkActions.value = false
    }
  })
}

const bulkDeactivate = () => {
  router.patch(route('financial.categories.bulk-toggle-status'), {
    ids: selectedCategories.value,
    status: false
  }, {
    preserveState: true,
    onSuccess: () => {
      selectedCategories.value = []
      showBulkActions.value = false
    }
  })
}

const bulkDelete = () => {
  if (confirm(`Tem certeza que deseja excluir ${selectedCategories.value.length} categorias?`)) {
    router.delete(route('financial.categories.bulk-destroy'), {
      data: { ids: selectedCategories.value },
      preserveState: true,
      onSuccess: () => {
        selectedCategories.value = []
        showBulkActions.value = false
      }
    })
  }
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL'
  }).format(value)
}
</script>

<template>
  <Head title="Categorias Financeiras" />

  <AppLayout>
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
              Categorias Financeiras
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
              Organize suas receitas e despesas por categorias
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
              @click="router.visit(route('financial.categories.create'))"
              class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              <Plus class="h-4 w-4" />
              Nova Categoria
            </button>
          </div>
        </div>

        <!-- Filters -->
        <div v-if="showFilters" class="bg-white dark:bg-gray-800 rounded-lg p-6 mb-6 border border-gray-200 dark:border-gray-700">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
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
                  placeholder="Nome, descrição..."
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
                <option value="active">Ativo</option>
                <option value="inactive">Inativo</option>
              </select>
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
              {{ selectedCategories.length }} categorias selecionadas
            </p>
            <div class="flex items-center gap-3">
              <button
                @click="bulkActivate"
                class="px-3 py-1.5 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors"
              >
                Ativar
              </button>
              <button
                @click="bulkDeactivate"
                class="px-3 py-1.5 bg-yellow-600 text-white text-sm rounded-lg hover:bg-yellow-700 transition-colors"
              >
                Desativar
              </button>
              <button
                @click="bulkDelete"
                class="px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors"
              >
                Excluir
              </button>
              <button
                @click="selectedCategories = []; showBulkActions = false"
                class="text-gray-500 hover:text-gray-700"
              >
                <X class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>

        <!-- Categories Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="category in categories.data"
            :key="category.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 hover:shadow-md transition-shadow"
          >
            <!-- Header -->
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
              <div class="flex items-start justify-between">
                <div class="flex items-center gap-3">
                  <input
                    type="checkbox"
                    :checked="selectedCategories.includes(category.id)"
                    @change="toggleCategory(category.id)"
                    class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                  />
                  
                  <div 
                    class="w-4 h-4 rounded-full"
                    :style="{ backgroundColor: category.color }"
                  ></div>
                  
                  <div class="flex-1">
                    <h3 class="font-semibold text-gray-900 dark:text-white">
                      {{ category.name }}
                    </h3>
                    <p v-if="category.description" class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                      {{ category.description }}
                    </p>
                  </div>
                </div>

                <!-- Type Badge -->
                <div class="flex items-center gap-2">
                  <span 
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                    :class="category.type === 'income' 
                      ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300' 
                      : 'bg-red-100 dark:bg-red-900/20 text-red-700 dark:text-red-300'"
                  >
                    <TrendingUp v-if="category.type === 'income'" class="h-3 w-3" />
                    <TrendingDown v-else class="h-3 w-3" />
                    {{ category.type_label }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6">
              <!-- Stats -->
              <div class="space-y-3 mb-4">
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-500 dark:text-gray-400">Transações:</span>
                  <span class="font-medium text-gray-900 dark:text-white">
                    {{ category.transactions_count || 0 }}
                  </span>
                </div>
                
                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-500 dark:text-gray-400">Total:</span>
                  <span 
                    class="font-semibold"
                    :class="category.type === 'income' ? 'text-green-600' : 'text-red-600'"
                  >
                    {{ category.total_amount ? formatCurrency(category.total_amount) : 'R$ 0,00' }}
                  </span>
                </div>

                <div class="flex items-center justify-between">
                  <span class="text-sm text-gray-500 dark:text-gray-400">Status:</span>
                  <span 
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                    :class="category.is_active 
                      ? 'bg-green-100 dark:bg-green-900/20 text-green-700 dark:text-green-300' 
                      : 'bg-gray-100 dark:bg-gray-900/20 text-gray-700 dark:text-gray-300'"
                  >
                    {{ category.status_label }}
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                <span class="text-xs text-gray-500 dark:text-gray-400">
                  Criado em {{ category.created_at_formatted }}
                </span>
                
                <div class="flex items-center gap-2">
                  <button
                    @click="toggleStatus(category)"
                    class="p-1.5 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 transition-colors"
                    :title="category.is_active ? 'Desativar' : 'Ativar'"
                  >
                    <ToggleLeft 
                      class="h-4 w-4"
                      :class="category.is_active ? 'text-green-600' : 'text-gray-400'"
                    />
                  </button>

                  <button
                    @click="router.visit(route('financial.categories.show', category.id))"
                    class="p-1.5 text-blue-600 hover:bg-blue-100 dark:hover:bg-blue-900/20 rounded-lg transition-colors"
                    title="Visualizar"
                  >
                    <Eye class="h-4 w-4" />
                  </button>

                  <button
                    @click="router.visit(route('financial.categories.edit', category.id))"
                    class="p-1.5 text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/20 rounded-lg transition-colors"
                    title="Editar"
                  >
                    <Edit class="h-4 w-4" />
                  </button>

                  <button
                    @click="deleteCategory(category)"
                    class="p-1.5 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/20 rounded-lg transition-colors"
                    title="Excluir"
                  >
                    <Trash2 class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-if="categories.data.length === 0" class="text-center py-12">
          <Tag class="h-12 w-12 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
            Nenhuma categoria encontrada
          </h3>
          <p class="text-gray-500 dark:text-gray-400 mb-6">
            Comece criando sua primeira categoria financeira.
          </p>
          <button
            @click="router.visit(route('financial.categories.create'))"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            <Plus class="h-4 w-4" />
            Nova Categoria
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="categories.data.length > 0" class="mt-8">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700 dark:text-gray-300">
              Mostrando {{ categories.meta.from }} a {{ categories.meta.to }} 
              de {{ categories.meta.total }} resultados
            </div>
            
            <div class="flex items-center gap-2">
              <template v-for="link in categories.links" :key="link?.label || Math.random()">
                <button
                  v-if="link?.url && link?.label"
                  @click="router.get(link.url)"
                  class="px-3 py-1.5 text-sm border rounded-lg transition-colors"
                  :class="link.active 
                    ? 'bg-blue-600 text-white border-blue-600' 
                    : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'"
                  v-html="link.label"
                ></button>
                <span
                  v-else-if="link?.label"
                  class="px-3 py-1.5 text-sm text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg"
                  v-html="link.label"
                ></span>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
