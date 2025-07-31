<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
      <div class="w-2 h-2 rounded-full bg-blue-500"></div>
      Dados Principais
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Nome/Razão Social *
        </label>
        <input
          v-model="form.name"
          type="text"
          required
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="Nome completo ou razão social"
        />
        <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Tipo *
        </label>
        <select
          v-model="form.type"
          required
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
        >
          <option value="Pessoa Física">Pessoa Física</option>
          <option value="Pessoa Jurídica">Pessoa Jurídica</option>
        </select>
        <div v-if="form.errors.type" class="text-red-500 text-sm mt-1">{{ form.errors.type }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          {{ form.type === 'Pessoa Física' ? 'CPF' : 'CNPJ' }} *
        </label>
        <div class="relative">
          <input
            v-model="localDocument"
            @input="handleDocumentInput"
            @blur="validateDocument"
            type="text"
            required
            :maxlength="form.type === 'Pessoa Física' ? 14 : 18"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            :class="{
              'border-red-500 focus:ring-red-500': documentValidation.isInvalid,
              'border-green-500 focus:ring-green-500': documentValidation.isValid
            }"
            :placeholder="form.type === 'Pessoa Física' ? '000.000.000-00' : '00.000.000/0000-00'"
          />
          <div v-if="documentValidation.isValid" class="absolute right-3 top-1/2 transform -translate-y-1/2">
            <div class="w-5 h-5 bg-green-500 rounded-full flex items-center justify-center">
              <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div v-else-if="documentValidation.isInvalid" class="absolute right-3 top-1/2 transform -translate-y-1/2">
            <div class="w-5 h-5 bg-red-500 rounded-full flex items-center justify-center">
              <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
        </div>
        <div v-if="form.errors.document" class="text-red-500 text-sm mt-1">{{ form.errors.document }}</div>
        <div v-else-if="documentValidation.message" class="text-green-500 text-sm mt-1">{{ documentValidation.message }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Status *
        </label>
        <select
          v-model="form.status"
          required
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
        >
          <option value="ativo">Ativo</option>
          <option value="inativo">Inativo</option>
        </select>
        <div v-if="form.errors.status" class="text-red-500 text-sm mt-1">{{ form.errors.status }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue'

interface Form {
  name: string
  type: string
  document: string
  status: string
  errors: Record<string, string>
}

interface Props {
  form: Form
}

const props = defineProps<Props>()

const localDocument = ref(props.form.document || '')
const documentValidation = ref({
  isValid: false,
  isInvalid: false,
  message: ''
})

// Watch for changes in form.document and update local value
watch(() => props.form.document, (newValue) => {
  localDocument.value = newValue || ''
})

// Watch for changes in form.type to update placeholder and validation
watch(() => props.form.type, () => {
  localDocument.value = ''
  props.form.document = ''
  documentValidation.value = { isValid: false, isInvalid: false, message: '' }
})

function handleDocumentInput(event: Event) {
  const target = event.target as HTMLInputElement
  let value = target.value.replace(/\D/g, '')
  
  if (props.form.type === 'Pessoa Física') {
    // CPF: 000.000.000-00
    if (value.length > 3) value = value.slice(0, 3) + '.' + value.slice(3)
    if (value.length > 7) value = value.slice(0, 7) + '.' + value.slice(7)
    if (value.length > 11) value = value.slice(0, 11) + '-' + value.slice(11, 13)
  } else {
    // CNPJ: 00.000.000/0000-00
    if (value.length > 2) value = value.slice(0, 2) + '.' + value.slice(2)
    if (value.length > 6) value = value.slice(0, 6) + '.' + value.slice(6)
    if (value.length > 10) value = value.slice(0, 10) + '/' + value.slice(10)
    if (value.length > 15) value = value.slice(0, 15) + '-' + value.slice(15, 17)
  }
  
  localDocument.value = value
  props.form.document = value.replace(/\D/g, '')
}

function validateDocument() {
  const cleanDoc = localDocument.value.replace(/\D/g, '')
  
  if (!cleanDoc) {
    documentValidation.value = { isValid: false, isInvalid: false, message: '' }
    return
  }
  
  let isValid = false
  
  if (props.form.type === 'Pessoa Física' && cleanDoc.length === 11) {
    isValid = validateCPF(cleanDoc)
  } else if (props.form.type === 'Pessoa Jurídica' && cleanDoc.length === 14) {
    isValid = validateCNPJ(cleanDoc)
  }
  
  documentValidation.value = {
    isValid,
    isInvalid: !isValid && cleanDoc.length > 0,
    message: isValid ? 'Documento válido' : ''
  }
}

function validateCPF(cpf: string): boolean {
  if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
    return false
  }

  let sum = 0
  let remainder

  for (let i = 1; i <= 9; i++) {
    sum += parseInt(cpf.substring(i - 1, i)) * (11 - i)
  }

  remainder = (sum * 10) % 11

  if ((remainder === 10) || (remainder === 11)) remainder = 0
  if (remainder !== parseInt(cpf.substring(9, 10))) return false

  sum = 0
  for (let i = 1; i <= 10; i++) {
    sum += parseInt(cpf.substring(i - 1, i)) * (12 - i)
  }

  remainder = (sum * 10) % 11

  if ((remainder === 10) || (remainder === 11)) remainder = 0
  if (remainder !== parseInt(cpf.substring(10, 11))) return false

  return true
}

function validateCNPJ(cnpj: string): boolean {
  if (cnpj.length !== 14) return false

  const weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]
  const weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]

  let sum = 0
  for (let i = 0; i < 12; i++) {
    sum += parseInt(cnpj[i]) * weights1[i]
  }
  
  const digit1 = sum % 11 < 2 ? 0 : 11 - (sum % 11)

  sum = 0
  for (let i = 0; i < 13; i++) {
    sum += parseInt(cnpj[i]) * weights2[i]
  }
  
  const digit2 = sum % 11 < 2 ? 0 : 11 - (sum % 11)

  return parseInt(cnpj[12]) === digit1 && parseInt(cnpj[13]) === digit2
}
</script>
