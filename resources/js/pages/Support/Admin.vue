<template>
    <Head title="Sistema de Suporte" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Sistema de Suporte
                </h2>
                <div class="flex gap-2">
                    <Link
                        :href="route('support.categories.index')"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 dark:border-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                        Categorias
                    </Link>
                    <Link
                        :href="route('support.tickets.create')"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white shadow-sm transition duration-150 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Novo Ticket
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Estatísticas -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-blue-600 dark:text-blue-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125V15.75c0-.621-.504-1.125-1.125-1.125H20.25M3.375 5.25h17.25c.621 0 1.125.504 1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ stats.total }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Total de Tickets
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-green-600 dark:text-green-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ stats.resolved }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Resolvidos
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-yellow-600 dark:text-yellow-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ stats.pending }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Pendentes
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-red-600 dark:text-red-300">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                    {{ stats.overdue }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Em Atraso
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros e Busca -->
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Buscar
                            </label>
                            <input
                                v-model="filters.search"
                                @input="applyFilters"
                                type="text"
                                placeholder="Buscar por título ou ID..."
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                            >
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Status
                            </label>
                            <select
                                v-model="filters.status"
                                @change="applyFilters"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Todos</option>
                                <option value="open">Aberto</option>
                                <option value="in_progress">Em Progresso</option>
                                <option value="pending_customer">Aguardando Cliente</option>
                                <option value="resolved">Resolvido</option>
                                <option value="closed">Fechado</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Prioridade
                            </label>
                            <select
                                v-model="filters.priority"
                                @change="applyFilters"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Todas</option>
                                <option value="low">Baixa</option>
                                <option value="medium">Média</option>
                                <option value="high">Alta</option>
                                <option value="critical">Crítica</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Categoria
                            </label>
                            <select
                                v-model="filters.category"
                                @change="applyFilters"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                            >
                                <option value="">Todas</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button
                                @click="clearFilters"
                                class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 dark:bg-gray-600 dark:text-gray-300 dark:hover:bg-gray-500"
                            >
                                Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lista de Tickets -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Ticket
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Título
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Prioridade
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Categoria
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Criado em
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Ações
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="ticket in tickets.data" :key="ticket.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                        #{{ ticket.ticket_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ ticket.title }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                Por {{ ticket.user.name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusClass(ticket.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ getStatusLabel(ticket.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getPriorityClass(ticket.priority)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ getPriorityLabel(ticket.priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <span v-if="ticket.category" :style="{ backgroundColor: ticket.category.color + '20', color: ticket.category.color }" class="inline-flex px-2 py-1 text-xs font-medium rounded">
                                            {{ ticket.category.name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(ticket.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="route('support.tickets.show', ticket.id)"
                                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3"
                                        >
                                            Ver
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginação -->
                    <div v-if="tickets.links" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        <nav class="flex items-center justify-between">
                            <div class="hidden sm:block">
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Mostrando
                                    <span class="font-medium">{{ tickets.from }}</span>
                                    até
                                    <span class="font-medium">{{ tickets.to }}</span>
                                    de
                                    <span class="font-medium">{{ tickets.total }}</span>
                                    resultados
                                </p>
                            </div>
                            <div class="flex space-x-2">
                                <Link
                                    v-for="link in tickets.links"
                                    :key="link.label"
                                    :href="link.url"
                                    v-html="link.label"
                                    :class="[
                                        'px-3 py-2 text-sm font-medium rounded-md',
                                        link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-300 dark:hover:bg-gray-700',
                                        !link.url && 'opacity-50 cursor-not-allowed'
                                    ]"
                                />
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AppLayout.vue'
import { debounce } from 'lodash'

const props = defineProps({
    tickets: Object,
    categories: Array,
    stats: Object,
    filters: Object
})

const filters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    priority: props.filters?.priority || '',
    category: props.filters?.category || ''
})

const applyFilters = debounce(() => {
    router.get(route('support.admin'), filters, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}, 300)

const clearFilters = () => {
    Object.keys(filters).forEach(key => filters[key] = '')
    router.get(route('support.admin'))
}

const getStatusClass = (status) => {
    const classes = {
        open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
        pending_customer: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        resolved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
        closed: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStatusLabel = (status) => {
    const labels = {
        open: 'Aberto',
        in_progress: 'Em Progresso',
        pending_customer: 'Aguardando Cliente',
        resolved: 'Resolvido',
        closed: 'Fechado'
    }
    return labels[status] || status
}

const getPriorityClass = (priority) => {
    const classes = {
        low: 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        medium: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
        high: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
        critical: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    }
    return classes[priority] || 'bg-gray-100 text-gray-800'
}

const getPriorityLabel = (priority) => {
    const labels = {
        low: 'Baixa',
        medium: 'Média',
        high: 'Alta',
        critical: 'Crítica'
    }
    return labels[priority] || priority
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>
