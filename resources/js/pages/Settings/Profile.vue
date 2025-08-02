<template>
  <div class="max-w-4xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              Informações do Perfil
            </h3>
            <p class="mt-1 text-sm text-gray-600">
              Atualize suas informações pessoais e foto de perfil.
            </p>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <form @submit.prevent="updateProfile">
              <div class="grid grid-cols-6 gap-6">
                <!-- Avatar Upload -->
                <div class="col-span-6 sm:col-span-4">
                  <label class="block text-sm font-medium text-gray-700">
                    Foto de Perfil
                  </label>
                  <div class="mt-1 flex items-center space-x-5">
                    <div class="h-20 w-20 rounded-full overflow-hidden bg-gray-100">
                      <img
                        v-if="avatarPreview || user.avatar_url"
                        :src="avatarPreview || user.avatar_url"
                        alt="Avatar"
                        class="h-full w-full object-cover"
                      >
                      <div v-else class="h-full w-full flex items-center justify-center">
                        <svg class="h-12 w-12 text-gray-300" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                      </div>
                    </div>
                    <div>
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
                        class="bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                      >
                        Alterar
                      </button>
                      <button
                        v-if="user.avatar_url"
                        type="button"
                        @click="removeAvatar"
                        class="ml-2 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                      >
                        Remover
                      </button>
                    </div>
                  </div>
                  <div v-if="form.errors.avatar" class="mt-2 text-sm text-red-600">
                    {{ form.errors.avatar }}
                  </div>
                </div>

                <!-- Nome -->
                <div class="col-span-6 sm:col-span-3">
                  <label for="name" class="block text-sm font-medium text-gray-700">
                    Nome
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    :class="{ 'border-red-300': form.errors.name }"
                  >
                  <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                    {{ form.errors.name }}
                  </div>
                </div>

                <!-- Email -->
                <div class="col-span-6 sm:col-span-3">
                  <label for="email" class="block text-sm font-medium text-gray-700">
                    Email
                  </label>
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                    :class="{ 'border-red-300': form.errors.email }"
                  >
                  <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">
                    {{ form.errors.email }}
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
                  Salvar Alterações
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Account Section -->
    <div class="mt-10 bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <div class="md:grid md:grid-cols-3 md:gap-6">
          <div class="md:col-span-1">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              Excluir Conta
            </h3>
            <p class="mt-1 text-sm text-gray-600">
              Uma vez excluída, sua conta não poderá ser recuperada.
            </p>
          </div>
          <div class="mt-5 md:mt-0 md:col-span-2">
            <button
              @click="showDeleteConfirm = true"
              class="bg-red-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
            >
              Excluir Conta
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-5">Confirmar Exclusão</h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <button
              @click="deleteAccount"
              :disabled="deleteForm.processing"
              class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:opacity-50"
            >
              <span v-if="deleteForm.processing">Excluindo...</span>
              <span v-else>Sim, Excluir</span>
            </button>
            <button
              @click="showDeleteConfirm = false"
              class="mt-3 px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
              Cancelar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { router } from '@inertiajs/vue3'

interface User {
  id: number
  name: string
  email: string
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
