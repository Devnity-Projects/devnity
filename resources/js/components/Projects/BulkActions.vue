<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Trash2, Edit, CheckCircle, Pause, PlayCircle, X } from 'lucide-vue-next'

interface Props {
  selectedIds: number[]
  onClearSelection: () => void
}

const props = defineProps<Props>()

const isProcessing = ref(false)
const showStatusModal = ref(false)
const selectedStatus = ref('')

const statuses = [
  { value: 'planning', label: 'Planejamento', icon: Edit },
  { value: 'in_progress', label: 'Em Andamento', icon: PlayCircle },
  { value: 'on_hold', label: 'Pausado', icon: Pause },
  { value: 'completed', label: 'Concluído', icon: CheckCircle },
  { value: 'cancelled', label: 'Cancelado', icon: X },
]

function bulkDelete() {
  if (confirm(`Tem certeza que deseja excluir ${props.selectedIds.length} projeto(s) selecionado(s)?`)) {
    isProcessing.value = true
    
    router.delete(route('projects.bulk-destroy'), {
      data: { ids: props.selectedIds },
      preserveScroll: true,
      onFinish: () => {
        isProcessing.value = false
        props.onClearSelection()
      }
    })
  }
}

function bulkUpdateStatus() {
  if (!selectedStatus.value) return
  
  isProcessing.value = true
  
  router.patch(route('projects.bulk-update-status'), {
    ids: props.selectedIds,
    status: selectedStatus.value
  }, {
    preserveScroll: true,
    onFinish: () => {
      isProcessing.value = false
      showStatusModal.value = false
      selectedStatus.value = ''
      props.onClearSelection()
    }
  })
}
</script>

<template>
  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-sm p-4 mb-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
          {{ selectedIds.length }} projeto(s) selecionado(s)
        </span>
        
        <button
          @click="onClearSelection"
          class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
        >
          Limpar seleção
        </button>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="showStatusModal = true"
          :disabled="isProcessing"
          class="devnity-button-secondary inline-flex items-center gap-2 text-sm"
        >
          <Edit class="h-4 w-4" />
          Alterar Status
        </button>

        <button
          @click="bulkDelete"
          :disabled="isProcessing"
          class="devnity-button-danger inline-flex items-center gap-2 text-sm"
        >
          <Trash2 class="h-4 w-4" />
          {{ isProcessing ? 'Excluindo...' : 'Excluir' }}
        </button>
      </div>
    </div>

    <!-- Modal para alterar status -->
    <div 
      v-if="showStatusModal"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
      @click="showStatusModal = false"
    >
      <div 
        class="bg-white dark:bg-gray-800 rounded-lg p-6 w-96 max-w-full mx-4"
        @click.stop
      >
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
          Alterar Status
        </h3>
        
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
          Alterar o status de {{ selectedIds.length }} projeto(s) selecionado(s):
        </p>

        <div class="space-y-2 mb-6">
          <label 
            v-for="status in statuses"
            :key="status.value"
            class="flex items-center gap-3 p-3 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700"
          >
            <input
              type="radio"
              :value="status.value"
              v-model="selectedStatus"
              class="text-blue-600 focus:ring-blue-500"
            />
            <component :is="status.icon" class="h-4 w-4 text-gray-500" />
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
              {{ status.label }}
            </span>
          </label>
        </div>

        <div class="flex justify-end gap-3">
          <button
            @click="showStatusModal = false"
            class="devnity-button-secondary text-sm"
          >
            Cancelar
          </button>
          <button
            @click="bulkUpdateStatus"
            :disabled="!selectedStatus || isProcessing"
            class="devnity-button-primary text-sm"
          >
            {{ isProcessing ? 'Atualizando...' : 'Confirmar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
