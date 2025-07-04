<script setup lang="ts">
defineProps({
  modelValue: [String, Number],
  type: { type: String, default: 'text' },
  placeholder: String,
  disabled: Boolean,
  error: String,
})
const emit = defineEmits(['update:modelValue'])
</script>

<template>
  <div>
    <input
      :type="type"
      :placeholder="placeholder"
      :value="modelValue"
      :disabled="disabled"
      @input="emit('update:modelValue', $event.target.value)"
      class="rounded-xl border px-4 py-3 w-full focus:outline-none transition
      border-[#d1d5db] focus:border-[#6a0dad] text-base
      disabled:bg-gray-100 disabled:cursor-not-allowed"
      :class="{ 'border-[#e55e55]': error }"
    />
    <div v-if="error" class="text-[#e55e55] text-xs mt-1">{{ error }}</div>
  </div>
</template>
