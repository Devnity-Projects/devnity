<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

interface Props {
  status?: number
  message?: string
}

const props = withDefaults(defineProps<Props>(), {
  status: 404,
  message: 'A página que você está procurando não foi encontrada.'
})

const goBack = () => {
  window.history.back()
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950 flex items-center justify-center px-4">
    <Head title="404 - Página Não Encontrada" />
    
    <div class="max-w-2xl w-full">
      <div class="text-center mb-8">
        <!-- Animated Icon -->
        <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-blue-100 dark:bg-blue-900/20 mb-6">
          <svg class="w-20 h-20 text-blue-600 dark:text-blue-400 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        
        <!-- Status Code -->
        <div class="mb-4">
          <h1 class="text-8xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400 leading-none">
            404
          </h1>
        </div>
        
        <!-- Title -->
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
          Página Não Encontrada
        </h2>
        
        <!-- Description -->
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-lg mx-auto">
          Ops! A página que você está procurando não existe ou foi movida para outro lugar.
        </p>

        <!-- Suggestions -->
        <div class="mb-8 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">
            O que você pode fazer?
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-left">
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              <span class="text-sm text-gray-600 dark:text-gray-400">Verificar se o endereço está correto</span>
            </div>
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              <span class="text-sm text-gray-600 dark:text-gray-400">Voltar para o dashboard</span>
            </div>
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              <span class="text-sm text-gray-600 dark:text-gray-400">Usar a busca para encontrar o que precisa</span>
            </div>
            <div class="flex items-start gap-3">
              <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
              <span class="text-sm text-gray-600 dark:text-gray-400">Entrar em contato com o suporte</span>
            </div>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <Link
            :href="route('dashboard')"
            class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Ir para o Dashboard
          </Link>
          
          <button
            @click="goBack"
            class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:scale-105 transition-all duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Voltar
          </button>
        </div>
      </div>

      <!-- Decorative Elements -->
      <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-10">
        <div class="absolute top-20 left-10 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-xl animate-blob"></div>
        <div class="absolute top-40 right-10 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-4000"></div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes blob {
  0% {
    transform: translate(0px, 0px) scale(1);
  }
  33% {
    transform: translate(30px, -50px) scale(1.1);
  }
  66% {
    transform: translate(-20px, 20px) scale(0.9);
  }
  100% {
    transform: translate(0px, 0px) scale(1);
  }
}

.animate-blob {
  animation: blob 7s infinite;
}

.animation-delay-2000 {
  animation-delay: 2s;
}

.animation-delay-4000 {
  animation-delay: 4s;
}
</style>
