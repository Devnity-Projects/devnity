<script setup lang="ts">
import { watch, onMounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useToast } from '@/components/ui/toast/use-toast'
import type { ToastProps } from '@/components/ui/toast' // Ajuste o caminho conforme seu projeto

// Tipos aceitos de mensagem
const flashMessageTypes = ['success', 'error', 'info', 'warning'] as const
type FlashType = (typeof flashMessageTypes)[number]

const { toast } = useToast()
const page = usePage()

function displayFlashMessage(type: FlashType, message?: string) {
  if (!message) return
  let title = 'Notificação'
  let variant: ToastProps['variant'] = 'default'

  switch (type) {
    case 'success': title = 'Sucesso!'; variant = 'default'; break
    case 'error':   title = 'Erro!';    variant = 'destructive'; break
    case 'info':    title = 'Informação'; variant = 'default'; break
    case 'warning': title = 'Atenção!'; variant = 'default'; break
  }
  toast({ title, description: message, variant })
}

// Assiste mudanças em cada tipo de flash
flashMessageTypes.forEach(type => {
  watch(
    () => page.props.flash?.[type],
    (newMessage) => {
      displayFlashMessage(type, newMessage)
    }
  )
})

// Mostra o flash já presente ao montar
onMounted(() => {
  const flash = page.props.flash
  flashMessageTypes.forEach(type => {
    displayFlashMessage(type, flash?.[type])
  })
})
</script>

<template>
  <!-- Não renderiza nada visual -->
</template>
