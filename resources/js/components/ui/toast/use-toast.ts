import { ref } from 'vue'
import type { ToastProps } from './toast-props'

/**
 * Composable para exibir toasts em qualquer lugar da aplicação.
 */
export function useToast() {
  // Lista reativa de toasts (opcional, para múltiplos toasts)
  const toasts = ref<ToastProps[]>([])

  function toast({ title, description, variant = 'default', timeout = 4000 }: ToastProps) {
    // Use seu próprio componente Toast aqui, ou faça algo simples:
    // Para demo, só um alert, mas você pode emitir eventos ou manipular uma store.
    // alert(`[${variant}] ${title}: ${description}`) // só para debug

    // Se você tem um <Toasts /> que mostra baseando-se em um array, só adicionar:
    toasts.value.push({ title, description, variant, timeout })

    // Remove automaticamente após timeout
    setTimeout(() => {
      toasts.value.shift()
    }, timeout)
  }

  return { toast, toasts }
}
