<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  size?: 'sm' | 'md' | 'lg' | 'xl'
  variant?: 'primary' | 'secondary' | 'minimal'
  animated?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  variant: 'primary',
  animated: false
})

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'sm': return 'h-6 w-6'
    case 'md': return 'h-8 w-8'
    case 'lg': return 'h-12 w-12'
    case 'xl': return 'h-16 w-16'
    default: return 'h-8 w-8'
  }
})

const iconClasses = computed(() => {
  const base = `relative ${sizeClasses.value}`
  
  if (props.variant === 'minimal') {
    return `${base} text-blue-600 dark:text-blue-400`
  }
  
  return base
})

const backgroundClasses = computed(() => {
  if (props.variant === 'minimal') return ''
  
  const base = 'absolute inset-0 rounded-lg'
  
  switch (props.variant) {
    case 'primary':
      return `${base} devnity-gradient`
    case 'secondary':
      return `${base} bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700`
    default:
      return `${base} devnity-gradient`
  }
})

const blurClasses = computed(() => {
  if (props.variant === 'minimal') return ''
  return 'absolute inset-0 devnity-gradient rounded-lg blur opacity-75'
})
</script>

<template>
  <div :class="[iconClasses, { 'animate-pulse': animated }]">
    <!-- Blur effect for glow -->
    <div v-if="variant !== 'minimal'" :class="blurClasses"></div>
    
    <!-- Background -->
    <div v-if="variant !== 'minimal'" :class="backgroundClasses"></div>
    
    <!-- Icon -->
    <div class="relative flex items-center justify-center h-full w-full">
      <svg 
        :class="[
          variant === 'minimal' ? 'h-full w-full' : 'h-2/3 w-2/3',
          variant === 'minimal' ? 'text-current' : 'text-white'
        ]"
        viewBox="0 0 24 24" 
        fill="none" 
        xmlns="http://www.w3.org/2000/svg"
      >
        <!-- Devnity Logo Icon -->
        <path 
          d="M12 2L3 7V17L12 22L21 17V7L12 2Z" 
          stroke="currentColor" 
          stroke-width="2" 
          stroke-linejoin="round"
        />
        <path 
          d="M12 8V16M8 10L12 12L16 10" 
          stroke="currentColor" 
          stroke-width="2" 
          stroke-linecap="round" 
          stroke-linejoin="round"
        />
        <circle cx="12" cy="12" r="2" fill="currentColor" />
      </svg>
    </div>
  </div>
</template>

<style scoped>
/* Custom animation for the icon */
@keyframes devnity-pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.8;
    transform: scale(1.05);
  }
}

.animate-pulse {
  animation: devnity-pulse 2s ease-in-out infinite;
}
</style>
