<script setup lang="ts">
import { ref, computed, watch, defineEmits } from 'vue'
import { useForm } from '@inertiajs/vue3'

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

watch(
  () => props.client,
  (newVal) => {
    if (newVal) {
      Object.assign(form, newVal)
    }
  },
  { immediate: true }
)

function submit() {
  emit('submit', form)
}
</script>

<template>
  <form @submit.prevent="submit" class="space-y-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium mb-1">Nome *</label>
        <input v-model="form.name" required class="input" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">E-mail *</label>
        <input v-model="form.email" type="email" required class="input" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Contato</label>
        <input v-model="form.contact_name" class="input" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Telefone</label>
        <input v-model="form.phone" class="input" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Status *</label>
        <select v-model="form.status" class="input" required>
          <option value="ativo">Ativo</option>
          <option value="inativo">Inativo</option>
          <option value="potencial">Potencial</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Empresa</label>
        <input v-model="form.company" class="input" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">CPF/CNPJ</label>
        <input v-model="form.document" class="input" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Endereço</label>
        <input v-model="form.address" class="input" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Cidade</label>
        <input v-model="form.city" class="input" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Estado</label>
        <input v-model="form.state" class="input" />
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">País</label>
        <input v-model="form.country" class="input" />
      </div>
    </div>
    <div>
      <label class="block text-sm font-medium mb-1">Notas</label>
      <textarea v-model="form.notes" class="input" rows="2"></textarea>
    </div>
    <div class="flex gap-2 mt-3">
      <button type="submit"
        class="bg-[#6a0dad] hover:bg-[#5a058a] text-white rounded-xl px-6 py-2 font-semibold transition-colors shadow"
        :disabled="processing || form.processing"
      >
        {{ submitLabel || 'Salvar' }}
      </button>
    </div>
  </form>
</template>

<style scoped>
.input {
  @apply w-full rounded-xl border px-3 py-2 bg-white dark:bg-[#232336] dark:text-gray-100 transition focus:ring-2 focus:ring-[#6a0dad];
}
</style>
