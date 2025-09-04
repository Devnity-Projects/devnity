<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useUserSettings } from '@/composables/useUserSettings'

interface Settings {
  theme: string
  language: string
  timezone: string
  date_format: string
  time_format: string
  email_notifications: boolean
  browser_notifications: boolean
  task_reminders: boolean
  project_updates: boolean
}

interface Props {
  settings: Settings
  status?: string
}

const props = defineProps<Props>()

const { applyTheme } = useUserSettings()

const form = useForm({
  theme: props.settings?.theme || 'system',
  language: props.settings?.language || 'pt-BR',
  timezone: props.settings?.timezone || 'America/Sao_Paulo',
  date_format: props.settings?.date_format || 'd/m/Y',
  time_format: props.settings?.time_format || 'H:i',
  email_notifications: props.settings?.email_notifications || true,
  browser_notifications: props.settings?.browser_notifications || true,
  task_reminders: props.settings?.task_reminders || true,
  project_updates: props.settings?.project_updates || false
})

const updateSettings = () => {
  form.patch(route('settings.update'), {
    onSuccess: () => {
      // Apply theme immediately after successful save
      applyTheme()
    }
  })
}
</script>

<template>
  <AppLayout>
    <div class="space-y-8">
      <!-- Status Message -->
      <div v-if="props.status" class="bg-green-50 dark:bg-green-950/50 border border-green-200 dark:border-green-800 rounded-lg p-4">
        <div class="flex">
          <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
          </svg>
          <p class="ml-3 text-sm font-medium text-green-800 dark:text-green-200">{{ props.status }}</p>
        </div>
      </div>

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
            Configurações
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">
            Gerencie suas preferências e configurações da conta
          </p>
        </div>
      </div>

      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:w-1/4">
          <nav class="space-y-1">
            <a
              :href="route('settings.index')"
              class="bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 group flex items-center px-3 py-2 text-sm font-medium rounded-lg"
            >
              <svg class="text-blue-500 dark:text-blue-300 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Geral
            </a>
            <a
              :href="route('settings.profile.edit')"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
            >
              <svg class="text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Perfil
            </a>
            <a
              :href="route('settings.password.edit')"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
            >
              <svg class="text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Senha
            </a>
          </nav>
        </div>

        <!-- Main Content -->
        <div class="lg:w-3/4 space-y-6">
          <!-- Appearance Settings -->
          <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Aparência</h2>
                  <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Configure o tema e aparência da interface
                  </p>
                </div>
              </div>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    Tema
                  </label>
                  <div class="grid grid-cols-3 gap-3">
                    <label class="cursor-pointer">
                      <input 
                        v-model="form.theme" 
                        type="radio" 
                        value="light" 
                        class="sr-only peer"
                      >
                      <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-950/50 transition-colors">
                        <div class="flex items-center space-x-3">
                          <div class="bg-white border border-gray-300 rounded w-8 h-6 shadow-sm"></div>
                          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Claro</span>
                        </div>
                      </div>
                    </label>
                    
                    <label class="cursor-pointer">
                      <input 
                        v-model="form.theme" 
                        type="radio" 
                        value="dark" 
                        class="sr-only peer"
                      >
                      <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-950/50 transition-colors">
                        <div class="flex items-center space-x-3">
                          <div class="bg-gray-800 border border-gray-600 rounded w-8 h-6 shadow-sm"></div>
                          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Escuro</span>
                        </div>
                      </div>
                    </label>
                    
                    <label class="cursor-pointer">
                      <input 
                        v-model="form.theme" 
                        type="radio" 
                        value="system" 
                        class="sr-only peer"
                      >
                      <div class="border-2 border-gray-200 dark:border-gray-700 rounded-lg p-4 peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-950/50 transition-colors">
                        <div class="flex items-center space-x-3">
                          <div class="relative w-8 h-6 rounded shadow-sm overflow-hidden">
                            <div class="absolute inset-0 w-1/2 bg-white border-r border-gray-300"></div>
                            <div class="absolute inset-0 left-1/2 bg-gray-800"></div>
                          </div>
                          <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Sistema</span>
                        </div>
                      </div>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Notification Settings -->
          <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Notificações</h2>
                  <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Configure como e quando receber notificações
                  </p>
                </div>
              </div>
              
              <div class="space-y-4">
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      Notificações por email
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      Receba atualizações importantes por email
                    </p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input 
                      v-model="form.email_notifications" 
                      type="checkbox" 
                      class="sr-only peer"
                    >
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                  </label>
                </div>
                
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      Notificações do navegador
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      Receba notificações instantâneas no navegador
                    </p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input 
                      v-model="form.browser_notifications" 
                      type="checkbox" 
                      class="sr-only peer"
                    >
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                  </label>
                </div>
                
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      Lembretes de tarefas
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      Receba lembretes sobre tarefas pendentes
                    </p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input 
                      v-model="form.task_reminders" 
                      type="checkbox" 
                      class="sr-only peer"
                    >
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                  </label>
                </div>
                
                <div class="flex items-center justify-between">
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      Atualizações de projetos
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                      Receba notificações sobre mudanças em projetos
                    </p>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input 
                      v-model="form.project_updates" 
                      type="checkbox" 
                      class="sr-only peer"
                    >
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Regional Settings -->
          <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Configurações Regionais</h2>
                  <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Configure idioma e fuso horário
                  </p>
                </div>
              </div>
              
              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label for="language" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Idioma
                  </label>
                  <select
                    id="language"
                    v-model="form.language"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100 transition-colors"
                  >
                    <option value="pt-BR">Português (Brasil)</option>
                    <option value="en-US">English (US)</option>
                    <option value="es-ES">Español</option>
                  </select>
                </div>
                
                <div>
                  <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Fuso Horário
                  </label>
                  <select
                    id="timezone"
                    v-model="form.timezone"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100 transition-colors"
                  >
                    <option value="America/Sao_Paulo">São Paulo (GMT-3)</option>
                    <option value="America/New_York">New York (GMT-5)</option>
                    <option value="Europe/London">London (GMT+0)</option>
                    <option value="Asia/Tokyo">Tokyo (GMT+9)</option>
                  </select>
                </div>
              </div>
            </div>
          </div>

          <!-- Save Button -->
          <div class="flex justify-end">
            <button
              @click="updateSettings"
              :disabled="form.processing"
              class="px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors inline-flex items-center"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Salvar configurações
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>