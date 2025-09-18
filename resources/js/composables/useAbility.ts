import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAbility() {
  const page = usePage();
  const user = computed(() => (page.props.auth as any)?.user ?? null);
  const roles = computed<string[]>(() => user.value?.roles ?? []);
  const permissions = computed<string[]>(() => user.value?.permissions ?? []);

  function hasRole(...required: string[]) {
    if (!user.value) return false;
    return required.some(r => roles.value.includes(r));
  }

  function can(...required: string[]) {
    if (!user.value) return false;
    return required.some(p => permissions.value.includes(p));
  }

  return { user, roles, permissions, hasRole, can };
}
