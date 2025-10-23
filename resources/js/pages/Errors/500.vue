<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'

interface Props {
  status?: number
  message?: string
}

const props = withDefaults(defineProps<Props>(), {
  status: 500,
  message: 'Ocorreu um erro interno no servidor.'
})

const reloadPage = () => {
  window.location.reload()
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950 flex items-center justify-center px-4">
    <Head :title="`${status} - Erro no Servidor`" />
    
    <div class="max-w-2xl w-full">
      <div class="text-center mb-8">
        <!-- Animated Icon -->
        <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-orange-100 dark:bg-orange-900/20 mb-6">
          <svg class="w-20 h-20 text-orange-600 dark:text-orange-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        
        <!-- Status Code -->
        <div class="mb-4">
          <h1 class="text-8xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-red-600 dark:from-orange-400 dark:to-red-400 leading-none">
            {{ status }}
          </h1>
        </div>
        
        <!-- Title -->
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
          {{ status === 503 ? 'Serviço Indisponível' : 'Erro no Servidor' }}
        </h2>
        
        <!-- Description -->
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-lg mx-auto">
          {{ status === 503 
            ? 'O sistema está temporariamente em manutenção. Voltaremos em breve.' 
            : 'Ops! Algo deu errado no servidor. Nossa equipe já foi notificada e está trabalhando na solução.'
          }}
        </p>

        <!-- Error Details -->
        <div class="mb-8 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <svg class="w-6 h-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="flex-1 text-left">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                O que aconteceu?
              </h3>
              <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ status === 503 
                  ? 'Estamos realizando manutenção programada para melhorar o sistema.' 
                  : 'Encontramos um problema inesperado ao processar sua solicitação.'
                }}
              </p>
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                O que você pode fazer?
              </h3>
              <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                <li class="flex items-start gap-2">
                  <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <span>Aguardar alguns instantes e tentar novamente</span>
                </li>
                <li class="flex items-start gap-2">
                  <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <span>Recarregar a página</span>
                </li>
                <li class="flex items-start gap-2">
                  <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <span>Se o problema persistir, entre em contato com o suporte</span>
                </li>
              </ul>
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
            @click="reloadPage"
            class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:scale-105 transition-all duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Recarregar Página
          </button>
        </div>

        <!-- Support Link -->
        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Precisa de ajuda urgente? 
            <a href="mailto:suporte@devnity.com" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium ml-1 hover:underline">
              Entre em contato com o suporte
            </a>
          </p>
        </div>
      </div>

      <!-- Decorative Elements -->
      <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-10">
        <div class="absolute top-20 left-10 w-72 h-72 bg-orange-500 rounded-full mix-blend-multiply filter blur-xl animate-blob"></div>
        <div class="absolute top-40 right-10 w-72 h-72 bg-red-500 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000"></div>
        <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-yellow-500 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-4000"></div>
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
