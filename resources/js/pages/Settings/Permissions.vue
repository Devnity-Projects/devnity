<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { useAbility } from '@/composables/useAbility'
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

interface User { 
  id: number
  name: string
  email: string
  avatar_url?: string 
}

interface AdminUser extends User {
  roles: string[]
}

interface Role {
  id: number
  name: string
  permissions: string[]
  users_count: number
}

interface RoleManagement {
  enabled: boolean
  roles: Role[]
  allPermissions: string[]
  permissionGroups: Array<{ key: string; label: string; permissions: string[] }>
  permissionsMeta: Record<string, { label: string; description: string }>
}

interface AdminControls {
  enabled: boolean
  users: AdminUser[]
  target: AdminUser
  targetRoles: string[]
  targetPermissions: string[]
  targetDirectPermissions: string[]
  allPermissions: string[]
  allRoles: Array<{ id: number; name: string; permissions_count: number }>
  permissionsMeta: Record<string, { label: string; description: string }>
  permissionGroups: Array<{ key: string; label: string; permissions: string[] }>
}

interface Props {
  user: User
  roles: string[]
  permissions: string[]
  admin?: AdminControls | null
  rolesManagement?: RoleManagement | null
}

const props = defineProps<Props>()
const { can } = useAbility()

// Current tab
const activeTab = ref<'users' | 'roles'>('users')

// Admin state (user management)
const selectedAdminUserId = ref<number | null>(props.admin?.target?.id ?? null)
const adminEnabled = computed(() => !!props.admin?.enabled)
const adminTarget = computed(() => props.admin?.target)
const adminTargetRoles = computed(() => props.admin?.targetRoles ?? [])
const adminTargetPermsEffective = computed(() => props.admin?.targetPermissions ?? [])
const adminTargetPermsDirect = computed(() => props.admin?.targetDirectPermissions ?? [])
const permMeta = computed(() => props.admin?.permissionsMeta ?? {})
const permGroups = computed(() => props.admin?.permissionGroups ?? [])
const isSelfSelected = computed(() => selectedAdminUserId.value === props.user.id)

// Role management state
const rolesManagementEnabled = computed(() => !!props.rolesManagement?.enabled)
const selectedRoleId = ref<number | null>(props.rolesManagement?.roles?.[0]?.id ?? null)
const selectedRole = computed(() => 
  props.rolesManagement?.roles.find(r => r.id === selectedRoleId.value)
)
const rolePermGroups = computed(() => props.rolesManagement?.permissionGroups ?? [])
const rolePermMeta = computed(() => props.rolesManagement?.permissionsMeta ?? {})

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

