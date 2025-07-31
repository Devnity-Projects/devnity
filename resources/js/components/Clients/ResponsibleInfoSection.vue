<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
      <div class="w-2 h-2 rounded-full bg-purple-500"></div>
      Pessoa Responsável
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Nome do Responsável
        </label>
        <input
          v-model="form.responsible"
          type="text"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="Nome do responsável"
        />
        <div v-if="form.errors.responsible" class="text-red-500 text-sm mt-1">{{ form.errors.responsible }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          E-mail do Responsável
        </label>
        <input
          v-model="form.responsible_email"
          type="email"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="email@exemplo.com"
        />
        <div v-if="form.errors.responsible_email" class="text-red-500 text-sm mt-1">{{ form.errors.responsible_email }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Telefone do Responsável
        </label>
        <input
          v-model="localResponsiblePhone"
          @input="handleResponsiblePhoneInput"
          type="text"
          maxlength="15"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="(11) 99999-9999"
        />
        <div v-if="form.errors.responsible_phone" class="text-red-500 text-sm mt-1">{{ form.errors.responsible_phone }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

interface Form {
  responsible: string
  responsible_email: string
  responsible_phone: string
  errors: Record<string, string>
}

interface Props {
  form: Form
}

const props = defineProps<Props>()

const localResponsiblePhone = ref(props.form.responsible_phone || '')

// Watch for changes in form.responsible_phone and update local value
watch(() => props.form.responsible_phone, (newValue) => {
  localResponsiblePhone.value = newValue || ''
})

function handleResponsiblePhoneInput(event: Event) {
  const target = event.target as HTMLInputElement
  let value = target.value.replace(/\D/g, '')
  
  if (value.length >= 11) {
    // Celular: (00) 90000-0000
    value = value.slice(0, 11)
    localResponsiblePhone.value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`
  } else if (value.length >= 10) {
    // Fixo: (00) 0000-0000
    value = value.slice(0, 10)
    localResponsiblePhone.value = `(${value.slice(0, 2)}) ${value.slice(2, 6)}-${value.slice(6)}`
  } else if (value.length >= 6) {
    localResponsiblePhone.value = `(${value.slice(0, 2)}) ${value.slice(2)}`
  } else if (value.length >= 2) {
    localResponsiblePhone.value = `(${value.slice(0, 2)}) ${value.slice(2)}`
  } else {
    localResponsiblePhone.value = value
  }
  
  props.form.responsible_phone = value
}
</script>