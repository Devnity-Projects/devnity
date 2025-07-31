<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
      <div class="w-2 h-2 rounded-full bg-gray-500"></div>
      Observações
    </h3>
    
    <div>
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
        Notas
      </label>
      <textarea
        v-model="form.notes"
        rows="4"
        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
        placeholder="Observações adicionais sobre o cliente..."
      ></textarea>
      <div v-if="form.errors.notes" class="text-red-500 text-sm mt-1">{{ form.errors.notes }}</div>
      <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        {{ form.notes?.length || 0 }}/5000 caracteres
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Form {
  notes: string
  errors: Record<string, string>
}

interface Props {
  form: Form
}

defineProps<Props>()
</script>