<script setup lang="ts">
import { computed } from 'vue'
import {
  CheckCircle,
  Clock,
  AlertCircle,
  PlayCircle,
  Pause,
  TrendingUp
} from 'lucide-vue-next'

interface Props {
  stats: {
    total: number
    todo: number
    in_progress: number
    review: number
    completed: number
    overdue: number
  }
}

const props = defineProps<Props>()

const statsData = computed(() => [
  {
    label: 'Total',
    value: props.stats.total,
    icon: TrendingUp,
    color: 'text-blue-600 dark:text-blue-400',
    bg: 'bg-blue-50 dark:bg-blue-900/20'
  },
  {
    label: 'A Fazer',
    value: props.stats.todo,
    icon: Clock,
    color: 'text-gray-600 dark:text-gray-400',
    bg: 'bg-gray-50 dark:bg-gray-900/20'
  },
  {
    label: 'Em Progresso',
    value: props.stats.in_progress,
    icon: PlayCircle,
    color: 'text-yellow-600 dark:text-yellow-400',
    bg: 'bg-yellow-50 dark:bg-yellow-900/20'
  },
  {
    label: 'Em Revisão',
    value: props.stats.review,
    icon: Pause,
    color: 'text-purple-600 dark:text-purple-400',
    bg: 'bg-purple-50 dark:bg-purple-900/20'
  },
  {
    label: 'Concluídos',
    value: props.stats.completed,
    icon: CheckCircle,
    color: 'text-green-600 dark:text-green-400',
    bg: 'bg-green-50 dark:bg-green-900/20'
  },
  {
    label: 'Atrasados',
    value: props.stats.overdue,
    icon: AlertCircle,
    color: 'text-red-600 dark:text-red-400',
    bg: 'bg-red-50 dark:bg-red-900/20'
  }
])
</script>

<template>
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
    <div
      v-for="stat in statsData"
      :key="stat.label"
      class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow"
    >
      <div class="flex items-center">
        <div :class="[stat.bg, 'rounded-lg p-2 mr-3']">
          <component :is="stat.icon" :class="[stat.color, 'h-6 w-6']" />
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
            {{ stat.label }}
          </p>
          <p :class="[stat.color, 'text-2xl font-bold']">
            {{ stat.value }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
