#!/bin/sh
set -e

# Cria o diretório para o socket do PHP-FPM
# O '-p' garante que o comando não falhe se o diretório já existir.
mkdir -p /run/php

# Verifica se a extensão LDAP está habilitada
echo "Verificando extensão LDAP..."
php -m | grep -i ldap || echo "AVISO: Extensão LDAP não encontrada!"

# Instala as dependências do Composer
echo "Instalando dependências do Composer..."
composer install --no-interaction --optimize-autoloader --no-dev

# Gera chave da aplicação se não existir
if [ ! -f .env ]; then
    echo "Copiando .env.example para .env..."
    cp .env.example .env
    php artisan key:generate
fi

# Otimizações Laravel
echo "Executando otimizações Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ajusta permissões
echo "Ajustando permissões..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Inicia o Supervisor, que gerencia o Nginx e o PHP-FPM
echo "Iniciando Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf