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

## 🚀 Deploy com Deployer

### Configurar Servidor (uma vez)

```bash
# No servidor
mkdir -p /var/www/devnity/shared
cd /var/www/devnity/shared
nano .env  # Configure as variáveis de produção
```

### Configurar Secrets no GitHub

`Settings` → `Secrets` → `Actions`:

- `SSH_PRIVATE_KEY` - Chave privada SSH
- `DEPLOY_HOST` - IP/domínio do servidor
- `DEPLOY_USER` - Usuário SSH (ex: deploy)
- `DEPLOY_PATH` - Caminho no servidor (ex: /var/www/devnity)

### Deploy

```bash
# Automático: push para main
git push origin main

# Manual: do seu computador
vendor/bin/dep deploy production
```

## 🛠️ Tecnologias

- Laravel 11 + Vue 3 + TypeScript
- Tailwind CSS 4 + Inertia.js
- Docker + Nginx + Supervisor
- Deployer PHP

## 📝 Comandos Úteis

```bash
# Ver releases no servidor
vendor/bin/dep releases production

# Rollback (voltar para versão anterior)
vendor/bin/dep rollback production

# SSH no servidor
vendor/bin/dep ssh production
```

---

**Desenvolvido com ❤️ usando Laravel e Vue.js**
