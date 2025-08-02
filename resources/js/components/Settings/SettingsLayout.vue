<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <!-- Header -->
    <div class="bg-white dark:bg-gray-800 shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Configurações</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gerencie suas informações pessoais e preferências</p>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:w-1/4">
          <nav class="space-y-1">
            <a
              :href="route('settings.index')"
              :class="[
                'group flex items-center px-3 py-2 text-sm font-medium rounded-md',
                currentPage === 'settings.index'
                  ? 'bg-indigo-50 border-r-2 border-indigo-500 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700'
              ]"
            >
              <svg :class="[
                'mr-3 h-5 w-5',
                currentPage === 'settings.index'
                  ? 'text-indigo-500 dark:text-indigo-300'
                  : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'
              ]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Geral
            </a>
            <a
              :href="route('settings.profile.edit')"
              :class="[
                'group flex items-center px-3 py-2 text-sm font-medium rounded-md',
                currentPage === 'settings.profile.edit'
                  ? 'bg-indigo-50 border-r-2 border-indigo-500 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700'
              ]"
            >
              <svg :class="[
                'mr-3 h-5 w-5',
                currentPage === 'settings.profile.edit'
                  ? 'text-indigo-500 dark:text-indigo-300'
                  : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'
              ]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Perfil
            </a>
            <a
              :href="route('settings.password.edit')"
              :class="[
                'group flex items-center px-3 py-2 text-sm font-medium rounded-md',
                currentPage === 'settings.password.edit'
                  ? 'bg-indigo-50 border-r-2 border-indigo-500 text-indigo-700 dark:bg-indigo-900 dark:text-indigo-200'
                  : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-700'
              ]"
            >
              <svg :class="[
                'mr-3 h-5 w-5',
                currentPage === 'settings.password.edit'
                  ? 'text-indigo-500 dark:text-indigo-300'
                  : 'text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300'
              ]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Senha
            </a>
          </nav>
        </div>

        <!-- Main Content -->
        <div class="lg:w-3/4">
          <slot />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const currentPage = computed(() => {
  return page.component
})
</script>
