import { computed, watch } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import type { User } from '@/types'

export function useUserSettings() {
  const page = usePage()
  
  const user = computed(() => page.props.auth?.user as User | null)
  
  const userSettings = computed(() => user.value?.settings)
  
  const theme = computed(() => userSettings.value?.theme || 'system')
  
  const getEffectiveTheme = () => {
    const userTheme = theme.value
    if (userTheme === 'system') {
      return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }
    return userTheme
  }
  
  const applyTheme = () => {
    const effectiveTheme = getEffectiveTheme()
    const isDark = effectiveTheme === 'dark'
    document.documentElement.classList.toggle('dark', isDark)
  }
  
  const updateUserTheme = (newTheme: 'light' | 'dark' | 'system') => {
    // Update backend settings
    router.patch(route('settings.update'), {
      theme: newTheme,
      language: userSettings.value?.language || 'pt-BR',
      timezone: userSettings.value?.timezone || 'America/Sao_Paulo',
      date_format: userSettings.value?.date_format || 'd/m/Y',
      time_format: userSettings.value?.time_format || 'H:i',
      email_notifications: userSettings.value?.email_notifications ?? true,
      browser_notifications: userSettings.value?.browser_notifications ?? true,
      task_reminders: userSettings.value?.task_reminders ?? true,
      project_updates: userSettings.value?.project_updates ?? false,
    }, {
      preserveState: true,
      preserveScroll: true,
      only: ['auth'],
    })
  }
  
  const toggleTheme = () => {
    const currentEffective = getEffectiveTheme()
    const newTheme = currentEffective === 'dark' ? 'light' : 'dark'
    updateUserTheme(newTheme)
  }
  
  // Computed para verificar se o tema atual é escuro (baseado nas configurações do usuário)
  const isDarkTheme = computed(() => {
    return getEffectiveTheme() === 'dark'
  })
  
  // Watch for changes in user theme settings and apply them automatically
  watch(theme, () => {
    applyTheme()
  }, { immediate: false })
  
  // Set up system theme listener for when user preference is 'system'
  const setupSystemThemeListener = () => {
    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)')
    const listener = () => {
      if (theme.value === 'system') {
        applyTheme()
      }
    }
    mediaQuery.addEventListener('change', listener)
    return () => mediaQuery.removeEventListener('change', listener)
  }
  
  return {
    user,
    userSettings,
    theme,
    isDarkTheme,
    getEffectiveTheme,
    applyTheme,
    updateUserTheme,
    toggleTheme,
    setupSystemThemeListener,
  }
}
