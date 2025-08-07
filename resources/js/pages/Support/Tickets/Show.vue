<template>
    <Head :title="`Ticket #${ticket.ticket_number}`" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Ticket #{{ ticket.ticket_number }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ ticket.title }}
                    </p>
                </div>
                <Link
                    :href="route('support.admin')"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-widest text-gray-700 shadow-sm transition duration-150 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 dark:border-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:ring-offset-gray-800"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Voltar
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Conteúdo principal -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Informações do ticket -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                        {{ ticket.title }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Criado por {{ ticket.user.name }} em {{ formatDate(ticket.created_at) }}
                                    </p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span :class="getStatusClass(ticket.status)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ getStatusLabel(ticket.status) }}
                                    </span>
                                    <span :class="getPriorityClass(ticket.priority)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ getPriorityLabel(ticket.priority) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="prose dark:prose-invert max-w-none">
                                <div class="whitespace-pre-wrap">{{ ticket.description }}</div>
                            </div>
                        </div>

                        <!-- Respostas -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Conversação
                                </h3>
                            </div>
                            
                            <div class="p-6 space-y-6">
                                <div v-for="response in ticket.responses" :key="response.id" class="flex space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white text-sm font-medium">
                                            {{ response.user.name.charAt(0).toUpperCase() }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2">
                                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ response.user.name }}
                                            </p>
                                            <span :class="response.is_internal ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'" class="inline-flex px-2 py-0.5 text-xs font-medium rounded">
                                                {{ response.is_internal ? 'Interno' : 'Cliente' }}
                                            </span>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ formatDate(response.created_at) }}
                                            </p>
                                        </div>
                                        <div class="mt-2 prose dark:prose-invert prose-sm max-w-none">
                                            <div class="whitespace-pre-wrap">{{ response.message }}</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nova resposta -->
                                <form @submit.prevent="submitResponse" class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                                    <div class="space-y-4">
                                        <div>
                                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Nova Resposta
                                            </label>
                                            <textarea
                                                id="message"
                                                v-model="responseForm.message"
                                                rows="4"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Digite sua resposta..."
                                                required
                                            ></textarea>
                                        </div>
                                        
                                        <div class="flex items-center justify-between">
                                            <label class="flex items-center">
                                                <input
                                                    v-model="responseForm.is_internal"
                                                    type="checkbox"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:focus:ring-indigo-500"
                                                />
                                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                    Nota interna (não visível para o cliente)
                                                </span>
                                            </label>
                                            
                                            <div class="flex space-x-2">
                                                <button
                                                    type="submit"
                                                    :disabled="responseForm.processing || !responseForm.message.trim()"
                                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                                                >
                                                    <span v-if="responseForm.processing">Enviando...</span>
                                                    <span v-else>Responder</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Status e ações -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                Ações
                            </h3>
                            
                            <div class="space-y-4">
                                <!-- Alterar status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Status
                                    </label>
                                    <select
                                        id="status"
                                        v-model="statusForm.status"
                                        @change="updateStatus"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="open">Aberto</option>
                                        <option value="in_progress">Em Progresso</option>
                                        <option value="pending_customer">Aguardando Cliente</option>
                                        <option value="resolved">Resolvido</option>
                                        <option value="closed">Fechado</option>
                                    </select>
                                </div>

                                <!-- Alterar prioridade -->
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Prioridade
                                    </label>
                                    <select
                                        id="priority"
                                        v-model="priorityForm.priority"
                                        @change="updatePriority"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="low">Baixa</option>
                                        <option value="medium">Média</option>
                                        <option value="high">Alta</option>
                                        <option value="critical">Crítica</option>
                                    </select>
                                </div>

                                <!-- Alterar responsável -->
                                <div>
                                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Responsável
                                    </label>
                                    <select
                                        id="assigned_to"
                                        v-model="assignedForm.assigned_to"
                                        @change="updateAssigned"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="">Não atribuído</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Informações do ticket -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                                Detalhes
                            </h3>
                            
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Número</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">#{{ ticket.ticket_number }}</dd>
                                </div>
                                
                                <div v-if="ticket.category">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Categoria</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">
                                        <span :style="{ backgroundColor: ticket.category.color + '20', color: ticket.category.color }" class="inline-flex px-2 py-1 text-xs font-medium rounded">
                                            {{ ticket.category.name }}
                                        </span>
                                    </dd>
                                </div>
                                
                                <div v-if="ticket.client">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Cliente</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ ticket.client.name }}</dd>
                                </div>
                                
                                <div v-if="ticket.assigned_to">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Responsável</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ ticket.assigned_to.name }}</dd>
                                </div>
                                
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Criado em</dt>
                                    <dd class="text-sm text-gray-900 dark:text-gray-100">{{ formatDate(ticket.created_at) }}</dd>
                                </div>
                                
                                <div v-if="ticket.sla_due_at">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">SLA</dt>
                                    <dd class="text-sm" :class="isOverdue(ticket.sla_due_at) ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100'">
                                        {{ formatDate(ticket.sla_due_at) }}
                                        <span v-if="isOverdue(ticket.sla_due_at)" class="block text-xs text-red-500">Em atraso</span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    ticket: Object,
    users: Array
})

const responseForm = useForm({
    message: '',
    is_internal: false
})

const statusForm = useForm({
    status: props.ticket.status
})

const priorityForm = useForm({
    priority: props.ticket.priority
})

const assignedForm = useForm({
    assigned_to: props.ticket.assigned_to?.id || ''
})

const submitResponse = () => {
    responseForm.post(route('support.tickets.responses.store', props.ticket.id), {
        onSuccess: () => {
            responseForm.reset()
        }
    })
}

const updateStatus = () => {
    statusForm.patch(route('support.tickets.update', props.ticket.id), {
        preserveScroll: true
    })
}

const updatePriority = () => {
    priorityForm.patch(route('support.tickets.update', props.ticket.id), {
        preserveScroll: true
    })
}

const updateAssigned = () => {
    assignedForm.patch(route('support.tickets.update', props.ticket.id), {
        preserveScroll: true
    })
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

const isOverdue = (date) => {
    return new Date(date) < new Date()
}
</script>
