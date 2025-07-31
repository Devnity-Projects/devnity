<template>
  <div>
    <h1>Debug Client Show</h1>
    <pre>{{ JSON.stringify(client, null, 2) }}</pre>
    <pre>{{ JSON.stringify(recentProjects, null, 2) }}</pre>
    <pre>{{ JSON.stringify(projectsCount, null, 2) }}</pre>
  </div>
</template>

<script setup lang="ts">
interface Client {
  id: number
  name: string
  type: string
  document: string
  formatted_document?: string
  email?: string
  phone?: string
  formatted_phone?: string
  responsible?: string
  responsible_email?: string
  responsible_phone?: string
  state_registration?: string
  municipal_registration?: string
  zip_code?: string
  address?: string
  number?: string
  complement?: string
  neighborhood?: string
  city?: string
  state?: string
  country?: string
  status: string
  notes?: string
  created_at: string
}

const props = defineProps<{
  client: Client
  recentProjects?: any[]
  projectsCount?: number
}>()
</script>
