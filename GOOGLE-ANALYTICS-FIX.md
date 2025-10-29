# Correção do Google Analytics

## 🔴 Problemas Identificados

1. **Variável não configurada**: `GOOGLE_ANALYTICS_ID` não existia no arquivo `.env`
2. **Race condition**: O script do Google Analytics era carregado antes do Vue.js estar pronto
3. **Sem tratamento de erro**: Se o gtag não existisse, a aplicação falhava silenciosamente

## ✅ Mudanças Realizadas

### 1. **Arquivo `.env`**
Adicionadas as variáveis:
```bash
GOOGLE_ANALYTICS_ID=seu-id-aqui
VITE_GOOGLE_ANALYTICS_ID="${GOOGLE_ANALYTICS_ID}"
```

### 2. **`resources/js/composables/useAnalytics.ts`** (REESCRITO)
- Adicionada função `waitForAnalytics()` para aguardar o carregamento do gtag
- Adicionada função `trackEvent()` para rastrear eventos customizados
- Adicionada função `isAnalyticsReady()` para verificar se Analytics está pronto
- Melhoria na limpeza de listeners ao desmontar
- Adicionados logs de debug para facilitar troubleshooting

### 3. **`resources/views/app.blade.php`** (MELHORADO)
- Adicionados logs de console para debug
- Adicionadas opções de privacidade (anonymize_ip, etc.)
- Adicionado bloco `@else` para avisar quando ID não está configurado

### 4. **`resources/js/layouts/AppLayout.vue`** (SEM MUDANÇAS)
- Já estava chamando `initTracking()` corretamente no `onMounted()`

## 🚀 Como Usar

### Configuração Inicial

1. **Obtenha seu ID do Google Analytics**:
   - Acesse [Google Analytics](https://analytics.google.com)
   - Crie uma propriedade ou use uma existente
   - Copie o ID (formato: `G-XXXXXXXXXX` ou `UA-XXXXXXXXX-X`)

2. **Configure no `.env`**:
   ```bash
   GOOGLE_ANALYTICS_ID=G-SEUIDENTIFICADOR
   VITE_GOOGLE_ANALYTICS_ID="${GOOGLE_ANALYTICS_ID}"
   ```

3. **Recrie o build de assets**:
   ```bash
   npm run build
   ```

4. **Reinicie o servidor**:
   ```bash
   php artisan serve
   ```

### Rastrear Eventos Customizados

Use a composable em qualquer componente Vue:

```typescript
import { useAnalytics } from '@/composables/useAnalytics'

export default {
  setup() {
    const { trackEvent } = useAnalytics()
    
    const handleClick = () => {
      trackEvent('button_click', {
        button_name: 'submit',
        page: 'form'
      })
    }
    
    return { handleClick }
  }
}
```

## 🔍 Verificação

1. **Abra o Console do Navegador** (F12):
   - Procure por mensagens `[Analytics]`
   - Deve mostrar: `Google Analytics initialized with ID: G-XXXXXXXXXX`

2. **Verifique no Google Analytics**:
   - Vá em: Relatórios > Engajamento > Visões gerais
   - Deve mostrar atividades em tempo real

3. **Teste com Network Tab**:
   - Vá em DevTools > Network
   - Procure por requisições para `google-analytics.com` ou `googletagmanager.com`
   - Deve haver POST requests de rastreamento

## 📋 Checklist de Troubleshooting

- [ ] `.env` contém `GOOGLE_ANALYTICS_ID` preenchido?
- [ ] Rodou `npm run build` após alterar `.env`?
- [ ] Servidor foi reiniciado?
- [ ] Console mostra mensagem de inicialização do Analytics?
- [ ] Google Analytics recebe dados em tempo real?
- [ ] Verificou se o ID está correto no Google Analytics?

## 📚 Documentação Útil

- [Google Analytics 4 Documentation](https://support.google.com/analytics)
- [Implementação gtag.js](https://developers.google.com/analytics/devguides/collection/gtagjs)
- [Inertia.js Guide](https://inertiajs.com/)
