<script setup lang="ts">
defineProps({
  type: { type: String, default: 'button' },
  variant: { type: String, default: 'primary' }, // primary, secondary, danger, ghost
  disabled: Boolean,
})
</script>

<template>
  <button
    :type="type"
    :class="[
      'px-4 py-2 font-bold rounded-2xl transition focus:outline-none',
      variant === 'primary' && 'bg-[#6a0dad] text-white hover:bg-[#5a089e]',
      variant === 'secondary' && 'bg-white text-[#6a0dad] border border-[#6a0dad] hover:bg-[#ede1f9]',
      variant === 'danger' && 'bg-[#e55e55] text-white hover:bg-[#d23b3b]',
      variant === 'ghost' && 'bg-transparent text-[#6a0dad] hover:bg-[#ede1f9]'
    ]"
    :disabled="disabled"
  >
    <slot />
  </button>
</template>
