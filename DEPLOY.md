# Deploy Rápido - Deployer PHP# 🚀 Guia de Deploy Automático - Devnity



## 📦 Primeira Configuração no ServidorEste guia explica como configurar e usar o sistema de deploy automático via GitHub Actions para o servidor usando Docker.



```bash## 📋 Pré-requisitos

# 1. Criar estrutura

sudo mkdir -p /var/www/devnity/shared1. Servidor Linux com Docker e Docker Compose instalados

sudo chown -R seu-usuario:seu-usuario /var/www/devnity2. SSH configurado no servidor

3. Repositório clonado no servidor

# 2. Configurar .env4. Acesso ao GitHub para configurar Secrets

nano /var/www/devnity/shared/.env

# Copie o conteúdo do .env de produção e configure## 🔧 Configuração Inicial



# 3. Criar storage e logs### 1. Preparar o Servidor

mkdir -p /var/www/devnity/shared/storage/{app,framework,logs}

mkdir -p /var/www/devnity/shared/storage/framework/{cache,sessions,views}```bash

# Conecte-se ao servidor via SSH

# 4. Ajustar permissõesssh usuario@seu-servidor.com

sudo chown -R www-data:www-data /var/www/devnity/shared/storage

sudo chmod -R 775 /var/www/devnity/shared/storage# Instale o Docker (se ainda não estiver instalado)

```curl -fsSL https://get.docker.com -o get-docker.sh

sudo sh get-docker.sh

## ⚙️ Configurar GitHub Secrets

# Instale o Docker Compose

`Settings` → `Secrets and variables` → `Actions` → `New repository secret`:sudo apt-get update

sudo apt-get install docker-compose-plugin

| Secret | Valor | Exemplo |

|--------|-------|---------|# Clone o repositório (se ainda não estiver clonado)

| `SSH_PRIVATE_KEY` | Chave privada SSH | Conteúdo de `~/.ssh/id_rsa` |cd /var/www

| `DEPLOY_HOST` | IP ou domínio | `192.168.1.100` ou `server.com` |sudo git clone git@github.com:LeandroLDomingos/devnity.git

| `DEPLOY_USER` | Usuário SSH | `deploy` ou `ubuntu` |cd devnity

| `DEPLOY_PATH` | Caminho do projeto | `/var/www/devnity` |

# Configure o arquivo .env de produção

## 🚀 Deploysudo cp .env.example .env

sudo nano .env  # Configure as variáveis de produção

### Automático (recomendado)

```bash# Dê permissões necessárias

git push origin mainsudo chown -R $USER:$USER /var/www/devnity

# GitHub Actions faz o deploy automaticamentechmod +x deploy.sh

``````



### Manual do computador### 2. Configurar Secrets no GitHub

```bash

vendor/bin/dep deploy productionVá para o repositório no GitHub: `Settings` → `Secrets and variables` → `Actions` → `New repository secret`

```

Configure os seguintes secrets:

### Manual do servidor

```bash| Secret | Descrição | Exemplo |

ssh usuario@servidor|--------|-----------|---------|

cd /var/www/devnity/current| `SSH_PRIVATE_KEY` | Chave privada SSH | Conteúdo do arquivo `~/.ssh/id_rsa` |

git pull| `SERVER_HOST` | IP ou domínio do servidor | `192.168.1.100` ou `seu-servidor.com` |

composer install --no-dev| `SERVER_USER` | Usuário SSH | `ubuntu` ou `root` |

npm ci && npm run build| `SERVER_PATH` | Caminho do projeto no servidor | `/var/www/devnity` |

php artisan migrate --force

php artisan config:cache#### Como obter a chave SSH privada:

docker-compose restart app

``````bash

# No seu computador local

## 📋 Comandos Deployercat ~/.ssh/id_rsa



```bash# Copie TODO o conteúdo, incluindo:

# Ver releases (últimas 3 versões)# -----BEGIN OPENSSH PRIVATE KEY-----

vendor/bin/dep releases production# ...

# -----END OPENSSH PRIVATE KEY-----

# Rollback (voltar versão)```

vendor/bin/dep rollback production

**⚠️ IMPORTANTE:** Nunca compartilhe sua chave privada! Use apenas nos Secrets do GitHub.

# SSH no servidor

vendor/bin/dep ssh production### 3. Configurar a Chave SSH no Servidor



# Ver logs```bash

vendor/bin/dep logs production# No servidor, adicione sua chave pública ao authorized_keys

echo "sua-chave-publica-aqui" >> ~/.ssh/authorized_keys

# Executar comando artisan

