import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function usePermissions() {
  const page = usePage()
  
  const user = computed(() => (page.props as any).auth?.user)
  const permissions = computed(() => (page.props as any).auth?.permissions || [])
  const roles = computed(() => (page.props as any).auth?.roles || [])

  const hasPermission = (permission: string): boolean => {
    return permissions.value.includes(permission)
  }

  const hasAnyPermission = (...perms: string[]): boolean => {
    return perms.some(permission => permissions.value.includes(permission))
  }

  const hasAllPermissions = (...perms: string[]): boolean => {
    return perms.every(permission => permissions.value.includes(permission))
  }

  const hasRole = (role: string): boolean => {
    return roles.value.includes(role)
  }

  const hasAnyRole = (...roleNames: string[]): boolean => {
    return roleNames.some(role => roles.value.includes(role))
  }

  const hasAllRoles = (...roleNames: string[]): boolean => {
    return roleNames.every(role => roles.value.includes(role))
  }

  const isSuperAdmin = computed(() => hasRole('super-admin'))
  const isAdmin = computed(() => hasAnyRole('super-admin', 'admin'))

  return {
    user,
    permissions,
    roles,
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    hasRole,
    hasAnyRole,
    hasAllRoles,
    isSuperAdmin,
    isAdmin,
  }
}
