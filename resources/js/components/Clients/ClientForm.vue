<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import BaseInput from '@/components/BaseInput.vue'
import BaseTextarea from '@/components/BaseTextarea.vue'

const props = defineProps<{
  client?: any
  submitLabel?: string
  processing?: boolean
}>()
const emit = defineEmits(['submit'])

const form = useForm({
  name: props.client?.name || '',
  contact_name: props.client?.contact_name || '',
  email: props.client?.email || '',
  phone: props.client?.phone || '',
  status: props.client?.status || 'ativo',
  company: props.client?.company || '',
  document: props.client?.document || '',
  address: props.client?.address || '',
  city: props.client?.city || '',
  state: props.client?.state || '',
  country: props.client?.country || '',
  notes: props.client?.notes || '',
})

function submit() {
  if (props.client) {
    form.put(route('clients.update', props.client.id))
  } else {
    form.post(route('clients.store'))
  }
}

</script>

<template>
  <form @submit.prevent="submit" class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Nome *</label>
        <BaseInput v-model="form.name" required placeholder="Nome do cliente" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">E-mail *</label>
        <BaseInput v-model="form.email" required type="email" placeholder="E-mail" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Contato</label>
        <BaseInput v-model="form.contact_name" placeholder="Nome do contato" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Telefone</label>
        <BaseInput v-model="form.phone" placeholder="Telefone" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Status *</label>
        <select v-model="form.status"
          class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-[#232336] dark:text-gray-100 transition focus:ring-2 focus:ring-[#6a0dad] outline-none"
          required
        >
          <option value="ativo">Ativo</option>
          <option value="inativo">Inativo</option>
          <option value="potencial">Potencial</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Empresa</label>
        <BaseInput v-model="form.company" placeholder="Empresa" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">CPF/CNPJ</label>
        <BaseInput v-model="form.document" placeholder="CPF ou CNPJ" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Endereço</label>
        <BaseInput v-model="form.address" placeholder="Endereço" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Cidade</label>
        <BaseInput v-model="form.city" placeholder="Cidade" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Estado</label>
        <BaseInput v-model="form.state" placeholder="Estado" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">País</label>
        <BaseInput v-model="form.country" placeholder="País" />
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Notas</label>
      <BaseTextarea v-model="form.notes" rows="2" placeholder="Observações adicionais" />
    </div>
    <div class="flex gap-2 mt-3">
      <button type="submit"
        class="bg-[#6a0dad] hover:bg-[#5a058a] text-white rounded-lg px-6 py-2 font-semibold transition-colors shadow"
        :disabled="processing || form.processing"
      >
        {{ submitLabel || 'Salvar' }}
      </button>
    </div>
  </form>
</template>
