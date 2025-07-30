<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  modelValue?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
  class?: string
  label?: string
  error?: string
  rows?: number
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  rows: 4
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const textareaClass = computed(() => [
  'w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-vertical',
  props.error ? 'border-red-500 focus:ring-red-500' : '',
  props.class
].filter(Boolean).join(' '))

function handleInput(event: Event) {
  const target = event.target as HTMLTextAreaElement
  emit('update:modelValue', target.value)
}
</script>

<template>
  <div class="space-y-2">
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <textarea
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :rows="rows"
      :class="textareaClass"
      @input="handleInput"
    ></textarea>
    
    <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>
  </div>
</template>
