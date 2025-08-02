<template>
  <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="md:flex md:items-center md:justify-between">
      <div class="flex-1 min-w-0">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
          Alterar Senha
        </h2>
      </div>
    </div>

    <!-- Navigation -->
    <div class="mt-6">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <a
            :href="route('settings.index')"
            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
          >
            Geral
          </a>
          <a
            :href="route('settings.profile.edit')"
            class="border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm"
          >
            Perfil
          </a>
          <span class="border-indigo-500 text-indigo-600 whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
            Senha
          </span>
        </nav>
      </div>
    </div>

    <!-- Password Change Form -->
    <div class="mt-6">
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="md:grid md:grid-cols-3 md:gap-6">
            <div class="md:col-span-1">
              <h3 class="text-lg font-medium leading-6 text-gray-900">
                Segurança da Conta
              </h3>
              <p class="mt-1 text-sm text-gray-600">
                Mantenha sua conta segura alterando sua senha regularmente.
              </p>
            </div>
            <div class="mt-5 md:mt-0 md:col-span-2">
              <form @submit.prevent="updatePassword">
                <div class="grid grid-cols-6 gap-6">
                  <!-- Current Password -->
                  <div class="col-span-6">
                    <label for="current_password" class="block text-sm font-medium text-gray-700">
                      Senha Atual
                    </label>
                    <input
                      id="current_password"
                      v-model="form.current_password"
                      type="password"
                      autocomplete="current-password"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      :class="{ 'border-red-300': form.errors.current_password }"
                    >
                    <div v-if="form.errors.current_password" class="mt-2 text-sm text-red-600">
                      {{ form.errors.current_password }}
                    </div>
                  </div>

                  <!-- New Password -->
                  <div class="col-span-6">
                    <label for="password" class="block text-sm font-medium text-gray-700">
                      Nova Senha
                    </label>
                    <input
                      id="password"
                      v-model="form.password"
                      type="password"
                      autocomplete="new-password"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      :class="{ 'border-red-300': form.errors.password }"
                    >
                    <div v-if="form.errors.password" class="mt-2 text-sm text-red-600">
                      {{ form.errors.password }}
                    </div>
                    <p class="mt-2 text-sm text-gray-500">
                      Sua senha deve ter pelo menos 8 caracteres.
                    </p>
                  </div>

                  <!-- Confirm Password -->
                  <div class="col-span-6">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                      Confirmar Nova Senha
                    </label>
                    <input
                      id="password_confirmation"
                      v-model="form.password_confirmation"
                      type="password"
                      autocomplete="new-password"
                      class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      :class="{ 'border-red-300': form.errors.password_confirmation }"
                    >
                    <div v-if="form.errors.password_confirmation" class="mt-2 text-sm text-red-600">
                      {{ form.errors.password_confirmation }}
                    </div>
                  </div>
                </div>

                <div class="mt-6 flex justify-end">
                  <button
                    type="button"
                    @click="$inertia.visit(route('settings.index'))"
                    class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                  >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Alterar Senha
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Password Security Tips -->
    <div class="mt-6">
      <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-blue-800">
              Dicas de Segurança
            </h3>
            <div class="mt-2 text-sm text-blue-700">
              <ul class="list-disc pl-5 space-y-1">
                <li>Use uma senha única que você não usa em outros sites</li>
                <li>Inclua letras maiúsculas, minúsculas, números e símbolos</li>
                <li>Evite informações pessoais como datas de nascimento</li>
                <li>Considere usar um gerenciador de senhas</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const updatePassword = () => {
  form.patch(route('settings.password.update'), {
    onSuccess: () => {
      form.reset()
    },
    onError: () => {
      form.reset('password', 'password_confirmation')
    }
  })
}
</script>
