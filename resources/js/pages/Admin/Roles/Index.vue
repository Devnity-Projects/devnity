<script setup lang="ts">
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, usePage } from '@inertiajs/vue3'

const props: any = usePage().props.value
const roles = computed(() => (props.roles ?? []) as any[])
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Roles</h1>

      <div class="bg-white shadow rounded p-4">
        <table class="w-full table-auto">
          <thead>
            <tr>
              <th class="text-left">Name</th>
              <th class="text-left">Permissions</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="role in roles" :key="role.id" class="border-t">
              <td class="py-2">{{ role.name }}</td>
              <td class="py-2">{{ role.permissions.map((p: any) => p.name).join(', ') }}</td>
              <td class="py-2 text-right"><Link :href="`/admin/roles/${role.id}/edit`" class="text-blue-600">Edit</Link></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
