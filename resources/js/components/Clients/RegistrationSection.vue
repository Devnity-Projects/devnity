<template>
  <div v-if="form.type === 'Pessoa Jurídica'" class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
      <div class="w-2 h-2 rounded-full bg-orange-500"></div>
      Inscrições
    </h3>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Inscrição Estadual
        </label>
        <input
          v-model="form.state_registration"
          type="text"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="Inscrição estadual"
        />
        <div v-if="form.errors.state_registration" class="text-red-500 text-sm mt-1">{{ form.errors.state_registration }}</div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
          Inscrição Municipal
        </label>
        <input
          v-model="form.municipal_registration"
          type="text"
          class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
          placeholder="Inscrição municipal"
        />
        <div v-if="form.errors.municipal_registration" class="text-red-500 text-sm mt-1">{{ form.errors.municipal_registration }}</div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Form {
  type: string
  state_registration: string
  municipal_registration: string
  errors: Record<string, string>
}

interface Props {
  form: Form
}

defineProps<Props>()
</script>