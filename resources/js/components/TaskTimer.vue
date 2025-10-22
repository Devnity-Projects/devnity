<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Play, Pause, StopCircle, Clock } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import axios from '@/bootstrap'

interface Props {
  taskId: number
  initialStatus?: {
    has_active_timer: boolean
    entry?: {
      id: number
      started_at: string
      elapsed_seconds: number
    }
    total_hours_worked?: number
  }
}

const props = defineProps<Props>()

const isRunning = ref(false)
const elapsedSeconds = ref(0)
const startedAt = ref<string | null>(null)
const totalHoursWorked = ref(props.initialStatus?.total_hours_worked ?? 0)
const isLoading = ref(false)
let intervalId: number | null = null

const formattedTime = computed(() => {
  const hours = Math.floor(elapsedSeconds.value / 3600)
  const minutes = Math.floor((elapsedSeconds.value % 3600) / 60)
  const seconds = elapsedSeconds.value % 60
  return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
})

const formattedTotalHours = computed(() => {
  const hours = Math.floor(totalHoursWorked.value)
  const minutes = Math.round((totalHoursWorked.value - hours) * 60)
  return `${hours}h ${String(minutes).padStart(2, '0')}m`
})

const buttonClass = computed(() => {
  if (isRunning.value) {
    return 'bg-red-600 hover:bg-red-700 text-white'
  }
  return 'bg-green-600 hover:bg-green-700 text-white'
})

async function startTimer() {
  if (isLoading.value) return
  
  isLoading.value = true
  try {
    const response = await axios.post(`/tasks/${props.taskId}/timer/start`)
    
    if (response.data.success) {
      isRunning.value = true
      startedAt.value = response.data.entry.started_at
      elapsedSeconds.value = 0
      startInterval()
    }
  } catch (error) {
    console.error('Erro ao iniciar timer:', error)
    alert('Erro ao iniciar timer. Tente novamente.')
  } finally {
    isLoading.value = false
  }
}

async function stopTimer() {
  if (isLoading.value) return
  
  isLoading.value = true
  try {
    const response = await axios.post(`/tasks/${props.taskId}/timer/stop`)
    
    if (response.data.success) {
      isRunning.value = false
      elapsedSeconds.value = 0
      stopInterval()
      
      // Mostrar mensagem de sucesso
      console.log('Timer parado:', response.data)
      
      // Recarregar página completa para atualizar todos os dados
      window.location.reload()
    }
  } catch (error) {
    console.error('Erro ao parar timer:', error)
    alert('Erro ao parar timer. Tente novamente.')
  } finally {
    isLoading.value = false
  }
}

function toggleTimer() {
  if (isRunning.value) {
    stopTimer()
  } else {
    startTimer()
  }
}

function startInterval() {
  if (intervalId) return
  
  intervalId = window.setInterval(() => {
    elapsedSeconds.value++
  }, 1000)
}

function stopInterval() {
  if (intervalId) {
    clearInterval(intervalId)
    intervalId = null
  }
}

async function checkStatus() {
  try {
    const response = await axios.get(`/tasks/${props.taskId}/timer/status`)
    const data = response.data
    
    // Atualizar total de horas trabalhadas
    totalHoursWorked.value = data.total_hours_worked ?? 0
    
    if (data.has_active_timer && data.entry) {
      isRunning.value = true
      startedAt.value = data.entry.started_at
      elapsedSeconds.value = data.entry.elapsed_seconds
      startInterval()
    }
  } catch (error) {
    console.error('Erro ao verificar status do timer:', error)
  }
}

onMounted(() => {
  // Verificar status inicial
  if (props.initialStatus?.has_active_timer && props.initialStatus.entry) {
    isRunning.value = true
    startedAt.value = props.initialStatus.entry.started_at
    elapsedSeconds.value = props.initialStatus.entry.elapsed_seconds
    startInterval()
  } else {
    checkStatus()
  }
})

onUnmounted(() => {
  stopInterval()
})
</script>

<template>
  <div class="flex items-center gap-3">
    <!-- Timer Display -->
    <div class="flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
      <Clock class="h-4 w-4 text-gray-600 dark:text-gray-400" :class="{ 'text-green-600 animate-pulse': isRunning }" />
      <span class="font-mono text-lg font-semibold text-gray-900 dark:text-white">
        {{ formattedTime }}
      </span>
    </div>

    <!-- Control Button -->
    <button
      @click="toggleTimer"
      :disabled="isLoading"
      :class="buttonClass"
      class="inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <Play v-if="!isRunning && !isLoading" class="h-4 w-4" />
      <StopCircle v-else-if="isRunning && !isLoading" class="h-4 w-4" />
      <div v-else class="h-4 w-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
      {{ isLoading ? 'Processando...' : (isRunning ? 'Parar' : 'Iniciar') }}
    </button>
  </div>
</template>
