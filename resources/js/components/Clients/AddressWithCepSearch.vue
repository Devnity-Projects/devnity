<template>
  <div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          CEP
        </label>
        <div class="relative">
          <input
            v-model="localZipCode"
            @blur="searchCep"
            @input="handleZipCodeInput"
            type="text"
            maxlength="9"
            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
            placeholder="00000-000"
          />
          <div v-if="isSearching" class="absolute right-3 top-1/2 transform -translate-y-1/2">
            <div class="animate-spin h-4 w-4 border-2 border-blue-500 border-t-transparent rounded-full"></div>
          </div>
        </div>
        <div v-if="form.errors.zip_code" class="text-red-500 text-sm mt-1">{{ form.errors.zip_code }}</div>
      </div>

      <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Endereço
        </label>
        <input
          v-model="form.address"
          type="text"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="Rua, avenida, etc."
        />
        <div v-if="form.errors.address" class="text-red-500 text-sm mt-1">{{ form.errors.address }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Número
        </label>
        <input
          v-model="form.number"
          type="text"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="123"
        />
        <div v-if="form.errors.number" class="text-red-500 text-sm mt-1">{{ form.errors.number }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Complemento
        </label>
        <input
          v-model="form.complement"
          type="text"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="Apt, sala, etc."
        />
        <div v-if="form.errors.complement" class="text-red-500 text-sm mt-1">{{ form.errors.complement }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Bairro
        </label>
        <input
          v-model="form.neighborhood"
          type="text"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="Nome do bairro"
        />
        <div v-if="form.errors.neighborhood" class="text-red-500 text-sm mt-1">{{ form.errors.neighborhood }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Cidade
        </label>
        <input
          v-model="form.city"
          type="text"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="Nome da cidade"
        />
        <div v-if="form.errors.city" class="text-red-500 text-sm mt-1">{{ form.errors.city }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Estado
        </label>
        <input
          v-model="form.state"
          type="text"
          maxlength="2"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="SP"
        />
        <div v-if="form.errors.state" class="text-red-500 text-sm mt-1">{{ form.errors.state }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import axios from 'axios'

interface Form {
  zip_code: string
  address: string
  number: string
  complement: string
  neighborhood: string
  city: string
  state: string
  errors: Record<string, string>
}

interface Props {
  form: Form
}

const props = defineProps<Props>()

const localZipCode = ref(props.form.zip_code || '')
const isSearching = ref(false)

// Watch for changes in the form zip_code and update local value
watch(() => props.form.zip_code, (newValue) => {
  localZipCode.value = newValue || ''
})

// Update form zip_code when local value changes
watch(localZipCode, (newValue) => {
  props.form.zip_code = newValue
})

function handleZipCodeInput(event: Event) {
  const target = event.target as HTMLInputElement
  let value = target.value.replace(/\D/g, '')
  
  if (value.length > 5) {
    value = value.slice(0, 5) + '-' + value.slice(5, 8)
  }
  
  localZipCode.value = value
}

async function searchCep() {
  const cleanCep = localZipCode.value.replace(/\D/g, '')
  
  if (cleanCep.length === 8) {
    isSearching.value = true
    
    try {
      const response = await axios.get(`/cep/${cleanCep}`)
      const data = response.data
      
      if (data.logradouro) props.form.address = data.logradouro
      if (data.bairro) props.form.neighborhood = data.bairro
      if (data.localidade) props.form.city = data.localidade
      if (data.uf) props.form.state = data.uf
      
    } catch (error) {
      console.error('Erro ao buscar CEP:', error)
    } finally {
      isSearching.value = false
    }
  }
}
</script>
