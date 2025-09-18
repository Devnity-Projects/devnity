<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { 
  Home, 
  Users, 
  FolderKanban, 
  Briefcase, 
  LifeBuoy, 
  Sun, 
  Moon, 
  LogOut, 
  User,
  Settings,
  Bell,
  Search,
  Menu,
  X,
  Code2,
  Zap,
  Shield,
  Rocket
} from 'lucide-vue-next'

import FlashToasts from '@/components/ui/toast/FlashToasts.vue'
import GlobalSearch from '@/components/GlobalSearch.vue'
import { useUserSettings } from '@/composables/useUserSettings'
import { router, usePage as useInertiaPage } from '@inertiajs/vue3'

const { user, isDarkTheme, applyTheme, toggleTheme, setupSystemThemeListener } = useUserSettings()
const inertiaPage = useInertiaPage()
const isImpersonated = computed(() => Boolean((inertiaPage.props as any)?.auth?.impersonated))
const impersonator = computed(() => (inertiaPage.props as any)?.auth?.impersonator)
const stopImpersonation = () => {
  router.post(route('settings.impersonate.stop'), {}, { preserveScroll: true })
}

// Global search
const globalSearch = ref()

const openSearch = () => {
  globalSearch.value?.open()
}

// Use the theme toggle from the composable
const toggleDark = toggleTheme

// Setup theme management
let cleanupSystemListener: (() => void) | null = null

onMounted(() => {
  // Apply the user's theme preference
  applyTheme()
  
  // Setup system theme change listener
  cleanupSystemListener = setupSystemThemeListener()
})

onUnmounted(() => {
  // Cleanup listener
  if (cleanupSystemListener) {
    cleanupSystemListener()
  }
})

// Navigation
const nav = [
  { label: 'Dashboard', href: '/dashboard', icon: Home, description: 'Visão geral do sistema' },
  { label: 'Clientes', href: '/clients', icon: Users, description: 'Gerenciar clientes e contatos' },
  { label: 'Projetos', href: '/projects', icon: FolderKanban, description: 'Projetos de desenvolvimento' },
  { label: 'Tarefas', href: '/tasks', icon: Briefcase, description: 'Gerenciar tarefas dos projetos' },
  { label: 'Financeiro', href: '/financial', icon: Zap, description: 'Gestão financeira' },
  { label: 'Suporte', href: '/support/admin', icon: LifeBuoy, description: 'Sistema de suporte' },
]

// UI State
const showProfile = ref(false)
const showMobileMenu = ref(false)
const showNotifications = ref(false)

// Read unread notifications count from Inertia page props if available.
const page = usePage()
const unreadNotifications = computed(() => {
  const props = page.props || (page as any).value || {}
  // common server-provided prop names: unread_notifications_count, unreadNotifications
  const count = props.unread_notifications_count ?? props.unreadNotifications ?? 0
  // if notifications array present, compute unread by `read` flag
  if (!count && Array.isArray(props.notifications)) {
    return props.notifications.filter((n: any) => !n.read).length
  }
  return Number(count || 0)
})

const handleClickOutside = (e: MouseEvent) => {
  const target = e.target as HTMLElement
  
  // Close dropdowns when clicking outside
  if (!target.closest('.profile-dropdown')) {
    showProfile.value = false
  }
  if (!target.closest('.notifications-dropdown')) {
    showNotifications.value = false
  }
  if (!target.closest('.mobile-menu-toggle') && !target.closest('.mobile-menu')) {
    showMobileMenu.value = false
  }
}

onMounted(() => {
  // Apply the user's theme preference
  applyTheme()
  
  // Setup system theme change listener
  cleanupSystemListener = setupSystemThemeListener()
  
  window.addEventListener('click', handleClickOutside)
  
  // Close mobile menu when window resizes to desktop
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
      showMobileMenu.value = false
    }
  })
})
</script>

