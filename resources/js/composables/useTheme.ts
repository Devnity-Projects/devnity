import { ref, watch } from 'vue'

const theme = ref<'light' | 'dark'>(getInitialTheme())

function getInitialTheme(): 'light' | 'dark' {
  // Prioridade: localStorage > SO
  const stored = localStorage.getItem('theme')
  if (stored === 'dark' || stored === 'light') return stored
  return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
}

function applyTheme(value: 'light' | 'dark') {
  theme.value = value
  localStorage.setItem('theme', value)
  if (value === 'dark') {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

// Observa a mudança e aplica
watch(theme, (value) => applyTheme(value), { immediate: true })

function toggleTheme() {
  theme.value = theme.value === 'dark' ? 'light' : 'dark'
}

export function useTheme() {
  return { theme, toggleTheme, applyTheme }
}
