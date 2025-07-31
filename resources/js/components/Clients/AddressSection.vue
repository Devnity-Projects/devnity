<template>
  <div class="space-y-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
      <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
      Endereço
    </h3>
    
    <AddressWithCepSearch :form="form" />
  </div>
</template>

<script setup lang="ts">
import AddressWithCepSearch from './AddressWithCepSearch.vue'

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

defineProps<Props>()
</script>