// Role management: toggle permission for role
const toggleRolePermission = (permName: string) => {
  if (!selectedRoleId.value) return
  const hasPermission = selectedRole.value?.permissions.includes(permName) ?? false
  router.patch(route('settings.roles.update', selectedRoleId.value), {
    permissions: hasPermission 
      ? selectedRole.value?.permissions.filter(p => p !== permName) ?? []
      : [...(selectedRole.value?.permissions ?? []), permName]
  }, {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ['rolesManagement'] })
  })
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
            Gerencie permissões, roles e acesso de usuários
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
              Permissões
            </a>
          </nav>
        </div>

        <!-- Main Content -->
        <div class="lg:w-3/4 space-y-6">
          <!-- Tabs -->
          <div v-if="adminEnabled || rolesManagementEnabled" class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
              <button
                v-if="adminEnabled"
                @click="activeTab = 'users'"
                :class="[
                  activeTab === 'users'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Gerenciar Usuários
              </button>
              <button
                v-if="rolesManagementEnabled"
                @click="activeTab = 'roles'"
                :class="[
                  activeTab === 'roles'
                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
                ]"
              >
                Gerenciar Roles
              </button>
            </nav>
          </div>

          <!-- User Management Tab -->
          <div v-if="activeTab === 'users' && adminEnabled" class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Gerenciar Usuários</h2>
                  <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Selecione um usuário e atribua roles e permissões diretas
                  </p>
                </div>
              </div>

              <!-- User selector -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usuário</label>
                <select 
                  v-model.number="selectedAdminUserId" 
                  @change="selectAdminUser" 
                  class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option v-for="u in admin?.users" :key="u.id" :value="u.id">
                    {{ u.name }} — {{ u.email }}
                    <span v-if="u.roles && u.roles.length" class="text-gray-500">({{ u.roles.join(', ') }})</span>
                  </option>
                </select>
              </div>

              <!-- Impersonation actions -->
              <div v-if="!isSelfSelected && can('users.impersonate')" class="mb-6 flex items-center gap-3 p-4 bg-blue-50 dark:bg-blue-950/20 rounded-lg border border-blue-200 dark:border-blue-900">
                <button 
                  @click="impersonateSelected" 
                  class="px-4 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors"
                >
                  <svg class="inline-block w-4 h-4 mr-2 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Visualizar como {{ adminTarget?.name }}
                </button>
                <span class="text-xs text-blue-700 dark:text-blue-300">
                  Você será autenticado como este usuário para visualizar o sistema do ponto de vista dele
                </span>
              </div>

              <!-- Roles for selected user -->
              <div class="mb-8">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  Roles do Usuário
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                  <div 
                    v-for="role in admin?.allRoles" 
                    :key="role.id" 
                    class="flex items-center justify-between p-4 rounded-lg border transition-colors"
                    :class="adminTargetRoles.includes(role.name) 
                      ? 'border-blue-200 bg-blue-50 dark:border-blue-900 dark:bg-blue-950/30' 
                      : 'border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900/50'"
                  >
                    <div class="flex items-center gap-3">
                      <div>
                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ role.name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ role.permissions_count }} permissões</div>
                      </div>
                    </div>
                    <label v-if="can('users.manage')" class="relative inline-flex items-center cursor-pointer">
                      <input 
                        type="checkbox" 
                        class="sr-only peer" 
                        :checked="adminTargetRoles.includes(role.name)" 
                        @change="adminToggleRole(role.name)"
                      >
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                  </div>
                </div>
              </div>

              <!-- Direct permissions for selected user (grouped by sections) -->
              <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center">
                  <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                  Permissões Diretas do Usuário
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                  Permissões diretas são atribuídas especificamente a este usuário, independente de roles. 
                  Permissões efetivas via role são mostradas mas não podem ser removidas diretamente aqui.
                </p>

                <div class="space-y-4">
                  <div v-for="grp in permGroups" :key="grp.key" class="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                      <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ grp.label }}</h4>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                      <div v-for="perm in grp.permissions" :key="perm" class="flex items-start justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                        <div class="flex flex-col gap-1 flex-1">
                          <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                              {{ permMeta[perm]?.label || perm }}
                            </span>
                            <span 
                              v-if="adminTargetPermsEffective.includes(perm) && !adminTargetPermsDirect.includes(perm)" 
                              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"
                            >
                              Via Role
                            </span>
                            <span 
                              v-else-if="adminTargetPermsDirect.includes(perm)" 
                              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300"
                            >
                              Direta
                            </span>
                          </div>
                          <p v-if="permMeta[perm]?.description" class="text-xs text-gray-500 dark:text-gray-400">
                            {{ permMeta[perm]?.description }}
                          </p>
                        </div>
                        <label v-if="can('users.manage')" class="relative inline-flex items-center cursor-pointer ml-4">
                          <input 
                            type="checkbox" 
                            class="sr-only peer" 
                            :checked="adminTargetPermsDirect.includes(perm)" 
                            @change="adminTogglePermission(perm)"
                          >
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Role Management Tab -->
          <div v-if="activeTab === 'roles' && rolesManagementEnabled" class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Gerenciar Roles</h2>
                  <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Configure permissões para cada role do sistema
                  </p>
                </div>
              </div>

              <!-- Role selector -->
              <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                <select 
                  v-model.number="selectedRoleId" 
                  class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
                  <option v-for="role in rolesManagement?.roles" :key="role.id" :value="role.id">
                    {{ role.name }} ({{ role.users_count }} usuários, {{ role.permissions.length }} permissões)
                  </option>
                </select>
              </div>

              <div v-if="selectedRole">
                <!-- Role info -->
                <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                  <div class="flex items-center justify-between">
                    <div>
                      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ selectedRole.name }}</h3>
                      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ selectedRole.users_count }} {{ selectedRole.users_count === 1 ? 'usuário' : 'usuários' }} com esta role
                      </p>
                    </div>
                    <div class="text-right">
                      <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ selectedRole.permissions.length }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">permissões ativas</div>
                    </div>
                  </div>
                </div>

                <!-- Permissions grouped by module -->
                <div class="space-y-4">
                  <div v-for="grp in rolePermGroups" :key="grp.key" class="border border-gray-200 dark:border-gray-800 rounded-lg overflow-hidden">
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                      <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ grp.label }}</h4>
                      <span v-if="selectedRole" class="text-xs text-gray-500 dark:text-gray-400">
                        {{ grp.permissions.filter(p => selectedRole.permissions.includes(p)).length }} / {{ grp.permissions.length }}
                      </span>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                      <div v-for="perm in grp.permissions" :key="perm" class="flex items-start justify-between px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-900/50 transition-colors">
                        <div class="flex flex-col gap-1 flex-1">
                          <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                              {{ rolePermMeta[perm]?.label || perm }}
                            </span>
                            <span 
                              v-if="selectedRole.permissions.includes(perm)" 
                              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300"
                            >
                              Ativa
                            </span>
                          </div>
                          <p v-if="rolePermMeta[perm]?.description" class="text-xs text-gray-500 dark:text-gray-400">
                            {{ rolePermMeta[perm]?.description }}
                          </p>
                        </div>
                        <label v-if="can('roles.manage')" class="relative inline-flex items-center cursor-pointer ml-4">
                          <input 
                            type="checkbox" 
                            class="sr-only peer" 
                            :checked="selectedRole.permissions.includes(perm)" 
                            @change="toggleRolePermission(perm)"
                          >
                          <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Show message if no permissions -->
          <div v-if="!adminEnabled && !rolesManagementEnabled" class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Sem permissão</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
              Você não tem permissão para gerenciar usuários ou roles.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
