<script setup>
import { ref, onMounted } from 'vue'
import { Sun, Moon, Code2, Zap, Shield, Rocket } from 'lucide-vue-next'

// Dark mode
const isDark = ref(
  localStorage.theme === 'dark' ||
  (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
)

const toggleDark = () => {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
  localStorage.theme = isDark.value ? 'dark' : 'light'
}

onMounted(() => {
  document.documentElement.classList.toggle('dark', isDark.value)
})

// Animated features
const features = [
  {
    icon: Zap,
    title: 'Inovação Contínua',
    description: 'Sempre na vanguarda da tecnologia'
  },
  {
    icon: Shield,
    title: 'Excelência Técnica',
    description: 'Código limpo, seguro e escalável'
  },
  {
    icon: Rocket,
    title: 'Foco no Resultado',
    description: 'Impacto real e valor mensurável'
  }
]
</script>

<template>
  <div class="min-h-screen flex bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    <!-- Theme Toggle -->
    <button
      @click="toggleDark"
      class="fixed top-4 right-4 z-50 p-3 rounded-full bg-white/80 dark:bg-gray-900/80 backdrop-blur-sm border border-gray-200/50 dark:border-gray-700/50 hover:bg-white dark:hover:bg-gray-900 transition-all duration-200 shadow-lg"
      aria-label="Alternar tema"
    >
      <Sun v-if="isDark" class="h-5 w-5 text-orange-500" />
      <Moon v-else class="h-5 w-5 text-gray-600" />
    </button>

    <!-- Left Side - Branding -->
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">
      <!-- Background with gradient -->
      <div class="absolute inset-0 devnity-gradient"></div>
      
      <!-- Animated background shapes -->
      <div class="absolute inset-0">
        <div class="absolute top-20 left-20 w-32 h-32 bg-white/10 rounded-full blur-xl animate-pulse"></div>
        <div class="absolute bottom-32 right-20 w-24 h-24 bg-white/10 rounded-full blur-xl animate-pulse delay-1000"></div>
        <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-white/10 rounded-full blur-xl animate-pulse delay-500"></div>
      </div>

      <!-- Content -->
      <div class="relative z-10 flex flex-col justify-center px-12 text-white">
        <!-- Logo and Brand -->
        <div class="mb-12">
          <div class="flex items-center gap-4 mb-6">
            <div class="p-3 bg-white/20 backdrop-blur-sm rounded-2xl">
              <Code2 class="h-12 w-12 text-white" />
            </div>
            <div>
              <h1 class="text-4xl font-bold">Devnity</h1>
              <p class="text-white/80 text-lg">Development Solutions</p>
            </div>
          </div>
          
          <h2 class="text-3xl font-bold mb-4 leading-tight">
            Transformando desafios em soluções tecnológicas
          </h2>
          
          <p class="text-xl text-white/90 leading-relaxed">
            Somos seus parceiros estratégicos na criação de sistemas inteligentes, 
            escaláveis e personalizados para impulsionar o crescimento da sua empresa.
          </p>
        </div>

        <!-- Features -->
        <div class="space-y-6">
          <div 
            v-for="(feature, index) in features"
            :key="index"
            class="flex items-center gap-4 devnity-animate-in"
            :style="{ animationDelay: `${index * 200}ms` }"
          >
            <div class="p-2 bg-white/20 backdrop-blur-sm rounded-lg">
              <component :is="feature.icon" class="h-6 w-6 text-white" />
            </div>
            <div>
              <h3 class="font-semibold text-lg">{{ feature.title }}</h3>
              <p class="text-white/80">{{ feature.description }}</p>
            </div>
          </div>
        </div>

        <!-- Quote -->
        <div class="mt-12 p-6 bg-white/10 backdrop-blur-sm rounded-xl border border-white/20">
          <blockquote class="text-lg italic">
            "Na Devnity, a inovação não é apenas um conceito, mas uma prática diária 
            que nos permite entregar soluções digitais que realmente fazem a diferença."
          </blockquote>
        </div>
      </div>
    </div>

    <!-- Right Side - Auth Form -->
    <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-20 xl:px-24">
      <div class="mx-auto w-full max-w-sm lg:w-96">
        <!-- Mobile Logo -->
        <div class="lg:hidden text-center mb-8">
          <div class="inline-flex items-center gap-3 mb-4">
            <div class="p-2 devnity-gradient rounded-xl">
              <Code2 class="h-8 w-8 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold devnity-text-gradient">Devnity</h1>
              <p class="text-sm text-gray-500 dark:text-gray-400">Development Solutions</p>
            </div>
          </div>
        </div>

        <!-- Auth Content Slot -->
        <div class="devnity-animate-in">
          <slot />
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
          <p class="text-xs text-gray-500 dark:text-gray-400">
            © 2025 Devnity. Todos os direitos reservados.
          </p>
          <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
            Transformando desafios em oportunidades
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom animations for auth page */
@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-10px) rotate(2deg); }
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

/* Smooth entrance for features */
.devnity-animate-in {
  animation: slideInFromLeft 0.6s ease-out forwards;
  opacity: 0;
}

@keyframes slideInFromLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

/* Enhanced glass morphism */
.glass-effect {
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.dark .glass-effect {
  background: rgba(0, 0, 0, 0.2);
  border: 1px solid rgba(255, 255, 255, 0.1);
}
</style>
