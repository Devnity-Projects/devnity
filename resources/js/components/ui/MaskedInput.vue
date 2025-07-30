<script setup lang="ts">
import { computed, ref, watch } from 'vue'

interface Props {
  modelValue?: string
  type?: 'cpf' | 'cnpj' | 'cpf-cnpj' | 'phone' | 'cep' | 'text'
  placeholder?: string
  required?: boolean
  disabled?: boolean
  class?: string
  label?: string
  error?: string
}

const props = withDefaults(defineProps<Props>(), {
  type: 'text',
  modelValue: ''
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const inputRef = ref<HTMLInputElement>()

const masks = {
  cpf: '999.999.999-99',
  cnpj: '99.999.999/9999-99',
  phone: '(99) 99999-9999',
  cep: '99999-999'
}

const dynamicMask = computed(() => {
  if (props.type === 'cpf-cnpj') {
    const cleanValue = (props.modelValue || '').replace(/\D/g, '')
    return cleanValue.length <= 11 ? masks.cpf : masks.cnpj
  }
  return masks[props.type as keyof typeof masks] || ''
})

function applyMask(value: string): string {
  if (!dynamicMask.value) return value
  
  const cleanValue = value.replace(/\D/g, '')
  const mask = dynamicMask.value
  let maskedValue = ''
  let valueIndex = 0
  
  for (let i = 0; i < mask.length && valueIndex < cleanValue.length; i++) {
    if (mask[i] === '9') {
      maskedValue += cleanValue[valueIndex]
      valueIndex++
    } else {
      maskedValue += mask[i]
    }
  }
  
  return maskedValue
}

function removeMask(value: string): string {
  return value.replace(/\D/g, '')
}

function handleInput(event: Event) {
  const target = event.target as HTMLInputElement
  const cursorPosition = target.selectionStart || 0
  
  let value = target.value
  
  if (dynamicMask.value) {
    const cleanValue = removeMask(value)
    const maskedValue = applyMask(cleanValue)
    
    target.value = maskedValue
    
    // Ajustar posição do cursor
    let newPosition = cursorPosition
    if (maskedValue.length < value.length) {
      newPosition = Math.max(0, cursorPosition - 1)
    } else if (maskedValue.length > value.length) {
      newPosition = cursorPosition + 1
    }
    
    setTimeout(() => {
      target.setSelectionRange(newPosition, newPosition)
    }, 0)
    
    emit('update:modelValue', cleanValue)
  } else {
    emit('update:modelValue', value)
  }
}

const displayValue = computed(() => {
  if (dynamicMask.value && props.modelValue) {
    return applyMask(props.modelValue)
  }
  return props.modelValue || ''
})

const inputClass = computed(() => [
  'w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all',
  props.error ? 'border-red-500 focus:ring-red-500' : '',
  props.class
].filter(Boolean).join(' '))
</script>

<template>
  <div class="space-y-2">
    <label v-if="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <input
      ref="inputRef"
      :value="displayValue"
      :placeholder="placeholder"
      :required="required"
      :disabled="disabled"
      :class="inputClass"
      @input="handleInput"
    />
    
    <div v-if="error" class="text-red-500 text-sm">{{ error }}</div>
  </div>
</template>
