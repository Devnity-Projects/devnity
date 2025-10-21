<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Link } from '@inertiajs/vue3'
import { useAbility } from '@/composables/useAbility'
import {
  Users,
  FolderKanban,
  Briefcase,
  LifeBuoy,
  TrendingUp,
  Clock,
  CheckCircle,
  AlertCircle,
  Code2,
  Zap,
  Shield,
  Rocket,
  ArrowUpRight,
  Calendar,
  Activity
} from 'lucide-vue-next'

const props = defineProps<{ 
  stats: Record<string, number>
  recentProjects?: Array<any>
  urgentTasks?: Array<any>
  recentActivity?: Array<any>
}>()

const currentTime = ref(new Date())
const { can } = useAbility()
const canCreateClient = computed(() => can('clients.create','clients.manage'))
const canCreateProject = computed(() => can('projects.create','projects.manage'))

// Cards dos indicadores com o novo design
const cards = [
  {
    label: 'Clientes Ativos',
    key: 'clients',
    icon: Users,
    gradient: 'from-blue-500 to-blue-600',
    bgColor: 'bg-blue-50 dark:bg-blue-950/20',
    textColor: 'text-blue-700 dark:text-blue-300',
    link: '/clients',
    description: 'Total de clientes cadastrados'
  },
  {
    label: 'Projetos',
    key: 'projects',
    icon: FolderKanban,
    gradient: 'from-purple-500 to-purple-600',
    bgColor: 'bg-purple-50 dark:bg-purple-950/20',
    textColor: 'text-purple-700 dark:text-purple-300',
    link: '/projects',
    description: 'Projetos em andamento'
  },
  {
    label: 'Tarefas',
    key: 'tasks',
    icon: Briefcase,
    gradient: 'from-emerald-500 to-emerald-600',
    bgColor: 'bg-emerald-50 dark:bg-emerald-950/20',
    textColor: 'text-emerald-700 dark:text-emerald-300',
    link: '/tasks',
    description: 'Tarefas em execução'
  },
  {
    label: 'Tarefas Atrasadas',
    key: 'overdue_tasks',
    icon: AlertCircle,
    gradient: 'from-red-500 to-red-600',
    bgColor: 'bg-red-50 dark:bg-red-950/20',
    textColor: 'text-red-700 dark:text-red-300',
    link: '/tasks?overdue=1',
    description: 'Tarefas que passaram do prazo'
  },
]

