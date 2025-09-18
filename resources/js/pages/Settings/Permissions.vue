<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

interface User { id: number; name: string; email: string; avatar_url?: string }
interface AdminUser { id: number; name: string; email: string }
interface AdminControls {
  enabled: boolean
  users: AdminUser[]
  target: AdminUser
  targetRoles: string[]
  targetPermissions: string[] // effective
  targetDirectPermissions: string[]
  allPermissions: string[]
  permissionsMeta?: Record<string, { label: string; description: string }>
}

interface Props {
  user: User
  roles: string[]
  admin?: AdminControls | null
}

const props = defineProps<Props>()

// Admin state
const selectedAdminUserId = ref<number | null>(props.admin?.target?.id ?? null)
const adminEnabled = computed(() => !!props.admin?.enabled)
const adminTarget = computed(() => props.admin?.target)
const adminTargetRoles = computed(() => props.admin?.targetRoles ?? [])
const adminTargetPermsEffective = computed(() => props.admin?.targetPermissions ?? [])
const adminTargetPermsDirect = computed(() => props.admin?.targetDirectPermissions ?? [])
const allPerms = computed(() => props.admin?.allPermissions ?? [])
const permMeta = computed(() => props.admin?.permissionsMeta ?? {})
const isSelfSelected = computed(() => selectedAdminUserId.value === props.user.id)

// Removidos: toggles próprios do usuário (permissões/roles)

// Admin: change selected user
const selectAdminUser = () => {
  if (!selectedAdminUserId.value) return
  router.get(route('settings.permissions'), { admin_user_id: selectedAdminUserId.value }, {
    preserveScroll: true,
    preserveState: true,
    only: ['admin']
  })
}

// Admin: toggle role for selected user
const adminToggleRole = (roleName: string) => {
  if (!selectedAdminUserId.value) return
  const hasRole = adminTargetRoles.value.includes(roleName)
  router.post(route('settings.admin.roles.toggle'), {
    user_id: selectedAdminUserId.value,
    role: roleName,
    granted: !hasRole,
  }, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ['admin'] })
  })
}

// Admin: toggle permission for selected user (direct permission)
const adminTogglePermission = (permName: string) => {
  if (!selectedAdminUserId.value) return
  // compute current direct grant
  const isDirect = adminTargetPermsDirect.value.includes(permName)
  router.post(route('settings.admin.permissions.toggle'), {
    user_id: selectedAdminUserId.value,
    permission: permName,
    granted: !isDirect,
  }, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ['admin'] })
  })
}

// Admin: impersonate selected user
const impersonateSelected = () => {
  if (!selectedAdminUserId.value) return
  router.post(route('settings.impersonate.start'), { user_id: selectedAdminUserId.value }, { preserveScroll: true })
}
</script>

<template>
  <AppLayout>
    <div class="space-y-8">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
            Configurações
          </h1>
          <p class="text-gray-600 dark:text-gray-400 mt-1">
            Gerencie roles e permissões dos usuários
          </p>
        </div>
      </div>

      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Navigation -->
        <div class="lg:w-1/4">
          <nav class="space-y-1">
            <a
              :href="route('settings.index')"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
            >
              <svg class="text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Geral
            </a>
            <a
              :href="route('settings.profile.edit')"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
            >
              <svg class="text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              Perfil
            </a>
            <a
              :href="route('settings.password.edit')"
              class="group flex items-center px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:text-white dark:hover:bg-gray-800 transition-colors"
            >
              <svg class="text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              Senha
            </a>
            <a
              :href="route('settings.permissions')"
              class="bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 group flex items-center px-3 py-2 text-sm font-medium rounded-lg"
            >
              <svg class="text-blue-500 dark:text-blue-300 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c1.657 0 3-1.567 3-3.5S13.657 4 12 4s-3 1.567-3 3.5S10.343 11 12 11z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21v-2a4 4 0 00-4-4H9a4 4 0 00-4 4v2" />
              </svg>
              Permissões
            </a>
          </nav>
        </div>

        <!-- Main Content -->
        <div class="lg:w-3/4 space-y-6">
          <!-- Admin: Manage other users (only visible if allowed) -->
          <div v-if="adminEnabled" class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Admin — Gerenciar Usuários</h2>
                  <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Selecione um usuário e atribua roles e permissões diretas. Permissões podem permanecer efetivas via role.
                  </p>
                </div>
              </div>

              <!-- User selector -->
              <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Usuário</label>
                <select v-model.number="selectedAdminUserId" @change="selectAdminUser" class="w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                  <option v-for="u in admin?.users" :key="u.id" :value="u.id">{{ u.name }} — {{ u.email }}</option>
                </select>
              </div>

              <!-- Impersonation actions -->
              <div class="flex items-center gap-3">
                <button v-if="!isSelfSelected" @click="impersonateSelected" class="px-3 py-2 text-sm rounded-md bg-blue-600 text-white hover:bg-blue-700">Visualizar como selecionado</button>
                <span class="text-xs text-gray-500 dark:text-gray-400">Apenas para administradores com permissão "manage users".</span>
              </div>

              <!-- Roles for selected user -->
              <div class="mt-6">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Papeis (Roles) do usuário</h3>
                <div class="space-y-3" v-if="props.roles?.length">
                  <div v-for="role in props.roles" :key="role" class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                      <span class="text-sm text-gray-900 dark:text-gray-100">{{ role }}</span>
                      <span v-if="adminTargetRoles.includes(role)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Atribuída</span>
                      <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">Não atribuída</span>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input type="checkbox" class="sr-only peer" :checked="adminTargetRoles.includes(role)" @change="adminToggleRole(role)">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Direct permissions for selected user -->
              <div class="mt-8">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Permissões diretas do usuário</h3>
                <div class="space-y-3" v-if="allPerms?.length">
                  <div v-for="perm in allPerms" :key="perm" class="flex items-start justify-between">
                    <div class="flex flex-col gap-1">
                      <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ permMeta[perm]?.label || perm }}</span>
                        <span v-if="adminTargetPermsEffective.includes(perm) && !adminTargetPermsDirect.includes(perm)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Efetiva via role</span>
                        <span v-else-if="adminTargetPermsEffective.includes(perm)" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300">Ativa</span>
                        <span v-else class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">Inativa</span>
                      </div>
                      <p v-if="permMeta[perm]?.description" class="text-xs text-gray-500 dark:text-gray-400">{{ permMeta[perm]?.description }}</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer mt-1">
                      <input type="checkbox" class="sr-only peer" :checked="adminTargetPermsDirect.includes(perm)" @change="adminTogglePermission(perm)">
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Removidas: seções antigas de teste de permissões e roles do próprio usuário -->
        </div>
      </div>
    </div>
  </AppLayout>
</template>
