<script setup lang="ts">
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

interface User {
  id: number
  name: string
  email: string
  phone?: string
  bio?: string
  avatar_url?: string
}

interface Props {
  user: User
}

const props = defineProps<Props>()

const avatarInput = ref<HTMLInputElement>()
const avatarPreview = ref<string>('')
const showDeleteConfirm = ref(false)

const form = useForm({
  name: props.user.name,
  email: props.user.email,
  phone: props.user.phone || '',
  bio: props.user.bio || '',
  avatar: null as File | null
})

const deleteForm = useForm({})

const handleAvatarChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    form.avatar = file
    
    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

const updateProfile = () => {
  form.post(route('settings.profile.update'), {
    onSuccess: () => {
      avatarPreview.value = ''
      if (avatarInput.value) {
        avatarInput.value.value = ''
      }
    }
  })
}

const removeAvatar = () => {
  router.delete(route('settings.profile.avatar.remove'), {
    onSuccess: () => {
      avatarPreview.value = ''
      if (avatarInput.value) {
        avatarInput.value.value = ''
      }
    }
  })
}

const deleteAccount = () => {
  deleteForm.delete(route('settings.profile.destroy'), {
    onSuccess: () => {
      router.visit('/')
    }
  })
}
</script>

<template>
  <AppLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
            Configurações
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">
            Gerencie suas informações pessoais e preferências
          </p>
        </div>
      </div>

      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:w-1/4">
          <nav class="space-y-1">
            <a
              :href="route('settings.index')"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
            >
              <svg class="text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Geral
            </a>
            <a
              :href="route('settings.profile.edit')"
              class="bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 group flex items-center px-3 py-2 text-sm font-medium rounded-lg"
            >
              <svg class="text-blue-500 dark:text-blue-300 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
        <div class="lg:w-3/4">
          <!-- Profile Information Card -->
          <div class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-6">
              <!-- Header -->
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Informações do Perfil</h2>
                  <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Atualize suas informações pessoais e foto de perfil
                  </p>
                </div>
              </div>

              <form @submit.prevent="updateProfile" class="space-y-6">
                <!-- Avatar Section -->
                <div class="flex items-center space-x-6">
                  <div class="relative">
                    <div class="h-20 w-20 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800 ring-2 ring-white dark:ring-gray-900 shadow-lg">
                      <img
                        v-if="avatarPreview || user.avatar_url"
                        :src="avatarPreview || user.avatar_url"
                        alt="Avatar"
                        class="h-full w-full object-cover"
                      >
                      <div v-else class="h-full w-full flex items-center justify-center">
                        <svg class="h-10 w-10 text-gray-400 dark:text-gray-500" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                      </div>
                    </div>
                    <button
                      type="button"
                      @click="avatarInput?.click()"
                      class="absolute -bottom-1 -right-1 bg-blue-600 hover:bg-blue-700 text-white p-1.5 rounded-full shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                    >
                      <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                    </button>
                  </div>
                  <div class="flex-1">
                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Foto de Perfil</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                      JPG, GIF ou PNG. Tamanho máximo de 2MB.
                    </p>
                    <div class="mt-2 flex space-x-2">
                      <input
                        ref="avatarInput"
                        type="file"
                        accept="image/*"
                        @change="handleAvatarChange"
                        class="hidden"
                      >
                      <button
                        type="button"
                        @click="avatarInput?.click()"
                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-xs font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                      >
                        <svg class="mr-1.5 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Alterar
                      </button>
                      <button
                        v-if="user.avatar_url"
                        type="button"
                        @click="removeAvatar"
                        class="inline-flex items-center px-3 py-1.5 border border-red-300 dark:border-red-600 rounded-md shadow-sm text-xs font-medium text-red-700 dark:text-red-400 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                      >
                        Remover
                      </button>
                    </div>
                    <div v-if="form.errors.avatar" class="mt-1 text-xs text-red-600 dark:text-red-400">
                      {{ form.errors.avatar }}
                    </div>
                  </div>
                </div>

                <!-- Form Fields -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                  <!-- Nome -->
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Nome completo
                    </label>
                    <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      placeholder="Digite seu nome"
                      class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 transition-colors"
                      :class="{ 'border-red-300 dark:border-red-600': form.errors.name }"
                    >
                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600 dark:text-red-400">
                      {{ form.errors.name }}
                    </div>
                  </div>

                  <!-- Email -->
                  <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Email
                    </label>
                    <input
                      id="email"
                      v-model="form.email"
                      type="email"
                      placeholder="Digite seu email"
                      class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 transition-colors"
                      :class="{ 'border-red-300 dark:border-red-600': form.errors.email }"
                    >
                    <div v-if="form.errors.email" class="mt-1 text-sm text-red-600 dark:text-red-400">
                      {{ form.errors.email }}
                    </div>
                  </div>

                  <!-- Telefone -->
                  <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                      Telefone
                    </label>
                    <input
                      id="phone"
                      v-model="form.phone"
                      type="tel"
                      placeholder="(11) 99999-9999"
                      class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 transition-colors"
                      :class="{ 'border-red-300 dark:border-red-600': form.errors.phone }"
                    >
                    <div v-if="form.errors.phone" class="mt-1 text-sm text-red-600 dark:text-red-400">
                      {{ form.errors.phone }}
                    </div>
                  </div>
                </div>

                <!-- Bio -->
                <div>
                  <label for="bio" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Biografia
                  </label>
                  <textarea
                    id="bio"
                    v-model="form.bio"
                    rows="3"
                    placeholder="Conte um pouco sobre você..."
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-400 transition-colors resize-none"
                    :class="{ 'border-red-300 dark:border-red-600': form.errors.bio }"
                  ></textarea>
                  <div v-if="form.errors.bio" class="mt-1 text-sm text-red-600 dark:text-red-400">
                    {{ form.errors.bio }}
                  </div>
                  <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Máximo de 500 caracteres
                  </p>
                </div>

                <!-- Submit buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                  <button
                    type="button"
                    @click="$inertia.visit(route('settings.index'))"
                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                  >
                    Cancelar
                  </button>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors inline-flex items-center"
                  >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <svg v-else class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Salvar alterações
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Delete Account Section -->
          <div class="mt-6 bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-red-200 dark:border-red-800 overflow-hidden">
            <div class="p-6">
              <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                  <div class="bg-red-100 dark:bg-red-900 rounded-full p-2">
                    <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                  </div>
                </div>
                <div class="flex-1">
                  <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    Zona de Perigo
                  </h3>
                  <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Uma vez excluída, sua conta e todos os dados associados serão perdidos permanentemente. Esta ação não pode ser desfeita.
                  </p>
                  <div class="mt-3">
                    <button
                      @click="showDeleteConfirm = true"
                      class="inline-flex items-center px-3 py-1.5 border border-red-300 dark:border-red-600 rounded-lg text-sm font-medium text-red-700 dark:text-red-400 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
                    >
                      <svg class="mr-1.5 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                      Excluir conta
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
      <div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl max-w-md w-full p-6 border border-gray-200 dark:border-gray-800">
        <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 dark:bg-red-900 rounded-full mb-4">
          <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 text-center mb-2">
          Excluir conta
        </h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 text-center mb-6">
          Tem certeza que deseja excluir sua conta? Esta ação é permanente e não pode ser desfeita. Todos os seus dados serão perdidos.
        </p>
        <div class="flex space-x-3">
          <button
            @click="showDeleteConfirm = false"
            class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
          >
            Cancelar
          </button>
          <button
            @click="deleteAccount"
            :disabled="deleteForm.processing"
            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors inline-flex items-center justify-center"
          >
            <svg v-if="deleteForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ deleteForm.processing ? 'Excluindo...' : 'Sim, excluir' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>