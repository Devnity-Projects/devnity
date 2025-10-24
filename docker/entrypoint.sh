#!/bin/sh
set -e

# Cria o diretório para o socket do PHP-FPM
# O '-p' garante que o comando não falhe se o diretório já existir.
mkdir -p /run/php

# Instala as dependências do Composer
composer install --no-interaction --optimize-autoloader --no-dev

# Inicia o Supervisor, que gerencia o Nginx e o PHP-FPM
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf