<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Home, Users, FolderKanban, ListTodo, LifeBuoy, Sun, Moon, LogOut, User } from 'lucide-vue-next'

const user = usePage().props.auth?.user

// Dark mode
const isDark = ref(
  localStorage.theme === 'dark' ||
  (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
)
const toggleDark = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.theme = isDark.value ? 'dark' : 'light'
}
watch(isDark, val => {
  document.documentElement.classList.toggle('dark', val)
  localStorage.theme = val ? 'dark' : 'light'
})
onMounted(() => {
  document.documentElement.classList.toggle('dark', isDark.value)
})

const nav = [
  { label: 'Dashboard', href: '/dashboard', icon: Home },
  { label: 'Clientes', href: '/clients', icon: Users },
  { label: 'Projetos', href: '/projects', icon: FolderKanban },
  { label: 'Tarefas', href: '/tasks', icon: ListTodo },
  { label: 'Chamados', href: '/tickets', icon: LifeBuoy },
]

const showProfile = ref(false)
const handleClickOutside = (e: MouseEvent) => {
  if (!(e.target as HTMLElement).closest('.profile-dropdown')) showProfile.value = false
}
onMounted(() => {
  window.addEventListener('click', handleClickOutside)
})
</script>

<template>
  <div class="min-h-screen flex flex-col bg-gray-100 dark:bg-[#191929] transition-colors duration-500">
    <!-- CAMADA 1: Header superior -->
    <header class="w-full bg-white dark:bg-[#232336] shadow z-20 transition-colors duration-500">
      <div class="max-w-screen-2xl mx-auto flex items-center justify-between px-6 h-16">
        <Link href="/dashboard" class="flex items-center gap-3 group transition-all duration-300">
          <img src="/images/logo-devnity.png" alt="Devnity" class="h-9 w-auto drop-shadow transition-transform duration-300 group-hover:scale-105" />
          <span class="font-bold text-xl text-[#6a0dad] dark:text-[#b59cff] tracking-tight group-hover:text-[#e55e55] transition-colors duration-300">Devnity</span>
        </Link>
        <div class="flex items-center gap-3">
          <button @click="toggleDark"
            class="p-2 rounded-full bg-gray-100 dark:bg-[#292940] hover:bg-gray-200 dark:hover:bg-[#373750] transition-colors duration-300 flex items-center justify-center"
            aria-label="Alternar tema"
          >
            <Sun v-if="isDark" class="w-5 h-5 text-[#e55e55] transition-transform duration-300" />
            <Moon v-else class="w-5 h-5 text-[#6a0dad] transition-transform duration-300" />
          </button>
          <!-- Perfil -->
          <div class="relative profile-dropdown">
            <button
              @click.stop="showProfile = !showProfile"
              class="flex items-center gap-2 px-3 py-2 rounded-xl bg-gray-50 dark:bg-[#1b1b29] hover:bg-gray-200 dark:hover:bg-[#292940] transition-colors duration-300 font-medium"
              aria-label="Abrir menu do perfil"
            >
              <User class="w-5 h-5 text-[#6a0dad] dark:text-[#b59cff]" />
              <span class="hidden sm:inline text-[#6a0dad] dark:text-[#b59cff]">{{ user?.name }}</span>
            </button>
            <Transition name="fade">
              <div
                v-if="showProfile"
                class="absolute right-0 mt-2 w-44 bg-white dark:bg-[#232336] border border-gray-100 dark:border-[#2a2a39] shadow-xl rounded-xl py-2 z-40 transition-all duration-200"
                @click.stop
              >
                <Link href="/profile" class="flex items-center gap-2 px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-[#292940] transition-colors duration-200">
                  <User class="w-4 h-4" /> Perfil
                </Link>
                <Link method="post" href="/logout" as="button"
                  class="flex items-center gap-2 px-4 py-2 text-[#e55e55] hover:bg-[#ffeaea] dark:hover:bg-[#442429] transition-colors duration-200 w-full text-left">
                  <LogOut class="w-4 h-4" /> Sair
                </Link>
              </div>
            </Transition>
          </div>
        </div>
      </div>
    </header>

    <!-- CAMADA 2: Navegação principal -->
    <nav class="w-full bg-[#f3eaff] dark:bg-[#30204a] shadow z-10 border-b border-[#ece7ff] dark:border-[#3d2766] transition-colors duration-500">
      <div class="max-w-screen-2xl mx-auto flex items-center gap-1 px-6 h-14 overflow-x-auto">
        <Link
          v-for="item in nav"
          :key="item.href"
          :href="item.href"
          class="flex items-center gap-2 px-4 py-2 rounded-xl font-medium transition-all duration-200 group
            text-[#6a0dad] dark:text-[#b59cff] hover:bg-white dark:hover:bg-[#232336]/80"
          :class="{ 
            'bg-white dark:bg-[#232336] shadow text-[#6a0dad] dark:text-[#b59cff] font-bold scale-105': $page.url.startsWith(item.href) 
          }"
        >
          <component :is="item.icon" class="w-5 h-5 group-hover:scale-125 transition-transform duration-200" />
          <span class="hidden sm:inline">{{ item.label }}</span>
        </Link>
      </div>
    </nav>

    <!-- Conteúdo principal -->
    <main class="flex-1 max-w-screen-2xl w-full mx-auto p-6 transition-all duration-500">
      <slot />
    </main>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
