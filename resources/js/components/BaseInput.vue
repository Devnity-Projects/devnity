<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  modelValue?: string | number
  type?: string
  id?: string
  name?: string
  placeholder?: string
  required?: boolean
  disabled?: boolean
  autocomplete?: string
  class?: string
}>()

const emit = defineEmits(['update:modelValue'])

const inputClass = computed(() =>
  [
    // Tailwind utilitários
    'w-full rounded-lg border px-3 py-2 bg-white dark:bg-[#232336] dark:text-gray-100',
    'transition focus:ring-2 focus:ring-[#6a0dad]',
    'outline-none',
    props.class, // permite adicionar mais classes ao usar
  ]
    .filter(Boolean)
    .join(' ')
)
</script>

<template>
  <input
    :id="id"
    :name="name"
    :type="type || 'text'"
    :placeholder="placeholder"
    :required="required"
    :disabled="disabled"
    :autocomplete="autocomplete"
    :value="modelValue"
    :class="inputClass"
    @input="$emit('update:modelValue', $event.target.value)"
  />
</template>
