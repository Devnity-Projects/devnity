<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { usePage, Link } from '@inertiajs/vue3'

const props: any = usePage().props.value
const role = ref(props.role)
const permissions = ref(props.permissions ?? [])
const selected = ref((role.value.permissions || []).map((p: any) => p.name))
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <h1 class="text-2xl font-bold">Edit Role: {{ role.name }}</h1>

      <form :action="`/admin/roles/${role.id}`" method="POST">
        <input type="hidden" name="_method" value="PATCH" />
        <input type="hidden" name="_token" :value="usePage().props.value.csrf_token" />

        <div class="grid gap-2">
          <div v-for="perm in permissions" :key="perm.id" class="flex items-center gap-2">
            <input type="checkbox" :name="'permissions[]'" :value="perm.name" :checked="selected.includes(perm.name)" />
            <label>{{ perm.name }}</label>
          </div>
        </div>

        <div class="mt-4">
          <button class="btn btn-primary">Save</button>
          <Link href="/admin/roles" class="btn btn-secondary">Back</Link>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