vendor/bin/dep artisan "cache:clear" production# Ou copie a chave pública do seu computador:

```# No computador local:

cat ~/.ssh/id_rsa.pub

## 🔧 Estrutura no Servidor

# No servidor:

```nano ~/.ssh/authorized_keys

/var/www/devnity/# Cole a chave pública e salve

├── current/              # Symlink para a release atual```

├── releases/

│   ├── 20251021120000/  # Release antiga## 🎯 Como Usar

│   ├── 20251021130000/  # Release atual

│   └── ...### Deploy Automático

└── shared/              # Arquivos compartilhados

    ├── .envO deploy acontece automaticamente quando você faz push para a branch `main`:

    └── storage/

``````bash

git add .

## 🐛 Troubleshootinggit commit -m "feat: nova funcionalidade"

git push origin main

### Deploy falhou```

```bash

# Ver logs detalhadosO GitHub Actions irá:

vendor/bin/dep deploy production -vvv1. ✅ Conectar ao servidor via SSH

2. ✅ Baixar o código mais recente

# Fazer rollback3. ✅ Reconstruir a imagem Docker

vendor/bin/dep rollback production4. ✅ Instalar dependências

```5. ✅ Compilar assets

6. ✅ Executar migrações

### Permissões7. ✅ Reiniciar os containers

```bash

# No servidor### Deploy Manual

sudo chown -R www-data:www-data /var/www/devnity/shared/storage

sudo chmod -R 775 /var/www/devnity/shared/storageVocê também pode executar o deploy manualmente:

```

1. Vá para o repositório no GitHub

### Limpar releases antigas2. Clique em `Actions`

```bash3. Selecione `Deploy to Production`

vendor/bin/dep cleanup production4. Clique em `Run workflow`

```5. Selecione a branch `main`

6. Clique em `Run workflow`

---

### Deploy via SSH Manual (Emergência)

📚 Documentação completa: https://deployer.org/docs/7.x/getting-started

Se precisar fazer deploy diretamente no servidor:

```bash
# Conecte-se ao servidor
ssh usuario@seu-servidor.com

# Navegue até o projeto
cd /var/www/devnity

# Execute o script de deploy
./deploy.sh
```

## 📊 Monitoramento

### Verificar Status do Deploy

No GitHub:
- Vá para `Actions` para ver o histórico de deploys
- Cada deploy mostra logs detalhados de cada etapa

No Servidor:
```bash
# Ver containers em execução
docker-compose ps

# Ver logs do container
docker-compose logs -f app

# Ver logs do Nginx
docker-compose logs -f app | grep nginx

# Ver logs do PHP
docker-compose logs -f app | grep php
```

### Verificar se a Aplicação está Funcionando

```bash
# Teste local no servidor
curl http://localhost:8002

# Teste externo
curl http://seu-servidor.com
```

## 🔧 Personalização do Deploy

### Modificar o Script de Deploy

Edite o arquivo `deploy.sh` conforme suas necessidades:

```bash
# Adicione comandos personalizados antes ou depois dos existentes
# Exemplo: limpar cache adicional
echo "🧹 Limpando cache Redis..."
docker-compose exec -T app php artisan cache:clear
```

### Modificar o Workflow

Edite `.github/workflows/deploy.yml` para:
- Adicionar testes antes do deploy
- Enviar notificações (Slack, Discord, etc.)
- Fazer deploy em múltiplos servidores
- Criar backups antes do deploy

Exemplo com backup:

```yaml
- name: Create backup
  run: |
    ssh $SERVER_USER@$SERVER_HOST "cd $SERVER_PATH && docker-compose exec -T app php artisan backup:run"
```

## 🐛 Solução de Problemas

### Erro: "Permission denied (publickey)"

```bash
# Verifique se a chave SSH está correta
ssh -i ~/.ssh/id_rsa usuario@servidor

# Verifique as permissões
chmod 600 ~/.ssh/id_rsa
chmod 700 ~/.ssh
```

### Erro: "Container já existe"

```bash
# No servidor, force a recriação
cd /var/www/devnity
docker-compose down -v
docker-compose up -d --build
```

### Erro: "Permission denied" em arquivos

