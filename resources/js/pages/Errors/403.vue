<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'

interface Props {
  status?: number
  message?: string
}

const props = withDefaults(defineProps<Props>(), {
  status: 403,
  message: 'Você não tem permissão para acessar este recurso.'
})

const title = computed(() => {
  return {
    403: 'Acesso Negado',
    404: 'Página Não Encontrada',
    419: 'Sessão Expirada',
    429: 'Muitas Requisições',
    500: 'Erro no Servidor',
    503: 'Serviço Indisponível',
  }[props.status] || 'Erro'
})

const description = computed(() => {
  return {
    403: 'Desculpe, você não tem as permissões necessárias para acessar esta página.',
    404: 'Desculpe, a página que você está procurando não foi encontrada.',
    419: 'Sua sessão expirou. Por favor, recarregue a página e tente novamente.',
    429: 'Você fez muitas requisições em pouco tempo. Por favor, aguarde um momento.',
    500: 'Ops! Algo deu errado no servidor. Já estamos trabalhando nisso.',
    503: 'O sistema está temporariamente em manutenção. Voltaremos em breve.',
  }[props.status] || props.message
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-900 dark:to-gray-950 flex items-center justify-center px-4">
    <Head :title="`${status} - ${title}`" />
    
    <div class="max-w-2xl w-full">
      <!-- Animated Icon Container -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-32 h-32 rounded-full bg-red-100 dark:bg-red-900/20 mb-6 animate-pulse">
          <svg class="w-16 h-16 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        
        <!-- Status Code -->
        <div class="mb-4">
          <h1 class="text-8xl font-extrabold text-gray-900 dark:text-white leading-none">
            {{ status }}
          </h1>
        </div>
        
        <!-- Title -->
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
          {{ title }}
        </h2>
        
        <!-- Description -->
        <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-lg mx-auto">
          {{ description }}
        </p>

        <!-- Permission Denied Details (403 only) -->
        <div v-if="status === 403" class="mb-8 p-6 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700">
          <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
              <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
            </div>
            <div class="flex-1 text-left">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                Por que estou vendo esta mensagem?
              </h3>
              <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                <li class="flex items-start gap-2">
                  <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <span>Você não possui a permissão necessária para acessar este recurso</span>
                </li>
                <li class="flex items-start gap-2">
                  <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <span>Sua role atual não tem acesso a esta funcionalidade</span>
                </li>
                <li class="flex items-start gap-2">
                  <svg class="w-5 h-5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                  <span>Entre em contato com o administrador para solicitar acesso</span>
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
            Voltar para o Dashboard
          </Link>
          
          <button
            @click="$inertia.visit($page.url, { preserveState: false, preserveScroll: false })"
            class="inline-flex items-center px-6 py-3 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white font-medium rounded-lg shadow hover:shadow-lg transform hover:scale-105 transition-all duration-200"
          >
            <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Tentar Novamente
          </button>
        </div>

        <!-- Support Link -->
        <div class="mt-8 pt-8 border-t border-gray-200 dark:border-gray-700">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Precisa de ajuda? 
            <a href="mailto:suporte@devnity.com" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 font-medium ml-1 hover:underline">
              Entre em contato com o suporte
            </a>
          </p>
        </div>
      </div>

      <!-- Decorative Elements -->
      <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none opacity-10">
        <div class="absolute top-20 left-10 w-72 h-72 bg-red-500 rounded-full mix-blend-multiply filter blur-xl animate-blob"></div>
        <div class="absolute top-40 right-10 w-72 h-72 bg-yellow-500 rounded-full mix-blend-multiply filter blur-xl animate-blob animation-delay-2000"></div>
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
