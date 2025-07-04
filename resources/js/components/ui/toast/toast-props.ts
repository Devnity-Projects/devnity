export type ToastProps = {
  title: string
  description: string
  variant?: 'default' | 'destructive' | 'info' | 'warning'
  timeout?: number
}
