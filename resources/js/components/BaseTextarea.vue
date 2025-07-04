<script setup lang="ts">
const props = defineProps<{
  modelValue?: string
  rows?: number
  placeholder?: string
  required?: boolean
  disabled?: boolean
  class?: string
}>()
const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <textarea
    :rows="rows || 3"
    :placeholder="placeholder"
    :required="required"
    :disabled="disabled"
    :class="[
      'w-full rounded-lg border px-3 py-2 bg-white dark:bg-[#232336] dark:text-gray-100 transition focus:ring-2 focus:ring-[#6a0dad] outline-none',
      props.class
    ]"
    :value="modelValue"
    @input="$emit('update:modelValue', $event.target.value)"
  ></textarea>
</template>
