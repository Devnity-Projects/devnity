<template>
    <Head title="Esqueci Minha Senha" />
    
    <AuthLayout>
        <div class="mb-4 text-sm text-gray-600">
            Esqueceu sua senha? Sem problemas. Informe o seu endereço de e-mail e enviaremos um link para redefinir sua senha.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">E-mail</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    required
                    autofocus
                    autocomplete="username"
                />
                <div v-if="form.errors.email" class="mt-2 text-sm text-red-600">{{ form.errors.email }}</div>
            </div>

            <div class="flex items-center justify-end mt-4">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="ml-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Enviar Link de Redefinição
                </button>
            </div>
        </form>
    </AuthLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { Head } from '@inertiajs/vue3'
import AuthLayout from '@/layouts/AuthLayout.vue'

defineProps({
    status: String,
})

const form = useForm({
    email: '',
})

const submit = () => {
    form.post(route('password.email'))
}
</script>
