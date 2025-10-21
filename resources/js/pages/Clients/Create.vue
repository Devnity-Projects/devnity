<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft, Save, UserPlus, Search, Loader2 } from 'lucide-vue-next'

const form = useForm({
  name: '',
  type: 'Pessoa Física',
  document: '',
  email: '',
  phone: '',
  responsible: '',
  responsible_email: '',
  responsible_phone: '',
  state_registration: '',
  municipal_registration: '',
  zip_code: '',
  address: '',
  number: '',
  complement: '',
  neighborhood: '',
  city: '',
  state: '',
  country: 'Brasil',
  status: 'ativo',
  notes: ''
})

// CEP search functionality
const searchingCep = ref(false)

// Document formatting functions
function formatDocument(value: string) {
  // Remove tudo que não é número
  const cleanValue = value.replace(/\D/g, '')
  
  if (form.type === 'Pessoa Física') {
    // Formato CPF: 000.000.000-00
    return cleanValue
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d{1,2})/, '$1-$2')
      .replace(/(-\d{2})\d+?$/, '$1')
  } else {
    // Formato CNPJ: 00.000.000/0000-00
    return cleanValue
      .replace(/(\d{2})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1.$2')
      .replace(/(\d{3})(\d)/, '$1/$2')
      .replace(/(\d{4})(\d)/, '$1-$2')
      .replace(/(-\d{2})\d+?$/, '$1')
  }
}

function onDocumentInput(event: Event) {
  const target = event.target as HTMLInputElement
  const formatted = formatDocument(target.value)
  form.document = formatted
}

async function searchCep() {
  if (!form.zip_code || form.zip_code.length < 8) return
  
  searchingCep.value = true
  
  try {
    const response = await fetch(`/api/cep/${form.zip_code.replace(/\D/g, '')}`)
    if (response.ok) {
      const data = await response.json()
      
      if (!data.error) {
        form.address = data.logradouro || form.address
        form.neighborhood = data.bairro || form.neighborhood
        form.city = data.localidade || form.city
        form.state = data.uf || form.state
      }
    }
  } catch (error) {
    console.error('Erro ao buscar CEP:', error)
  } finally {
    searchingCep.value = false
  }
}

function submit() {
  form.post(route('clients.store'), {
    onSuccess: () => {
      router.visit(route('clients.index'))
    },
  })
}

// Watch for type changes to reformat document
watch(() => form.type, () => {
  if (form.document) {
    form.document = formatDocument(form.document)
  }
})
</script>

<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      <!-- Header Simples -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <UserPlus class="h-6 w-6 text-gray-600" />
          <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Novo Cliente</h1>
        </div>
        <Link
          href="/clients"
          class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors"
        >
          <ArrowLeft class="h-4 w-4" />
          Voltar
        </Link>
      </div>

      <!-- Formulário -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Informações Básicas -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Informações Básicas</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Nome *
              </label>
              <input
                id="name"
                v-model="form.name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Nome completo ou razão social"
              />
              <div v-if="form.errors.name" class="text-sm text-red-600 dark:text-red-400 mt-1">
                {{ form.errors.name }}
              </div>
            </div>
            
            <div>
              <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Tipo *
              </label>
              <select
                id="type"
                v-model="form.type"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              >
                <option value="Pessoa Física">Pessoa Física</option>
                <option value="Pessoa Jurídica">Pessoa Jurídica</option>
              </select>
            </div>

            <div>
              <label for="document" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ form.type === 'Pessoa Física' ? 'CPF' : 'CNPJ' }} *
              </label>
              <input
                id="document"
                v-model="form.document"
                type="text"
                required
                @input="onDocumentInput"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                :placeholder="form.type === 'Pessoa Física' ? '000.000.000-00' : '00.000.000/0000-00'"
              />
              <div v-if="form.errors.document" class="text-sm text-red-600 dark:text-red-400 mt-1">
                {{ form.errors.document }}
              </div>
            </div>

            <div>
              <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Status
              </label>
              <select
                id="status"
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              >
                <option value="ativo">Ativo</option>
                <option value="inativo">Inativo</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Contato -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Contato</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                E-mail
              </label>
              <input
                id="email"
                v-model="form.email"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="email@exemplo.com"
              />
            </div>

            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Telefone
              </label>
              <input
                id="phone"
                v-model="form.phone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="(11) 99999-9999"
              />
            </div>
          </div>
        </div>

        <!-- Responsável (apenas para PJ) -->
        <div v-if="form.type === 'Pessoa Jurídica'" class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Responsável</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label for="responsible" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Nome
              </label>
              <input
                id="responsible"
                v-model="form.responsible"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="Nome do responsável"
              />
            </div>

            <div>
              <label for="responsible_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                E-mail
              </label>
              <input
                id="responsible_email"
                v-model="form.responsible_email"
                type="email"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="email@exemplo.com"
              />
            </div>

            <div>
              <label for="responsible_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Telefone
              </label>
              <input
                id="responsible_phone"
                v-model="form.responsible_phone"
                type="tel"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                placeholder="(11) 99999-9999"
              />
            </div>
          </div>
        </div>

        <!-- Endereço -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Endereço</h3>
          
          <div class="grid grid-cols-1 gap-4">
            <!-- CEP com busca -->
            <div class="md:w-1/3">
              <label for="zip_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                CEP
              </label>
              <div class="flex gap-2">
                <input
                  id="zip_code"
                  v-model="form.zip_code"
                  type="text"
                  class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  placeholder="00000-000"
                  @input="searchCep"
                />
                <button
                  type="button"
                  @click="searchCep"
                  :disabled="searchingCep"
                  class="px-3 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 transition-colors"
                >
                  <Loader2 v-if="searchingCep" class="h-4 w-4 animate-spin" />
                  <Search v-else class="h-4 w-4" />
                </button>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="md:col-span-2">
                <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Endereço
                </label>
                <input
                  id="address"
                  v-model="form.address"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  placeholder="Rua, Avenida, etc."
                />
              </div>

              <div>
                <label for="number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Número
                </label>
                <input
                  id="number"
                  v-model="form.number"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  placeholder="123"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label for="complement" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Complemento
                </label>
                <input
                  id="complement"
                  v-model="form.complement"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  placeholder="Apt, Sala, etc."
                />
              </div>

              <div>
                <label for="neighborhood" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Bairro
                </label>
                <input
                  id="neighborhood"
                  v-model="form.neighborhood"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  placeholder="Bairro"
                />
              </div>

              <div>
                <label for="city" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Cidade
                </label>
                <input
                  id="city"
                  v-model="form.city"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  placeholder="Cidade"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="state" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  Estado
                </label>
                <input
                  id="state"
                  v-model="form.state"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  placeholder="SP"
                />
              </div>

              <div>
                <label for="country" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                  País
                </label>
                <input
                  id="country"
                  v-model="form.country"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                  placeholder="Brasil"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Observações -->
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
          <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Observações</h3>
          
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
              Notas
            </label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
              placeholder="Observações adicionais sobre o cliente..."
            ></textarea>
          </div>
        </div>

        <!-- Botões -->
        <div class="flex items-center justify-end gap-3 pt-4">
          <Link
            href="/clients"
            class="px-4 py-2 text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white transition-colors"
          >
            Cancelar
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="flex items-center gap-2 px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 transition-colors"
          >
            <Save class="h-4 w-4" />
            Salvar Cliente
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
