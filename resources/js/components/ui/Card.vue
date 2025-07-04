<script setup lang="ts">
defineProps({
  title: String,
  subtitle: String,
  actions: Array, // [{icon: '', label: '', click: fn}]
})
</script>

<template>
  <div class="bg-white rounded-2xl shadow p-6 flex flex-col gap-2">
    <div class="flex items-center justify-between">
      <div>
        <div class="text-xl font-bold text-[#6a0dad]">{{ title }}</div>
        <div v-if="subtitle" class="text-gray-500 text-sm">{{ subtitle }}</div>
      </div>
      <div class="flex gap-2">
        <button v-for="action in actions" :key="action.label" @click="action.click"
          class="text-xl hover:text-[#e55e55]">
          <span v-html="action.icon"></span>
        </button>
      </div>
    </div>
    <slot />
  </div>
</template>
