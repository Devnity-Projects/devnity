<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
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
    <div class="flex flex-col items-center mb-8">
      <img src="/images/logo-devnity.png" alt="Devnity Logo" class="w-32 h-32 mb-4 drop-shadow-md" />
      <h1 class="text-2xl font-bold mt-2 text-[#6a0dad] dark:text-[#b59cff]">Bem-vindo ao Devnity</h1>
      <p class="text-gray-400 dark:text-gray-300 mb-2 text-sm">Sistema de Gestão SaaS para empresas</p>
    </div>
    <div class="bg-white dark:bg-[#232336] shadow-2xl rounded-2xl px-10 py-8 w-full max-w-sm">
      <form @submit.prevent="submit" autocomplete="on">
        <div v-if="form.errors.email || form.errors.password" class="mb-4 flex items-center gap-2 bg-[#ffeaea] dark:bg-[#442429] text-[#e55e55] rounded-xl px-4 py-3 border border-[#e55e55]/50">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
          </svg>
          <span class="font-medium">
            {{ form.errors.email || form.errors.password }}
          </span>
        </div>
        <div class="mb-6">
          <label class="block mb-1 text-[#6a0dad] dark:text-[#b59cff] font-semibold">E-mail</label>
          <input v-model="form.email" type="email" autocomplete="email"
            class="w-full rounded-xl border border-[#d1d5db] dark:border-[#444466] px-4 py-3 focus:outline-none focus:border-[#6a0dad] dark:focus:border-[#b59cff] transition bg-white dark:bg-[#1b1b29] text-black dark:text-white"
            placeholder="Seu e-mail" />
        </div>
        <div class="mb-6 relative">
          <label class="block mb-1 text-[#6a0dad] dark:text-[#b59cff] font-semibold">Senha</label>
          <input v-model="form.password"
            :type="showPassword ? 'text' : 'password'"
            autocomplete="current-password"
            class="w-full rounded-xl border border-[#d1d5db] dark:border-[#444466] px-4 py-3 focus:outline-none focus:border-[#6a0dad] dark:focus:border-[#b59cff] transition bg-white dark:bg-[#1b1b29] text-black dark:text-white"
            placeholder="Senha" />
          <button type="button"
            class="absolute right-4 top-8 text-gray-400 dark:text-gray-300"
            @click="showPassword = !showPassword"
            tabindex="-1"
          >
            <svg v-if="!showPassword" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="#6a0dad" stroke-width="2" d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3" stroke="#6a0dad" stroke-width="2"/></svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24"><path stroke="#e55e55" stroke-width="2" d="m3 3 18 18M2 12s-4-7 10-7a9.44 9.44 0 0 1 6.49 2.63M22 12s-4 7-10 7a9.44 9.44 0 0 1-6.49-2.63"/><circle cx="12" cy="12" r="3" stroke="#e55e55" stroke-width="2"/></svg>
          </button>
        </div>
        <div class="mb-4 flex items-center gap-2">
          <input v-model="form.remember" type="checkbox" id="remember" />
          <label for="remember" class="text-gray-600 dark:text-gray-300">Lembrar-me</label>
        </div>
        <button
          :disabled="form.processing"
          type="submit"
          class="w-full bg-[#6a0dad] hover:bg-[#5a089e] text-white font-bold py-3 rounded-xl transition mb-3 shadow"
        >Entrar</button>
        <div class="text-center text-sm">
          <a href="/forgot-password" class="text-[#6a0dad] dark:text-[#b59cff] font-semibold hover:underline">Esqueci minha senha</a>
        </div>
      </form>
    </div>
    <div class="mt-8 text-gray-400 dark:text-gray-500 text-xs text-center">
      &copy; 2025 Devnity — Sistema de Gestão SaaS<br/>
      <span class="text-[#e55e55]">Versão beta</span>
    </div>
  </AuthLayout>
</template>
