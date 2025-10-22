<template>
  <Teleport to="body">
    <!-- Backdrop -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm"
      @click="close"
    ></div>

    <!-- Search Modal -->
    <div
      v-if="isOpen"
      class="fixed inset-0 z-50 flex items-start justify-center pt-[10vh]"
      @click.self="close"
    >
      <div class="w-full max-w-2xl mx-4 bg-white dark:bg-gray-900 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700">
        <!-- Search Input -->
        <div class="flex items-center px-4 py-3 border-b border-gray-200 dark:border-gray-700">
          <Search class="h-5 w-5 text-gray-400 mr-3" />
          <input
            ref="searchInput"
            v-model="query"
            type="text"
            placeholder="Buscar tasks, projetos, clientes..."
            class="w-full bg-transparent text-gray-900 dark:text-white placeholder-gray-500 focus:outline-none text-lg"
            @keydown.escape="close"
            @keydown.enter="navigateToSelected"
            @keydown.arrow-down.prevent="selectNext"
            @keydown.arrow-up.prevent="selectPrevious"
          />
          <kbd class="hidden sm:inline-flex items-center px-2 py-1 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded text-xs text-gray-500 dark:text-gray-400">
            ESC
          </kbd>
        </div>

        <!-- Results -->
        <div class="max-h-96 overflow-y-auto">
          <!-- Loading -->
          <div v-if="loading" class="flex items-center justify-center py-8">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
          </div>

          <!-- No Results -->
          <div v-else-if="query && !hasResults" class="text-center py-8 text-gray-500 dark:text-gray-400">
            <FileX class="h-8 w-8 mx-auto mb-2 opacity-50" />
            <p>Nenhum resultado encontrado para "{{ query }}"</p>
          </div>

          <!-- Recent Searches (when no query) -->
          <div v-else-if="!query && recentSearches.length > 0" class="p-4">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
              Buscas recentes
            </h3>
            <div class="space-y-1">
              <button
                v-for="(recent, index) in recentSearches"
                :key="index"
                @click="selectRecentSearch(recent)"
                class="w-full flex items-center px-3 py-2 text-left rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              >
                <Clock class="h-4 w-4 text-gray-400 mr-3" />
                <span class="text-gray-700 dark:text-gray-300">{{ recent }}</span>
              </button>
            </div>
          </div>

          <!-- Search Results -->
          <div v-else-if="hasResults" class="divide-y divide-gray-100 dark:divide-gray-800">
            <!-- Tasks -->
            <div v-if="results.tasks.length > 0" class="p-4">
              <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                Tasks ({{ results.tasks.length }})
              </h3>
              <div class="space-y-1">
                <button
                  v-for="(task, index) in results.tasks"
                  :key="`task-${task.id}`"
                  :class="[
                    'w-full flex items-start px-3 py-2 text-left rounded-lg transition-colors',
                    selectedIndex === getItemIndex('task', index) 
                      ? 'bg-blue-50 dark:bg-blue-900/20 border-l-2 border-blue-500' 
                      : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                  ]"
                  @click="navigateToTask(task)"
                >
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                      <CheckSquare class="h-4 w-4 text-blue-500 flex-shrink-0" />
                      <span class="font-medium text-gray-900 dark:text-white truncate">{{ task.title }}</span>
                      <span :class="getPriorityClass(task.priority)" class="text-xs px-2 py-0.5 rounded-full">
                        {{ task.priority_label }}
                      </span>
                    </div>
                    <div class="flex items-center gap-4 mt-1 text-sm text-gray-500 dark:text-gray-400">
                      <span>{{ task.project.name }}</span>
                      <span v-if="task.assigned_user">{{ task.assigned_user.name }}</span>
                      <span>{{ task.status_label }}</span>
                    </div>
                  </div>
                </button>
              </div>
            </div>

            <!-- Projects -->
            <div v-if="results.projects.length > 0" class="p-4">
              <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                Projetos ({{ results.projects.length }})
              </h3>
              <div class="space-y-1">
                <button
                  v-for="(project, index) in results.projects"
                  :key="`project-${project.id}`"
                  :class="[
                    'w-full flex items-start px-3 py-2 text-left rounded-lg transition-colors',
                    selectedIndex === getItemIndex('project', index)
                      ? 'bg-blue-50 dark:bg-blue-900/20 border-l-2 border-blue-500' 
                      : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                  ]"
                  @click="navigateToProject(project)"
                >
                  <FolderOpen class="h-4 w-4 text-green-500 mr-3 mt-0.5 flex-shrink-0" />
                  <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-900 dark:text-white truncate">{{ project.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                      <span>{{ project.client?.name || 'Projeto Pessoal' }}</span>
                      <span v-if="project.description" class="ml-2">• {{ project.description }}</span>
                    </div>
                  </div>
                </button>
              </div>
            </div>

            <!-- Clients -->
            <div v-if="results.clients.length > 0" class="p-4">
              <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                Clientes ({{ results.clients.length }})
              </h3>
              <div class="space-y-1">
                <button
                  v-for="(client, index) in results.clients"
                  :key="`client-${client.id}`"
                  :class="[
                    'w-full flex items-start px-3 py-2 text-left rounded-lg transition-colors',
                    selectedIndex === getItemIndex('client', index)
                      ? 'bg-blue-50 dark:bg-blue-900/20 border-l-2 border-blue-500' 
                      : 'hover:bg-gray-100 dark:hover:bg-gray-800'
                  ]"
                  @click="navigateToClient(client)"
                >
                  <Building class="h-4 w-4 text-purple-500 mr-3 mt-0.5 flex-shrink-0" />
                  <div class="flex-1 min-w-0">
                    <div class="font-medium text-gray-900 dark:text-white truncate">{{ client.name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                      <span v-if="client.email">{{ client.email }}</span>
                      <span v-if="client.phone" class="ml-2">• {{ client.phone }}</span>
                    </div>
                  </div>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between px-4 py-2 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded">↵</kbd>
              <span>abrir</span>
            </div>
            <div class="flex items-center gap-1">
              <kbd class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded">↑↓</kbd>
              <span>navegar</span>
            </div>
          </div>
          <div class="flex items-center gap-1">
            <span>powered by</span>
            <span class="font-medium text-blue-600 dark:text-blue-400">Devnity</span>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  Search, 
  CheckSquare, 
  FolderOpen, 
  Building, 
  Clock, 
  FileX 
} from 'lucide-vue-next'

interface User {
  id: number
  name: string
}

interface Client {
  id: number
  name: string
  email?: string
  phone?: string
}

interface Project {
  id: number
  name: string
  description?: string
  client: Client
}

interface Task {
  id: number
  title: string
  priority: string
  priority_label: string
  status: string
  status_label: string
  project: Project
  assigned_user?: User
}

interface SearchResults {
  tasks: Task[]
  projects: Project[]
  clients: Client[]
}

const isOpen = ref(false)
const query = ref('')
const loading = ref(false)
const searchInput = ref<HTMLInputElement>()
const selectedIndex = ref(0)
const recentSearches = ref<string[]>([])

const results = ref<SearchResults>({
  tasks: [],
  projects: [],
  clients: []
})

const hasResults = computed(() => {
  return results.value.tasks.length > 0 || 
         results.value.projects.length > 0 || 
         results.value.clients.length > 0
})

const totalResults = computed(() => {
  return results.value.tasks.length + 
         results.value.projects.length + 
         results.value.clients.length
})

// Load recent searches from localStorage
onMounted(() => {
  const saved = localStorage.getItem('devnity_recent_searches')
  if (saved) {
    try {
      recentSearches.value = JSON.parse(saved)
    } catch (e) {
      // Ignore invalid JSON
    }
  }
})

// Save recent searches to localStorage
const saveRecentSearches = () => {
  localStorage.setItem('devnity_recent_searches', JSON.stringify(recentSearches.value))
}

// Add to recent searches
const addToRecentSearches = (searchQuery: string) => {
  if (searchQuery.trim().length < 2) return
  
  // Remove if already exists
  const index = recentSearches.value.indexOf(searchQuery)
  if (index > -1) {
    recentSearches.value.splice(index, 1)
  }
  
  // Add to beginning
  recentSearches.value.unshift(searchQuery)
  
  // Keep only last 5
  if (recentSearches.value.length > 5) {
    recentSearches.value = recentSearches.value.slice(0, 5)
  }
  
  saveRecentSearches()
}

// Get index for keyboard navigation
const getItemIndex = (type: 'task' | 'project' | 'client', index: number): number => {
  let currentIndex = 0
  
  if (type === 'task') {
    return currentIndex + index
  }
  
  currentIndex += results.value.tasks.length
  if (type === 'project') {
    return currentIndex + index
  }
  
  currentIndex += results.value.projects.length
  if (type === 'client') {
    return currentIndex + index
  }
  
  return 0
}

// Navigation functions
const selectNext = () => {
  if (selectedIndex.value < totalResults.value - 1) {
    selectedIndex.value++
  }
}

const selectPrevious = () => {
  if (selectedIndex.value > 0) {
    selectedIndex.value--
  }
}

const navigateToSelected = () => {
  let currentIndex = 0
  
  // Check tasks
  if (selectedIndex.value < results.value.tasks.length) {
    const task = results.value.tasks[selectedIndex.value]
    navigateToTask(task)
    return
  }
  currentIndex += results.value.tasks.length
  
  // Check projects
  if (selectedIndex.value < currentIndex + results.value.projects.length) {
    const project = results.value.projects[selectedIndex.value - currentIndex]
    navigateToProject(project)
    return
  }
  currentIndex += results.value.projects.length
  
  // Check clients
  if (selectedIndex.value < currentIndex + results.value.clients.length) {
    const client = results.value.clients[selectedIndex.value - currentIndex]
    navigateToClient(client)
    return
  }
}

const navigateToTask = (task: Task) => {
  addToRecentSearches(query.value)
  close()
  router.visit(`/tasks/${task.id}`)
}

const navigateToProject = (project: Project) => {
  addToRecentSearches(query.value)
  close()
  router.visit(`/projects/${project.id}`)
}

const navigateToClient = (client: Client) => {
  addToRecentSearches(query.value)
  close()
  router.visit(`/clients/${client.id}`)
}

const selectRecentSearch = (searchQuery: string) => {
  query.value = searchQuery
  nextTick(() => {
    searchInput.value?.focus()
  })
}

const getPriorityClass = (priority: string) => {
  const classes: Record<string, string> = {
    low: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
    medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
    high: 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-400',
    urgent: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
  }
  return classes[priority] || classes.medium
}

// Search function with debounce
let searchTimeout: ReturnType<typeof setTimeout>
const performSearch = async () => {
  if (!query.value.trim() || query.value.length < 2) {
    results.value = { tasks: [], projects: [], clients: [] }
    selectedIndex.value = 0
    return
  }

  loading.value = true
  
  try {
    const response = await fetch(`/api/search?q=${encodeURIComponent(query.value)}`, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      }
    })
    
    if (response.ok) {
      const data = await response.json()
      results.value = data
      selectedIndex.value = 0
    }
  } catch (error) {
    console.error('Search error:', error)
  } finally {
    loading.value = false
  }
}

// Watch query changes
watch(query, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(performSearch, 300)
})

// Open/close functions
const open = () => {
  isOpen.value = true
  nextTick(() => {
    searchInput.value?.focus()
  })
}

const close = () => {
  isOpen.value = false
  query.value = ''
  results.value = { tasks: [], projects: [], clients: [] }
  selectedIndex.value = 0
}

// Keyboard shortcuts
const handleKeydown = (e: KeyboardEvent) => {
  if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
    e.preventDefault()
    if (isOpen.value) {
      close()
    } else {
      open()
    }
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})

// Expose functions to parent
defineExpose({
  open,
  close,
  isOpen
})
</script>
