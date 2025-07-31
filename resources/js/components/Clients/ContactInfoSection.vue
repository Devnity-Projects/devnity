<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
      <div class="w-2 h-2 rounded-full bg-green-500"></div>
      Informações de Contato
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          E-mail
        </label>
        <input
          v-model="form.email"
          type="email"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="email@exemplo.com"
        />
        <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Telefone
        </label>
        <input
          v-model="localPhone"
          @input="handlePhoneInput"
          type="text"
          maxlength="15"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="(11) 99999-9999"
        />
        <div v-if="form.errors.phone" class="text-red-500 text-sm mt-1">{{ form.errors.phone }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'

interface Form {
  email: string
  phone: string
  errors: Record<string, string>
}

interface Props {
  form: Form
}

const props = defineProps<Props>()

const localPhone = ref(props.form.phone || '')

// Watch for changes in form.phone and update local value
watch(() => props.form.phone, (newValue) => {
  localPhone.value = newValue || ''
})

function handlePhoneInput(event: Event) {
  const target = event.target as HTMLInputElement
  let value = target.value.replace(/\D/g, '')
  
  if (value.length >= 11) {
    // Celular: (00) 90000-0000
    value = value.slice(0, 11)
    localPhone.value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`
  } else if (value.length >= 10) {
    // Fixo: (00) 0000-0000
    value = value.slice(0, 10)
    localPhone.value = `(${value.slice(0, 2)}) ${value.slice(2, 6)}-${value.slice(6)}`
  } else if (value.length >= 6) {
    localPhone.value = `(${value.slice(0, 2)}) ${value.slice(2)}`
  } else if (value.length >= 2) {
    localPhone.value = `(${value.slice(0, 2)}) ${value.slice(2)}`
  } else {
    localPhone.value = value
  }
  
  props.form.phone = value
}
</script>