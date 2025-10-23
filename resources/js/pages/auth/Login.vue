<script setup lang="ts">
import { useForm, router, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import { Eye, EyeOff, Mail, Lock, AlertCircle, ArrowRight } from 'lucide-vue-next'
import AuthLayout from '@/Layouts/AuthLayout.vue'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)

function submit() {
  form.post('/login', {
    onError: () => {},
    onSuccess: () => {
      form.reset('password')
      router.visit('/dashboard')
    },
  })
}
</script>

<template>
  <AuthLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="text-center lg:text-left">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
          Acesse sua conta
        </h2>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Entre com suas credenciais do Active Directory
        </p>
      </div>

      <!-- Error Alert -->
      <div 
        v-if="form.errors.email || form.errors.password" 
        class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300"
      >
        <AlertCircle class="h-5 w-5 flex-shrink-0" />
        <span class="text-sm font-medium">
          {{ form.errors.email || form.errors.password }}
        </span>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="submit" class="space-y-6" autocomplete="on">
        <!-- Email Field -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Usuário ou E-mail
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Mail class="h-5 w-5 text-gray-400" />
            </div>
            <input
              id="email"
              v-model="form.email"
              type="text"
              autocomplete="username"
              required
              class="block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
              placeholder="usuario.dominio ou seu@email.com"
            />
          </div>
          <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Use seu usuário do Active Directory ou e-mail
          </p>
        </div>

        <!-- Password Field -->
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Senha
          </label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Lock class="h-5 w-5 text-gray-400" />
            </div>
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              autocomplete="current-password"
              required
              class="block w-full pl-10 pr-10 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
              placeholder="Sua senha"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
              tabindex="-1"
            >
              <Eye v-if="!showPassword" class="h-5 w-5" />
              <EyeOff v-else class="h-5 w-5" />
            </button>
          </div>
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <input
              id="remember"
              v-model="form.remember"
              type="checkbox"
              class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"
            />
            <label for="remember" class="ml-2 block text-sm text-gray-700 dark:text-gray-300">
              Lembrar-me
            </label>
          </div>
          <Link
            href="/forgot-password"
            class="text-sm font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors"
          >
            Esqueci minha senha
          </Link>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="form.processing"
          class="w-full flex justify-center items-center gap-2 py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white devnity-gradient hover:devnity-gradient-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
        >
          <span v-if="!form.processing">Entrar</span>
          <span v-else>Entrando...</span>
          <ArrowRight v-if="!form.processing" class="h-4 w-4" />
          <div v-else class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
        </button>
      </form>

      <!-- Register Link -->
      <div class="text-center">
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Não tem uma conta?
          <Link
            href="/register"
            class="font-medium text-blue-600 hover:text-blue-500 dark:text-blue-400 dark:hover:text-blue-300 transition-colors ml-1"
          >
            Criar conta
          </Link>
        </p>
      </div>

    </div>
  </AuthLayout>
</template>
