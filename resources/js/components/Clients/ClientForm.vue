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
  name: props.client?.name ?? '',
  type: props.client?.type ?? 'Pessoa Física',
  document: props.client?.document ?? '',
  email: props.client?.email ?? '',
  phone: props.client?.phone ?? '',
  responsible: props.client?.responsible ?? '',
  responsible_email: props.client?.responsible_email ?? '',
  responsible_phone: props.client?.responsible_phone ?? '',
  state_registration: props.client?.state_registration ?? '',
  municipal_registration: props.client?.municipal_registration ?? '',
  zip_code: props.client?.zip_code ?? '',
  address: props.client?.address ?? '',
  number: props.client?.number ?? '',
  complement: props.client?.complement ?? '',
  neighborhood: props.client?.neighborhood ?? '',
  city: props.client?.city ?? '',
  state: props.client?.state ?? '',
  country: props.client?.country ?? 'Brasil',
  status: props.client?.status ?? 'ativo',
  notes: props.client?.notes ?? '',
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
      <!-- Obrigatórios -->
      <div>
        <label class="block text-sm font-medium mb-1">Nome/Razão Social *</label>
        <BaseInput v-model="form.name" required maxlength="255" placeholder="Nome completo ou razão social" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Tipo *</label>
        <select v-model="form.type"
          class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-[#232336] dark:text-gray-100 transition focus:ring-2 focus:ring-[#6a0dad] outline-none"
          required
        >
          <option value="Pessoa Física">Pessoa Física</option>
          <option value="Pessoa Jurídica">Pessoa Jurídica</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">CPF/CNPJ *</label>
        <BaseInput v-model="form.document" required maxlength="20" placeholder="CPF ou CNPJ" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Status *</label>
        <select v-model="form.status"
          class="w-full rounded-lg border px-3 py-2 bg-white dark:bg-[#232336] dark:text-gray-100 transition focus:ring-2 focus:ring-[#6a0dad] outline-none"
          required
        >
          <option value="ativo">Ativo</option>
          <option value="inativo">Inativo</option>
        </select>
      </div>
      <!-- Contato -->
      <div>
        <label class="block text-sm font-medium mb-1">E-mail</label>
        <BaseInput v-model="form.email" type="email" maxlength="255" placeholder="E-mail" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Telefone</label>
        <BaseInput v-model="form.phone" maxlength="30" placeholder="Telefone" />
      </div>
      <!-- Responsável e Inscrições (PJ) -->
      <div>
        <label class="block text-sm font-medium mb-1">Responsável</label>
        <BaseInput v-model="form.responsible" maxlength="255" placeholder="Responsável legal ou contato" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">E-mail do responsável</label>
        <BaseInput v-model="form.responsible_email" type="email" maxlength="255" placeholder="E-mail do responsável" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Telefone do responsável</label>
        <BaseInput v-model="form.responsible_phone" maxlength="30" placeholder="Telefone do responsável" />
      </div>
      <div v-if="form.type === 'Pessoa Jurídica'">
        <label class="block text-sm font-medium mb-1">Inscrição Estadual</label>
        <BaseInput v-model="form.state_registration" maxlength="50" placeholder="Inscrição Estadual" />
      </div>
      <div v-if="form.type === 'Pessoa Jurídica'">
        <label class="block text-sm font-medium mb-1">Inscrição Municipal</label>
        <BaseInput v-model="form.municipal_registration" maxlength="50" placeholder="Inscrição Municipal" />
      </div>
      <!-- Endereço completo -->
      <div>
        <label class="block text-sm font-medium mb-1">CEP</label>
        <BaseInput v-model="form.zip_code" maxlength="10" placeholder="CEP" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Endereço</label>
        <BaseInput v-model="form.address" maxlength="255" placeholder="Endereço" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Número</label>
        <BaseInput v-model="form.number" maxlength="10" placeholder="Número" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Complemento</label>
        <BaseInput v-model="form.complement" maxlength="255" placeholder="Complemento" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Bairro</label>
        <BaseInput v-model="form.neighborhood" maxlength="100" placeholder="Bairro" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Cidade</label>
        <BaseInput v-model="form.city" maxlength="100" placeholder="Cidade" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Estado</label>
        <BaseInput v-model="form.state" maxlength="2" placeholder="UF" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">País</label>
        <BaseInput v-model="form.country" maxlength="100" placeholder="País" />
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
