<script setup lang="ts">
import { 
  FolderKanban, 
  TrendingUp, 
  CheckCircle, 
  AlertTriangle,
  Clock,
  PlayCircle
} from 'lucide-vue-next'

interface Stats {
  total: number
  active: number
  completed: number
  overdue: number
  planning: number
  in_progress: number
}

interface Props {
  stats: Stats
}

const props = defineProps<Props>()

const quickStats = [
  {
    label: 'Total',
    value: props.stats.total,
    icon: FolderKanban,
    color: 'text-blue-600 dark:text-blue-400',
    bgColor: 'bg-blue-50 dark:bg-blue-950/20'
  },
  {
    label: 'Ativos',
    value: props.stats.active,
    icon: TrendingUp,
    color: 'text-green-600 dark:text-green-400',
    bgColor: 'bg-green-50 dark:bg-green-950/20'
  },
  {
    label: 'Concluídos',
    value: props.stats.completed,
    icon: CheckCircle,
    color: 'text-emerald-600 dark:text-emerald-400',
    bgColor: 'bg-emerald-50 dark:bg-emerald-950/20'
  },
  {
    label: 'Atrasados',
    value: props.stats.overdue,
    icon: AlertTriangle,
    color: 'text-red-600 dark:text-red-400',
    bgColor: 'bg-red-50 dark:bg-red-950/20'
  },
  {
    label: 'Planejamento',
    value: props.stats.planning,
    icon: Clock,
    color: 'text-yellow-600 dark:text-yellow-400',
    bgColor: 'bg-yellow-50 dark:bg-yellow-950/20'
  },
  {
    label: 'Em Andamento',
    value: props.stats.in_progress,
    icon: PlayCircle,
    color: 'text-purple-600 dark:text-purple-400',
    bgColor: 'bg-purple-50 dark:bg-purple-950/20'
  }
]
</script>

<template>
  <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    <div 
      v-for="stat in quickStats" 
      :key="stat.label"
      :class="[
        'p-4 rounded-lg border border-gray-200 dark:border-gray-700',
        stat.bgColor
      ]"
    >
      <div class="flex items-center gap-3">
        <component 
          :is="stat.icon" 
          :class="['h-8 w-8', stat.color]"
        />
        
        <div>
          <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
            {{ stat.value }}
          </p>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ stat.label }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
