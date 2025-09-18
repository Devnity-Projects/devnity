<template>
    <Head title="Novo Ticket" />
    
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Novo Ticket de Suporte
                </h2>
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
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Coluna da esquerda -->
                            <div class="space-y-6">
                                <!-- Título -->
                                <div>
                                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Título *
                                    </label>
                                    <input
                                        id="title"
                                        v-model="form.title"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.title }"
                                        placeholder="Ex: Problema com login no sistema"
                                        required
                                    />
                                    <p v-if="form.errors.title" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.title }}
                                    </p>
                                </div>

                                <!-- Categoria -->
                                <div>
                                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Categoria *
                                    </label>
                                    <select
                                        id="category"
                                        v-model="form.support_category_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.support_category_id }"
                                        required
                                    >
                                        <option value="">Selecione uma categoria</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.support_category_id" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.support_category_id }}
                                    </p>
                                </div>

                                <!-- Prioridade -->
                                <div>
                                    <label for="priority" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Prioridade *
                                    </label>
                                    <select
                                        id="priority"
                                        v-model="form.priority"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.priority }"
                                        required
                                    >
                                        <option value="">Selecione uma prioridade</option>
                                        <option value="low">Baixa</option>
                                        <option value="medium">Média</option>
                                        <option value="high">Alta</option>
                                        <option value="critical">Crítica</option>
                                    </select>
                                    <p v-if="form.errors.priority" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ form.errors.priority }}
                                    </p>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ getPriorityDescription(form.priority) }}
                                    </p>
                                </div>

                                <!-- Cliente (opcional) -->
                                <div>
                                    <label for="client" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Cliente
                                    </label>
                                    <select
                                        id="client"
                                        v-model="form.client_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="">Nenhum cliente específico</option>
                                        <option v-for="client in clients" :key="client.id" :value="client.id">
                                            {{ client.name }}
                                        </option>
                                    </select>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Relacione este ticket com um cliente específico (opcional)
                                    </p>
                                </div>
                            </div>

                            <!-- Coluna da direita -->
                            <div class="space-y-6">
                                <!-- Responsável -->
                                <div>
                                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Responsável
                                    </label>
                                    <select
                                        id="assigned_to"
                                        v-model="form.assigned_to"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="">Atribuir automaticamente</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Deixe em branco para atribuição automática
                                    </p>
                                </div>

                                <!-- SLA -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        SLA Personalizado
                                    </label>
                                    <div class="flex items-center space-x-3">
                                        <label class="flex items-center">
                                            <input
                                                v-model="useCustomSLA"
                                                type="checkbox"
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:focus:ring-indigo-500"
                                            />
                                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                                Definir prazo personalizado
                                            </span>
                                        </label>
                                    </div>
                                    <div v-if="useCustomSLA" class="mt-3">
                                        <input
                                            v-model="form.sla_due_at"
                                            type="datetime-local"
                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Se não definir, será calculado automaticamente baseado na prioridade
                                    </p>
                                </div>

                                <!-- Informações adicionais -->
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                                    <div class="flex">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-blue-400 mr-2 mt-0.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                        </svg>
                                        <div class="text-sm text-blue-800 dark:text-blue-200">
                                            <h4 class="font-medium">Dica:</h4>
                                            <ul class="mt-1 list-disc list-inside space-y-1">
                                                <li>Seja específico no título</li>
                                                <li>Escolha a prioridade adequada</li>
                                                <li>Forneça o máximo de detalhes na descrição</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Descrição (span full width) -->
                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Descrição *
                            </label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="8"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:focus:border-indigo-500 dark:focus:ring-indigo-500 sm:text-sm"
                                :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.description }"
                                placeholder="Descreva detalhadamente o problema ou solicitação. Inclua passos para reproduzir, mensagens de erro, capturas de tela, etc."
                                required
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ form.errors.description }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                Quanto mais detalhes, melhor poderemos ajudá-lo. Mínimo 20 caracteres.
                            </p>
                        </div>

                        <!-- Botões -->
                        <div class="mt-8 flex justify-end space-x-3">
                            <Link
                                :href="route('support.admin')"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:border-gray-500 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            >
                                Cancelar
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing || !canSubmit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span v-if="form.processing">Criando...</span>
                                <span v-else>Criar Ticket</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    categories: Array,
    clients: Array,
    users: Array
})

const useCustomSLA = ref(false)

const form = useForm({
    title: '',
    description: '',
    support_category_id: '',
    priority: 'medium',
    client_id: '',
    assigned_to: '',
    sla_due_at: ''
})

const canSubmit = computed(() => {
    return form.title.trim() && 
           form.description.trim().length >= 20 && 
           form.support_category_id && 
           form.priority
})

const getPriorityDescription = (priority) => {
    const descriptions = {
        low: 'Questões não críticas que podem ser resolvidas em alguns dias',
        medium: 'Problemas importantes que afetam o funcionamento normal',
        high: 'Problemas críticos que impedem operações importantes',
        critical: 'Emergências que requerem ação imediata'
    }
    return descriptions[priority] || ''
}

const submit = () => {
    // Se não estiver usando SLA personalizado, remover o campo
    if (!useCustomSLA.value) {
        form.sla_due_at = ''
    }
    
    form.post(route('support.tickets.store'), {
        onSuccess: () => {
            form.reset()
            useCustomSLA.value = false
        }
    })
}
</script>
