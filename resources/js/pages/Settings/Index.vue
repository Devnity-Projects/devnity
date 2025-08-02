<template>
  <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between">
      <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          Configurações
        </h2>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="mt-6">
      <div class="sm:hidden">
        <label for="tabs" class="sr-only">Selecione uma aba</label>
        <select
          id="tabs"
          name="tabs"
          class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
          @change="handleTabChange"
        >
          <option value="general">Geral</option>
          <option value="profile">Perfil</option>
          <option value="password">Senha</option>
        </select>
      </div>
      <div class="hidden sm:block">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button
              @click="activeTab = 'general'"
              :class="[
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === 'general'
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Geral
            </button>
            <a
              :href="route('settings.profile.edit')"
              :class="[
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
                $page.component === 'Settings/Profile'
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Perfil
            </a>
            <a
              :href="route('settings.password.edit')"
              :class="[
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
                $page.component === 'Settings/Password'
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
              ]"
            >
              Senha
            </a>
          </nav>
        </div>
      </div>
    </div>

    <!-- General Settings Content -->
    <div v-if="activeTab === 'general'" class="mt-6">
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            Preferências Gerais
          </h3>
          <div class="mt-2 max-w-xl text-sm text-gray-500">
            <p>Configure suas preferências de tema, idioma e notificações.</p>
          </div>
          
          <form @submit.prevent="updateSettings" class="mt-5">
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
              <!-- Theme -->
              <div class="sm:col-span-3">
                <label for="theme" class="block text-sm font-medium text-gray-700">
                  Tema
                </label>
                <select
                  id="theme"
                  v-model="form.theme"
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                >
                  <option value="light">Claro</option>
                  <option value="dark">Escuro</option>
                </select>
              </div>

              <!-- Language -->
              <div class="sm:col-span-3">
                <label for="language" class="block text-sm font-medium text-gray-700">
                  Idioma
                </label>
                <select
                  id="language"
                  v-model="form.language"
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                >
                  <option value="pt-BR">Português (Brasil)</option>
                  <option value="en-US">English (US)</option>
                </select>
              </div>

              <!-- Timezone -->
              <div class="sm:col-span-3">
                <label for="timezone" class="block text-sm font-medium text-gray-700">
                  Fuso Horário
                </label>
                <select
                  id="timezone"
                  v-model="form.timezone"
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                >
                  <option value="America/Sao_Paulo">São Paulo (GMT-3)</option>
                  <option value="America/New_York">New York (GMT-5)</option>
                  <option value="Europe/London">London (GMT+0)</option>
                  <option value="Europe/Paris">Paris (GMT+1)</option>
                </select>
              </div>

              <!-- Date Format -->
              <div class="sm:col-span-3">
                <label for="date_format" class="block text-sm font-medium text-gray-700">
                  Formato de Data
                </label>
                <select
                  id="date_format"
                  v-model="form.date_format"
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                >
                  <option value="d/m/Y">DD/MM/AAAA</option>
                  <option value="m/d/Y">MM/DD/AAAA</option>
                  <option value="Y-m-d">AAAA-MM-DD</option>
                </select>
              </div>

              <!-- Time Format -->
              <div class="sm:col-span-3">
                <label for="time_format" class="block text-sm font-medium text-gray-700">
                  Formato de Hora
                </label>
                <select
                  id="time_format"
                  v-model="form.time_format"
                  class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
                >
                  <option value="H:i">24 horas (HH:MM)</option>
                  <option value="g:i A">12 horas (H:MM AM/PM)</option>
                </select>
              </div>
            </div>

            <!-- Notifications -->
            <div class="mt-6">
              <fieldset>
                <legend class="text-base font-medium text-gray-900">Notificações</legend>
                <div class="mt-4 space-y-4">
                  <div class="flex items-start">
                    <div class="flex items-center h-5">
                      <input
                        id="email_notifications"
                        v-model="form.email_notifications"
                        type="checkbox"
                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                      >
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="email_notifications" class="font-medium text-gray-700">
                        Notificações por Email
                      </label>
                      <p class="text-gray-500">Receba notificações importantes por email.</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start">
                    <div class="flex items-center h-5">
                      <input
                        id="browser_notifications"
                        v-model="form.browser_notifications"
                        type="checkbox"
                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                      >
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="browser_notifications" class="font-medium text-gray-700">
                        Notificações do Navegador
                      </label>
                      <p class="text-gray-500">Receba notificações push no navegador.</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start">
                    <div class="flex items-center h-5">
                      <input
                        id="task_reminders"
                        v-model="form.task_reminders"
                        type="checkbox"
                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                      >
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="task_reminders" class="font-medium text-gray-700">
                        Lembretes de Tarefas
                      </label>
                      <p class="text-gray-500">Receba lembretes sobre tarefas próximas ao vencimento.</p>
                    </div>
                  </div>
                  
                  <div class="flex items-start">
                    <div class="flex items-center h-5">
                      <input
                        id="project_updates"
                        v-model="form.project_updates"
                        type="checkbox"
                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                      >
                    </div>
                    <div class="ml-3 text-sm">
                      <label for="project_updates" class="font-medium text-gray-700">
                        Atualizações de Projetos
                      </label>
                      <p class="text-gray-500">Receba notificações sobre mudanças nos projetos.</p>
                    </div>
                  </div>
                </div>
              </fieldset>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
              <button
                type="button"
                @click="resetToDefaults"
                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Restaurar Padrões
              </button>
              <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Salvar Configurações
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

interface UserSettings {
  theme: string
  language: string
  email_notifications: boolean
  browser_notifications: boolean
  task_reminders: boolean
  project_updates: boolean
  timezone: string
  date_format: string
  time_format: string
}

interface Props {
  settings: UserSettings
}

const props = defineProps<Props>()

const activeTab = ref('general')

const form = useForm({
  theme: props.settings.theme,
  language: props.settings.language,
  email_notifications: props.settings.email_notifications,
  browser_notifications: props.settings.browser_notifications,
  task_reminders: props.settings.task_reminders,
  project_updates: props.settings.project_updates,
  timezone: props.settings.timezone,
  date_format: props.settings.date_format,
  time_format: props.settings.time_format
})

const handleTabChange = (event: Event) => {
  const target = event.target as HTMLSelectElement
  const value = target.value
  
  if (value === 'profile') {
    router.visit(route('settings.profile.edit'))
  } else if (value === 'password') {
    router.visit(route('settings.password.edit'))
  } else {
    activeTab.value = value
  }
}

const updateSettings = () => {
  form.patch(route('settings.update'), {
    onSuccess: () => {
      // Settings updated successfully
    }
  })
}

const resetToDefaults = () => {
  router.post(route('settings.reset'), {}, {
    onSuccess: () => {
      // Reload the form with default values
      form.theme = 'light'
      form.language = 'pt-BR'
      form.email_notifications = true
      form.browser_notifications = true
      form.task_reminders = true
      form.project_updates = true
      form.timezone = 'America/Sao_Paulo'
      form.date_format = 'd/m/Y'
      form.time_format = 'H:i'
    }
  })
}
</script>
