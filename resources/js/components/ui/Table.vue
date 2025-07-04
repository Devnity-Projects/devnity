<script setup lang="ts">
defineProps({
  columns: Array,
  rows: Array
})
</script>

<template>
  <table class="w-full bg-white rounded-2xl shadow">
    <thead>
      <tr>
        <th v-for="col in columns" :key="col.key" class="py-2 px-4 text-left text-gray-600 font-semibold">
          {{ col.label }}
        </th>
      </tr>
    </thead>
    <tbody>
      <tr v-for="row in rows" :key="row.id" class="hover:bg-[#ede1f9]/30 transition">
        <td v-for="col in columns" :key="col.key" class="py-2 px-4">
          <slot :name="col.key" :row="row">
            {{ row[col.key] }}
          </slot>
        </td>
      </tr>
    </tbody>
  </table>
</template>