```bash
# No servidor, ajuste permissões
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Deploy está lento

```bash
# Desabilite a reconstrução do cache no deploy.sh
# Remova a flag --no-cache do comando docker-compose build
docker-compose build  # Sem --no-cache
```

## 📝 Boas Práticas

1. **Sempre teste em develop antes de fazer merge para main**
2. **Use tags para versionar releases importantes**
   ```bash
   git tag -a v1.0.0 -m "Release version 1.0.0"
   git push origin v1.0.0
   ```
3. **Mantenha backups regulares do banco de dados**
4. **Monitore os logs após cada deploy**
5. **Configure um arquivo `.env` específico para produção**
6. **Use variáveis de ambiente para configurações sensíveis**

## 🔒 Segurança

- ✅ Nunca commite arquivos `.env`
- ✅ Use secrets do GitHub para dados sensíveis
- ✅ Mantenha as dependências atualizadas
- ✅ Configure firewall no servidor (UFW)
- ✅ Use HTTPS com certificado SSL
- ✅ Limite o acesso SSH por IP quando possível

## � Backup e Restauração

### Fazer Backup Manual

```bash
# No servidor
cd /var/www/devnity
./backup.sh
```

O script de backup cria:
- Dump do banco de dados (MySQL ou SQLite)
- Backup dos arquivos de storage
- Cópia do arquivo .env
- Manifest com informações do backup

Backups são armazenados em `backups/` e mantidos por 30 dias.

### Restaurar Backup

```bash
# No servidor
cd /var/www/devnity
./restore.sh

# Siga as instruções no menu interativo
```

⚠️ **ATENÇÃO:** A restauração sobrescreve os dados atuais!

### Backups Automáticos

Configure backups automáticos com cron:

```bash
# Editar crontab
crontab -e

# Adicionar backup diário às 2h
0 2 * * * /var/www/devnity/backup.sh >> /var/www/devnity/storage/logs/backup.log 2>&1
```

Veja exemplos completos em `crontab.example`.

## 🛠️ Administração do Servidor

### Script de Administração Interativo

```bash
# No servidor
cd /var/www/devnity
./admin.sh
```

O script `admin.sh` oferece um menu interativo com opções para:
- Ver status e logs
- Reiniciar/parar/iniciar aplicação
- Executar migrations
- Limpar cache
- Criar usuários admin
- Fazer backup
- E muito mais!

### Scripts Disponíveis

| Script | Descrição |
|--------|-----------|
| `deploy.sh` | Deploy completo da aplicação |
| `server-setup.sh` | Configuração inicial do servidor |
| `backup.sh` | Backup do banco e arquivos |
| `restore.sh` | Restaurar backup |
| `admin.sh` | Menu de administração |

## 📊 Monitoramento

### Logs

```bash
# Ver logs em tempo real
docker-compose logs -f app

# Ver últimas 100 linhas
docker-compose logs --tail=100 app

# Ver logs de um serviço específico
docker-compose logs -f db
docker-compose logs -f redis
```

### Recursos do Sistema

```bash
# Uso de CPU e memória dos containers
docker stats

# Uso de disco
df -h

# Memória do sistema
free -h

# Processos do Docker
docker ps
```

### Health Check

```bash
# Verificar se aplicação está respondendo
curl http://localhost:8002

# Verificar status dos containers
docker-compose ps

# Verificar logs de erro
docker-compose logs app | grep -i error
```

## 🔄 Manutenção

### Atualizar Dependências

```bash
# Atualizar Composer
docker-compose exec app composer update

# Atualizar NPM
docker-compose exec app npm update

# Atualizar tudo e rebuild
./deploy.sh
```

### Limpar Cache e Otimizar

```bash
# Limpar todos os caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Recriar caches otimizados
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache
```

### Limpar Docker

```bash
# Remover imagens não utilizadas
docker image prune -f

# Limpeza completa do Docker
docker system prune -a -f

# Remover volumes não utilizados
docker volume prune -f
```

## 🔧 Docker Compose Alternativo

Para ambientes de produção com MySQL e Redis, use:

```bash
# Copiar configuração de produção
cp docker-compose.prod.yml docker-compose.yml

# Configurar variáveis no .env
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=devnity_prod
DB_USERNAME=devnity_user
DB_PASSWORD=senha_forte

REDIS_HOST=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis

# Iniciar com MySQL e Redis
docker-compose up -d
```

## �📚 Recursos Adicionais

- [Documentação Docker](https://docs.docker.com/)
- [GitHub Actions](https://docs.github.com/en/actions)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Docker Compose](https://docs.docker.com/compose/)

## 🆘 Suporte

Em caso de problemas:
1. Verifique os logs do GitHub Actions
2. Verifique os logs do container no servidor
3. Use o `./admin.sh` para diagnóstico interativo
4. Teste o deploy manualmente via SSH
5. Revise as configurações de secrets
6. Verifique a conexão SSH
7. Consulte os backups disponíveis

---

**Última atualização:** 21 de Outubro de 2025
