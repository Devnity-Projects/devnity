# Correção do Google Analytics com Deployer

## 🔴 Problema Identificado

Quando você faz deploy com Deployer, o `npm run build` era executado em um container Docker com um volume temporário, portanto **não tinha acesso ao arquivo `.env` compartilhado**. Isso causava que `VITE_GOOGLE_ANALYTICS_ID` fosse `undefined` no build, mesmo que tivesse sido configurado no servidor.

## ✅ Soluções Implementadas

### 1. **`vite.config.ts`** - Garantir injeção de variáveis de ambiente
Adicionado configuração `define` para injetar `process.env` no build:
```typescript
define: {
    'import.meta.env': JSON.stringify(process.env),
}
```

### 2. **`deploy.php`** - Copiar `.env` antes do build

#### Adicionada tarefa `build:prepare`:
```php
task('build:prepare', function () {
    run('cp -f {{deploy_path}}/shared/.env {{release_path}}/.env || true');
});
```

Isso garante que o `.env` compartilhado seja copiado para o `release_path` **antes** do npm build.

#### Melhorada tarefa `npm:build`:
- Adicionado debug para verificar se variáveis VITE_ estão presentes
- Mostra as variáveis detectadas no log do deploy

#### Atualizada sequência `build:assets`:
```php
task('build:assets', [
    'build:prepare',  // ← NOVO: Copia .env antes
    'npm:install',
    'npm:build',
])
```

## 🚀 Como Fazer o Deploy Agora

```bash
dep deploy production
```

O novo fluxo será:
1. ✅ `deploy:prepare` - Prepara release
2. ✅ `docker:build` - Constrói imagem Docker
3. ✅ `deploy:shared` - Copia `.env` para shared
4. ✅ `permissions:fix` - Corrige permissões
5. ✅ `deploy:vendors` - Instala dependências PHP
6. ✅ **`build:prepare`** ← NOVO: Copia `.env` para release_path
7. ✅ `npm:install` - Instala dependências Node
8. ✅ `npm:build` - Compila assets (COM `.env` disponível!)
9. ✅ ... resto do deploy

## 🔍 Verificação

Após o deploy, procure no log por:

```
[DEBUG] 📋 Variáveis de ambiente detectadas:
GOOGLE_ANALYTICS_ID=G-35FHQCG2F4
VITE_GOOGLE_ANALYTICS_ID=G-35FHQCG2F4
VITE_APP_NAME=Devnity
```

Se ver essas linhas, as variáveis foram detectadas corretamente durante o build! ✅

## 📋 Checklist de Troubleshooting

- [ ] Verificou o log do deploy por "Variáveis de ambiente detectadas"?
- [ ] O `.env` no servidor tem `GOOGLE_ANALYTICS_ID` preenchido?
- [ ] Após o deploy, o console do navegador mostra `[Analytics] Google Analytics initialized with ID`?
- [ ] Não há erros de "undefined" no console do navegador?
- [ ] Testou conectar no Google Analytics e viu dados chegando?

## 🎯 O Que Mudou Desde o Último Deploy

### Modificações:
1. ✏️ `vite.config.ts` - Adicionada injeção de variáveis
2. ✏️ `deploy.php` - Adicionadas tarefas de preparação e debug
3. ✏️ `.env` - Configurado `GOOGLE_ANALYTICS_ID=G-35FHQCG2F4`

### Nenhum arquivo excluído ou renomeado
- ✅ `useAnalytics.ts` continua funcionando
- ✅ `app.blade.php` continua funcionando
- ✅ `AppLayout.vue` continua funcionando

## 💡 Dicas

Se ainda tiver problemas após o deploy:

1. **Verifique o logs do servidor:**
   ```bash
   ssh deployer@173.249.1.40
   cd /var/www/devnity/current
   docker compose logs -f app
   ```

2. **Verifique se as variáveis foram compiladas no bundle:**
   ```bash
   # Dentro do container
   grep "G-35FHQCG2F4" public/build/app.js
   ```

3. **Teste o analytics localmente antes de fazer deploy:**
   ```bash
   VITE_GOOGLE_ANALYTICS_ID=G-35FHQCG2F4 npm run build
   npm run dev
   # Abra http://localhost:3000 e veja o console
   ```
