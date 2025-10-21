# 🚀 Devnity

Sistema de gestão empresarial desenvolvido com Laravel, Vue.js e Docker.

## 📋 Requisitos

- PHP 8.3+
- Composer
- Node.js 20+
- Docker & Docker Compose

## 💻 Instalação Local

```bash
git clone git@github.com:LeandroLDomingos/devnity.git
cd devnity
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

## 🚀 Deploy Automático

Este projeto utiliza **Deployer PHP** para deploy automatizado com Docker.

### 📦 Comandos Rápidos

```bash
# Windows PowerShell
.\deploy.ps1 deploy production          # Deploy completo
.\deploy.ps1 deploy:quick production    # Deploy rápido
.\deploy.ps1 rollback production        # Reverter deploy
.\deploy.ps1 status production          # Ver status

# Linux/Mac
./deploy.sh deploy production           # Deploy completo
./deploy.sh rollback production         # Reverter deploy

# Usando Deployer diretamente
vendor/bin/dep deploy production        # Deploy
vendor/bin/dep rollback production      # Rollback
vendor/bin/dep status production        # Status
vendor/bin/dep logs production          # Ver logs

# Usando Make (Linux/Mac)
make deploy ENV=production              # Deploy
make rollback ENV=production            # Rollback
make status ENV=production              # Status
```

### 📚 Documentação Completa

- **[DEPLOY-QUICK.md](./DEPLOY-QUICK.md)** - Guia rápido com comandos essenciais
- **[DEPLOY.md](./DEPLOY.md)** - Documentação completa de deploy

### ⚙️ Configuração Inicial

1. **Configure os hosts** no arquivo `deploy.php`:
   ```php
   host('production')->set('hostname', 'SEU_IP')
   ```

2. **Configure SSH** no seu computador:
   ```bash
   ssh-keygen -t ed25519
   ssh-copy-id deploy@seu-servidor.com
   ```

3. **Inicialize o servidor**:
   ```bash
   vendor/bin/dep server:init production
   vendor/bin/dep config:check production
   ```

4. **Configure .env** no servidor:
   ```bash
   scp .env.production.example deploy@servidor:/var/www/devnity/shared/.env
   ssh deploy@servidor
   nano /var/www/devnity/shared/.env
   ```

5. **Primeiro deploy**:
   ```bash
   vendor/bin/dep deploy production
   ```

## 🛠️ Tecnologias

- **Backend:** Laravel 12 + PHP 8.2+
- **Frontend:** Vue 3 + TypeScript + Inertia.js
- **Styling:** Tailwind CSS 4
- **Infraestrutura:** Docker + Nginx + Supervisor
- **Deploy:** Deployer PHP 7.5+
- **Autenticação:** Laravel Fortify
- **Permissões:** Spatie Laravel Permission

## 📝 Comandos de Deploy

| Comando | Descrição |
|---------|-----------|
| `deploy` | Deploy completo (build + assets) |
| `deploy:quick` | Deploy rápido (sem build) |
| `deploy:safe` | Deploy com modo manutenção |
| `rollback` | Reverter para versão anterior |
| `status` | Ver status do sistema |
| `logs` | Ver logs da aplicação |
| `maintenance:on` | Ativar modo manutenção |
| `maintenance:off` | Desativar modo manutenção |

### 🔧 Comandos Auxiliares

```bash
# Verificar configuração
vendor/bin/dep config:check production

# Ver status dos containers
vendor/bin/dep docker:status production

# Limpar caches
vendor/bin/dep artisan:clear production

# Reiniciar queue
vendor/bin/dep queue:restart production

# SSH no servidor
vendor/bin/dep ssh production
```

---

**Desenvolvido com ❤️ usando Laravel e Vue.js**
