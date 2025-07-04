<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue'
const props = defineProps<{ stats: Record<string, number> }>()

const cards = [
  {
    label: 'Clientes',
    key: 'clients',
    icon: '👥',
    color: 'bg-[#ede1f9] dark:bg-[#251c37]',
    text: 'text-[#6a0dad] dark:text-[#b59cff]',
    link: '/clients',
  },
  {
    label: 'Projetos',
    key: 'projects',
    icon: '🗂️',
    color: 'bg-[#f9e5e5] dark:bg-[#2c1d23]',
    text: 'text-[#e55e55]',
    link: '/projects',
  },
  {
    label: 'Tarefas',
    key: 'tasks',
    icon: '📝',
    color: 'bg-[#e3ffe9] dark:bg-[#1d2c23]',
    text: 'text-green-700 dark:text-green-400',
    link: '/tasks',
  },
  {
    label: 'Chamados',
    key: 'tickets',
    icon: '🆘',
    color: 'bg-[#fff5db] dark:bg-[#332a1a]',
    text: 'text-orange-600 dark:text-orange-400',
    link: '/tickets',
  },
]
</script>

<template>
  <AppLayout>
    <div>
      <h1 class="text-3xl font-bold mb-6 text-[#6a0dad] dark:text-[#b59cff]">Dashboard</h1>
      <!-- Cards dos indicadores -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <a
          v-for="card in cards"
          :key="card.key"
          :href="card.link"
          :class="[card.color, 'rounded-2xl shadow flex items-center gap-4 p-6 hover:scale-[1.03] transition cursor-pointer']"
        >
          <div class="text-4xl">{{ card.icon }}</div>
          <div>
            <div :class="['text-3xl font-extrabold', card.text]">{{ props.stats[card.key] ?? 0 }}</div>
            <div class="text-gray-500 dark:text-gray-300 font-semibold">{{ card.label }}</div>
          </div>
        </a>
      </div>
      <!-- Seção de boas-vindas -->
      <div class="bg-white dark:bg-[#232336] rounded-2xl shadow p-8 flex flex-col md:flex-row items-center gap-6 mb-6">
        <img src="/images/logo-devnity-bg.png" class="w-24 h-24 rounded-2xl shadow-lg" alt="Devnity" />
        <div>
          <div class="text-2xl font-bold text-[#6a0dad] dark:text-[#b59cff]">Bem-vindo ao Devnity 👋</div>
          <div class="text-gray-500 dark:text-gray-300 mt-2 max-w-lg">
            Este é seu sistema central de gestão!<br>
            Use o menu acima para acessar clientes, projetos, tarefas e chamados.<br>
            Todos os seus dados ficam organizados, visíveis e prontos para a sua equipe.
          </div>
        </div>
      </div>
      <!-- Espaço para próximos gráficos ou novidades -->
      <div class="text-center text-gray-400 dark:text-gray-500 text-sm mt-12">
        Mais novidades em breve.<br>
        <span class="text-[#6a0dad] dark:text-[#b59cff]">Devnity</span> — evoluindo junto com sua empresa!
      </div>
    </div>
  </AppLayout>
</template>
