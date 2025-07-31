<template>
  <div v-if="selectedClients.length > 0" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="text-sm text-blue-700 dark:text-blue-300">
          <strong>{{ selectedClients.length }}</strong> cliente(s) selecionado(s)
        </div>
        <button
          @click="clearSelection"
          class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
        >
          Limpar seleção
        </button>
      </div>
      
      <div class="flex items-center gap-2">
        <select
          v-model="bulkAction"
          class="text-sm border border-blue-300 dark:border-blue-600 rounded px-3 py-1 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
        >
          <option value="">Selecione uma ação</option>
          <option value="activate">Ativar</option>
          <option value="deactivate">Desativar</option>
          <option value="delete">Excluir</option>
        </select>
        
        <button
          @click="executeBulkAction"
          :disabled="!bulkAction"
          class="btn-primary text-sm px-4 py-1 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Executar
        </button>
      </div>
    </div>
  </div>

  <!-- Confirmation Modal -->
  <div v-if="showConfirmModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
        Confirmar Ação
      </h3>
      
      <p class="text-gray-600 dark:text-gray-400 mb-6">
        {{ confirmMessage }}
      </p>
      
      <div class="flex justify-end gap-3">
        <button
          @click="showConfirmModal = false"
          class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200"
        >
          Cancelar
        </button>
        <button
          @click="confirmBulkAction"
          class="btn-danger"
          :class="{ 'btn-primary': bulkAction !== 'delete' }"
        >
          Confirmar
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'

interface Props {
  selectedClients: number[]
}

interface Emits {
  (e: 'clear-selection'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const bulkAction = ref('')
const showConfirmModal = ref(false)

const confirmMessage = computed(() => {
  const count = props.selectedClients.length
  
  switch (bulkAction.value) {
    case 'activate':
      return `Tem certeza que deseja ativar ${count} cliente(s)?`
    case 'deactivate':
      return `Tem certeza que deseja desativar ${count} cliente(s)?`
    case 'delete':
      return `Tem certeza que deseja excluir ${count} cliente(s)? Esta ação não pode ser desfeita.`
    default:
      return ''
  }
})

function clearSelection() {
  emit('clear-selection')
}

function executeBulkAction() {
  if (!bulkAction.value) return
  showConfirmModal.value = true
}

function confirmBulkAction() {
  const clients = props.selectedClients
  
  switch (bulkAction.value) {
    case 'activate':
      router.patch(route('clients.bulk-toggle-status'), {
        clients,
        status: 'ativo'
      })
      break
    case 'deactivate':
      router.patch(route('clients.bulk-toggle-status'), {
        clients,
        status: 'inativo'
      })
      break
    case 'delete':
      router.delete(route('clients.bulk-destroy'), {
        data: { clients }
      })
      break
  }
  
  showConfirmModal.value = false
  bulkAction.value = ''
  clearSelection()
}
</script>