<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    <!-- Impersonation Banner -->
    <div v-if="isImpersonated" class="w-full bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200 text-sm">
      <div class="container mx-auto max-w-screen-2xl px-4 sm:px-6 py-2 flex items-center justify-between">
        <div>
          Você está visualizando como outro usuário.
          <span v-if="impersonator" class="ml-2 opacity-80">(Admin original: {{ impersonator.name }} — {{ impersonator.email }})</span>
        </div>
        <button @click="stopImpersonation" class="px-3 py-1 rounded-md bg-amber-200 hover:bg-amber-300 text-amber-900 dark:bg-amber-800 dark:hover:bg-amber-700 dark:text-amber-100 transition-colors">
          Voltar para admin
        </button>
      </div>
    </div>
    <FlashToasts />
    
    <!-- Header -->
    <header class="sticky top-0 z-50 w-full border-b bg-white/95 dark:bg-gray-900/95 backdrop-blur supports-[backdrop-filter]:bg-white/60 dark:supports-[backdrop-filter]:bg-gray-900/60">
      <div class="container mx-auto flex h-16 max-w-screen-2xl items-center justify-between px-4 sm:px-6">
        <!-- Logo e Brand -->
        <div class="flex items-center gap-6">
          <!-- Mobile Menu Button -->
          <button
            @click.stop="showMobileMenu = !showMobileMenu"
            class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors mobile-menu-toggle"
            aria-label="Toggle menu"
            type="button"
          >
            <Menu v-if="!showMobileMenu" class="h-5 w-5" />
            <X v-else class="h-5 w-5" />
          </button>

          <!-- Logo -->
          <Link href="/dashboard" class="flex items-center gap-3 group">
            <div class="relative">
              <div class="absolute inset-0 devnity-gradient rounded-lg blur opacity-75 group-hover:opacity-100 transition-opacity"></div>
              <div class="relative bg-white dark:bg-gray-900 p-2 rounded-lg">
                <Code2 class="h-6 w-6 text-blue-600 dark:text-blue-400" />
              </div>
            </div>
            <div class="hidden sm:block">
              <h1 class="text-xl font-bold devnity-text-gradient">Devnity</h1>
              <p class="text-xs text-gray-500 dark:text-gray-400 -mt-1">Development Solutions</p>
            </div>
          </Link>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center gap-2">
          <Link
            v-for="item in nav"
            :key="item.href"
            :href="item.href"
            class="relative flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 group"
            :class="[
              $page.url.startsWith(item.href)
                ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300'
                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-gray-100'
            ]"
          >
            <component 
              :is="item.icon" 
              class="h-4 w-4 transition-transform group-hover:scale-110" 
            />
            <span>{{ item.label }}</span>
            
            <!-- Active indicator -->
            <div 
              v-if="$page.url.startsWith(item.href)"
              class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-6 h-0.5 devnity-gradient rounded-full"
            ></div>
          </Link>
        </nav>

        <!-- Right side actions -->
        <div class="flex items-center gap-2">
          <!-- Search -->
          <button 
            @click="openSearch"
            class="hidden sm:flex items-center gap-2 px-3 py-2 text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
          >
            <Search class="h-4 w-4" />
            <span class="hidden lg:inline">Buscar...</span>
            <kbd class="hidden lg:inline-flex items-center px-1.5 py-0.5 border border-gray-200 dark:border-gray-600 rounded text-xs">⌘K</kbd>
          </button>

          <!-- Mobile Search -->
          <button 
            @click="openSearch"
            class="sm:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            aria-label="Buscar"
          >
            <Search class="h-5 w-5 text-gray-600 dark:text-gray-300" />
          </button>

          <!-- Notifications -->
          <div class="relative notifications-dropdown">
            <button
              @click.stop="showNotifications = !showNotifications"
              class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              aria-label="Notificações"
            >
              <Bell class="h-5 w-5 text-gray-600 dark:text-gray-300" />
              <div v-if="unreadNotifications > 0" class="absolute -top-1 -right-1 h-3 w-3 bg-red-500 rounded-full animate-pulse"></div>
            </button>

            <!-- Notifications Dropdown -->
            <Transition name="fade-scale">
              <div
                v-if="showNotifications"
                class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-2 z-50"
                @click.stop
              >
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
                  <h3 class="font-semibold text-gray-900 dark:text-gray-100">Notificações</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                  <div class="px-4 py-3 text-center text-gray-500 dark:text-gray-400">
                    Nenhuma notificação nova
                  </div>
                </div>
              </div>
            </Transition>
          </div>

          <!-- Theme Toggle -->
          <button
            @click="toggleDark"
            class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            aria-label="Alternar tema"
          >
            <Sun v-if="isDarkTheme" class="h-5 w-5 text-gray-600 dark:text-gray-300" />
            <Moon v-else class="h-5 w-5 text-gray-600 dark:text-gray-300" />
          </button>

          <!-- Profile Dropdown -->
          <div class="relative profile-dropdown">
            <button
              @click.stop="showProfile = !showProfile"
              class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              aria-label="Menu do perfil"
            >
              <div class="h-8 w-8 rounded-lg overflow-hidden">
                <img
                  v-if="user?.avatar_url"
                  :src="user.avatar_url"
                  :alt="user.name"
                  class="h-full w-full object-cover"
                >
                <div v-else class="h-full w-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white text-sm font-semibold">
                  {{ user?.name?.charAt(0).toUpperCase() }}
                </div>
              </div>
              <span class="hidden sm:inline text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ user?.name }}
              </span>
            </button>

            <!-- Profile Dropdown Menu -->
            <Transition name="fade-scale">
              <div
                v-if="showProfile"
                class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl py-2 z-50"
                @click.stop
              >
                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ user?.name }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ user?.email }}</p>
                </div>
                
                <Link 
                  href="/settings/profile" 
                  class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                  <User class="h-4 w-4" />
                  Perfil
                </Link>
                
                <Link 
                  href="/settings" 
                  class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                >
                  <Settings class="h-4 w-4" />
                  Configurações
                </Link>
                
                <div class="border-t border-gray-100 dark:border-gray-800 mt-2 pt-2">
                  <Link 
                    method="post" 
                    href="/logout" 
                    as="button"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors w-full text-left"
                  >
                    <LogOut class="h-4 w-4" />
                    Sair
                  </Link>
                </div>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </header>

    <!-- Mobile Navigation -->
    <div 
      v-if="showMobileMenu"
      class="md:hidden bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-4 py-4 shadow-lg"
    >
        <div class="space-y-2">
          <Link
            v-for="item in nav"
            :key="item.href"
            :href="item.href"
            @click="showMobileMenu = false"
            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors block w-full"
            :class="[
              $page.url.startsWith(item.href)
                ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300'
                : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'
            ]"
          >
            <component :is="item.icon" class="h-5 w-5" />
            <div>
              <div>{{ item.label }}</div>
              <div class="text-xs text-gray-500 dark:text-gray-400">{{ item.description }}</div>
            </div>
          </Link>
        </div>
      </div>

    <!-- Main Content -->
    <main class="container mx-auto max-w-screen-2xl px-4 sm:px-6 py-6">
      <!-- Breadcrumb e Actions podem ser adicionados aqui -->
      <div class="devnity-animate-in">
        <slot />
      </div>
    </main>

    <!-- Global Search Modal -->
    <GlobalSearch ref="globalSearch" />

    <!-- Footer -->
    <footer class="border-t bg-gray-50 dark:bg-gray-900/50 py-8 mt-auto">
      <div class="container mx-auto max-w-screen-2xl px-4 sm:px-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
              <Code2 class="h-5 w-5 text-blue-600 dark:text-blue-400" />
              <span class="font-semibold devnity-text-gradient">Devnity</span>
            </div>
            <div class="hidden md:flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
              <div class="flex items-center gap-1">
                <Zap class="h-4 w-4" />
                <span>Inovação Contínua</span>
              </div>
              <div class="flex items-center gap-1">
                <Shield class="h-4 w-4" />
                <span>Excelência Técnica</span>
              </div>
              <div class="flex items-center gap-1">
                <Rocket class="h-4 w-4" />
                <span>Foco no Resultado</span>
              </div>
            </div>
          </div>
          <div class="text-sm text-gray-500 dark:text-gray-400">
            © 2025 Devnity. Transformando desafios em soluções.
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
/* Transitions */
.fade-scale-enter-active,
.fade-scale-leave-active {
  transition: all 0.2s ease;
}

.fade-scale-enter-from,
.fade-scale-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(-5px);
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease-out;
  transform-origin: top;
}

.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-10px) scaleY(0.95);
}

.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-10px) scaleY(0.95);
}

/* Custom scrollbar for mobile menu */
.mobile-menu {
  scrollbar-width: none;
  -ms-overflow-style: none;
  max-height: calc(100vh - 4rem);
  overflow-y: auto;
}

.mobile-menu::-webkit-scrollbar {
  display: none;
}

/* Ensure mobile menu appears above other content */
.mobile-menu {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Glass morphism effect for header */
header {
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

/* Smooth focus states */
button:focus-visible,
a:focus-visible {
  outline: 2px solid rgb(59 130 246);
  outline-offset: 2px;
  border-radius: 0.5rem;
}

/* Enhanced hover states */
.group:hover .devnity-gradient {
  filter: brightness(1.1);
}

/* Animation for the active indicator */
@keyframes slideIn {
  from {
    width: 0;
    opacity: 0;
  }
  to {
    width: 1.5rem;
    opacity: 1;
  }
}

.nav-item-active .active-indicator {
  animation: slideIn 0.3s ease-out;
}
</style>
