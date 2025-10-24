<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/components/Settings/SettingsLayout.vue'
import { Eye, EyeOff, Save } from 'lucide-vue-next'

interface Props {
  menuVisibility: {
    tasks: boolean
    projects: boolean
    clients: boolean
    financial: boolean
    support: boolean
  }
}

const props = defineProps<Props>()

const form = useForm({
  tasks: props.menuVisibility.tasks,
  projects: props.menuVisibility.projects,
  clients: props.menuVisibility.clients,
  financial: props.menuVisibility.financial,
  support: props.menuVisibility.support,
})

const menuItems = [
  { key: 'tasks', label: 'Tarefas', description: 'Gerenciamento de tarefas e atividades' },
  { key: 'projects', label: 'Projetos', description: 'Controle de projetos e entregas' },
  { key: 'clients', label: 'Clientes', description: 'Cadastro e gestão de clientes' },
  { key: 'financial', label: 'Financeiro', description: 'Controle financeiro (em desenvolvimento)' },
  { key: 'support', label: 'Suporte', description: 'Sistema de tickets de suporte (em desenvolvimento)' },
]

function submitForm() {
  form.post(route('settings.system.menu-visibility'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout title="Configurações do Sistema">
    <SettingsLayout>
      <div class="space-y-6">
        <!-- Header -->
        <div>
          <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            Configurações do Sistema
          </h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Configure a visibilidade dos itens do menu para todos os usuários
          </p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm" class="space-y-6">
          <!-- Menu Visibility Card -->
          <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
              Visibilidade do Menu
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
              Controle quais itens aparecem no menu de navegação. Módulos em desenvolvimento podem ser ocultados.
            </p>

            <div class="space-y-4">
              <div
                v-for="item in menuItems"
                :key="item.key"
                class="flex items-start gap-4 p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
              >
                <div class="flex items-center h-5 mt-1">
                  <input
                    :id="item.key"
                    v-model="(form as any)[item.key]"
                    type="checkbox"
                    class="h-5 w-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700"
                  />
                </div>
                <div class="flex-1">
                  <label
                    :for="item.key"
                    class="block text-sm font-medium text-gray-900 dark:text-gray-100 cursor-pointer"
                  >
                    <div class="flex items-center gap-2">
                      <component :is="(form as any)[item.key] ? Eye : EyeOff" class="h-4 w-4" />
                      {{ item.label }}
                    </div>
                  </label>
                  <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ item.description }}
                  </p>
                </div>
                <div>
                  <span
                    :class="[
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                      (form as any)[item.key]
                        ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300'
                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
                    ]"
                  >
                    {{ (form as any)[item.key] ? 'Visível' : 'Oculto' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="flex items-center justify-end gap-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              As alterações serão aplicadas para todos os usuários
            </p>
            <button
              type="submit"
              :disabled="form.processing"
              class="inline-flex items-center gap-2 px-6 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-lg transition-colors font-medium"
            >
              <Save class="h-4 w-4" />
              {{ form.processing ? 'Salvando...' : 'Salvar' }}
            </button>
          </div>
        </form>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