// Valores da empresa
const companyValues = [
  {
    icon: Zap,
    title: 'Inovação Contínua',
    description: 'Sempre buscando novas soluções e tecnologias'
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

onMounted(() => {
  const interval = setInterval(() => {
    currentTime.value = new Date()
  }, 1000)
  
  return () => clearInterval(interval)
})
</script>

<template>
  <AppLayout>
    <div class="space-y-8">
      <!-- Header com saudação -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
            Dashboard
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">
            Visão geral do sistema • {{ currentTime.toLocaleDateString('pt-BR') }}
          </p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
          <Clock class="h-4 w-4" />
          {{ currentTime.toLocaleTimeString('pt-BR') }}
        </div>
      </div>

      <!-- Cards dos indicadores principais -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <Link
          v-for="card in cards"
          :key="card.key"
          :href="card.link"
          class="group devnity-card p-6 hover:shadow-lg transition-all duration-200"
        >
          <div class="flex items-center justify-between mb-4">
            <div :class="[
              'p-3 rounded-xl bg-gradient-to-br',
              card.gradient,
              'text-white shadow-lg group-hover:scale-110 transition-transform duration-200'
            ]">
              <component :is="card.icon" class="h-6 w-6" />
            </div>
            <ArrowUpRight class="h-5 w-5 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300 transition-colors" />
          </div>
          
          <div class="space-y-2">
            <div :class="['text-3xl font-bold', card.textColor]">
              {{ props.stats[card.key] ?? 0 }}
            </div>
            <div class="font-medium text-gray-900 dark:text-gray-100">
              {{ card.label }}
            </div>
            <div class="text-sm text-gray-500 dark:text-gray-400">
              {{ card.description }}
            </div>
          </div>
        </Link>
      </div>

      <!-- Seção principal de boas-vindas -->
      <div class="devnity-card p-8">
        <div class="flex flex-col lg:flex-row items-center gap-8">
          <!-- Logo e Brand -->
          <div class="flex-shrink-0">
            <div class="relative">
              <div class="absolute inset-0 devnity-gradient rounded-2xl blur-lg opacity-30"></div>
              <div class="relative bg-white dark:bg-gray-900 p-6 rounded-2xl border border-gray-200 dark:border-gray-700">
                <Code2 class="h-16 w-16 text-blue-600 dark:text-blue-400" />
              </div>
            </div>
          </div>

          <!-- Conteúdo principal -->
          <div class="flex-1 text-center lg:text-left">
            <h2 class="text-3xl font-bold mb-4">
              <span class="devnity-text-gradient">Bem-vindo à Devnity</span>
            </h2>
            <p class="text-lg text-gray-600 dark:text-gray-300 mb-6 max-w-2xl">
              Sua parceira estratégica em soluções tecnológicas personalizadas. 
              Transformamos desafios empresariais em oportunidades de crescimento 
              através da excelência técnica e inovação contínua.
            </p>
            
            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-3 justify-center lg:justify-start">
              <Link 
                v-if="canCreateClient"
                href="/clients/create"
                class="devnity-button-primary flex items-center gap-2"
              >
                <Users class="h-4 w-4" />
                Novo Cliente
              </Link>
              <Link 
                v-if="canCreateProject"
                href="/projects/create"
                class="devnity-button-secondary flex items-center gap-2"
              >
                <FolderKanban class="h-4 w-4" />
                Novo Projeto
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Valores da empresa -->
      <div class="devnity-card p-8">
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-6 text-center">
          Nossos Valores
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div 
            v-for="(value, index) in companyValues"
            :key="index"
            class="text-center group"
          >
            <div class="inline-flex p-4 rounded-full bg-blue-50 dark:bg-blue-950/20 mb-4 group-hover:scale-110 transition-transform duration-200">
              <component :is="value.icon" class="h-8 w-8 text-blue-600 dark:text-blue-400" />
            </div>
            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2">
              {{ value.title }}
            </h4>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ value.description }}
            </p>
          </div>
        </div>
      </div>

      <!-- Atividade recente e Projetos/Tarefas -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Projetos Recentes -->
        <div class="devnity-card p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <FolderKanban class="h-5 w-5 text-gray-600 dark:text-gray-400" />
              <h3 class="font-semibold text-gray-900 dark:text-gray-100">Projetos Recentes</h3>
            </div>
            <Link 
              v-if="can('projects.view','projects.manage')"
              href="/projects" 
              class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
            >
              Ver todos
            </Link>
          </div>
          <div class="space-y-3">
            <div v-if="props.recentProjects && props.recentProjects.length > 0">
              <div 
                v-for="project in props.recentProjects" 
                :key="project.id"
                class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              >
                <div class="flex-shrink-0">
                  <div class="h-2 w-2 bg-blue-500 rounded-full"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ project.name }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ project.client?.name }} • {{ project.status }}
                  </p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-4">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Nenhum projeto cadastrado
              </p>
            </div>
          </div>
        </div>

        <!-- Tarefas Urgentes -->
        <div class="devnity-card p-6">
          <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
              <AlertCircle class="h-5 w-5 text-gray-600 dark:text-gray-400" />
              <h3 class="font-semibold text-gray-900 dark:text-gray-100">Tarefas Urgentes</h3>
            </div>
            <Link 
              v-if="can('tasks.view','tasks.manage')"
              href="/tasks?priority=urgent" 
              class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300"
            >
              Ver todas
            </Link>
          </div>
          <div class="space-y-3">
            <div v-if="props.urgentTasks && props.urgentTasks.length > 0">
              <div 
                v-for="task in props.urgentTasks" 
                :key="task.id"
                class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
              >
                <div class="flex-shrink-0">
                  <div class="h-2 w-2 bg-red-500 rounded-full"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">
                    {{ task.title }}
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ task.project?.name }} • {{ task.due_date ? new Date(task.due_date).toLocaleDateString('pt-BR') : 'Sem prazo' }}
                  </p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-4">
              <p class="text-sm text-gray-500 dark:text-gray-400">
                Nenhuma tarefa urgente
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer inspiracional -->
      <div class="text-center py-8">
        <p class="text-gray-500 dark:text-gray-400 mb-2">
          "Na Devnity, a inovação não é apenas um conceito, mas uma prática diária."
        </p>
        <p class="text-sm text-gray-400 dark:text-gray-500">
          Transformando desafios em soluções • Evoluindo junto com sua empresa
        </p>
      </div>
    </div>
  </AppLayout>
</template>
