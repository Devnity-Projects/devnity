<script setup>
import { Link, router } from '@inertiajs/vue3'

const menus = [
  { name: 'Dashboard', icon: '🏠', href: '/dashboard' },
  { name: 'Clientes', icon: '👥', href: '/clients' },
  { name: 'Projetos', icon: '🗂️', href: '/projects' },
  { name: 'Tarefas', icon: '📝', href: '/tasks' },
  { name: 'Chamados', icon: '🆘', href: '/tickets' },
]
</script>

<template>
  <div class="min-h-screen bg-[#f3f0ff] flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow flex justify-between items-center px-6 py-3 border-b">
      <div class="flex items-center gap-3">
        <img src="/images/logo-devnity.png" alt="Devnity" class="w-10 h-10" />
        <Link href="/dashboard" class="text-[#6a0dad] text-2xl font-extrabold tracking-tight hover:underline">
          Devnity
        </Link>
      </div>
      <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500 font-semibold">Olá, Usuário</span>
        <button
          @click="router.visit('/')"
          class="text-[#e55e55] px-3 py-1 rounded-xl font-bold hover:bg-[#ffeaea] transition"
        >Sair</button>
      </div>
    </header>
    <!-- Menu horizontal -->
    <nav class="bg-[#6a0dad] px-4 flex gap-2 py-2 shadow text-white text-base font-semibold">
      <template v-for="item in menus" :key="item.href">
        <Link
          :href="item.href"
          class="flex items-center gap-2 px-4 py-2 rounded-2xl transition hover:bg-[#5a089e] hover:text-gray-100"
          active-class="bg-white text-[#6a0dad]"
        >
          <span>{{ item.icon }}</span>
          <span>{{ item.name }}</span>
        </Link>
      </template>
    </nav>
    <!-- Conteúdo -->
    <main class="flex-1 p-6 max-w-7xl w-full mx-auto">
      <slot />
    </main>
  </div>
</template>
