<?php
namespace Deployer;

require 'recipe/laravel.php';

// ==========================================
// CONFIGURAções GERAIS
// ==========================================

set('application', 'Devnity');
set('docker_project_name', function () {
    // Sanitiza o nome da aplicação para ser usado como nome do projeto Docker.
    $name = get('application');
    $name = strtolower($name);
    $name = preg_replace('/[^a-z0-9]/', '', $name); // Remove caracteres não alfanuméricos
    return $name;
});
set('repository', 'git@github.com:Devnity-Projects/devnity.git');
set('keep_releases', 3);
set('writable_mode', 'chmod');
set('writable_chmod_mode', '0775');
// Use symlinks relativos para que os links (ex: storage) funcionem dentro do container Docker
set('use_relative_symlink', true);
set('ssh_multiplexing', false);
set('default_timeout', 3600); // Timeout padrão para comandos longos

// Helper function para executar comandos Docker
// Durante build (antes do docker:up): usa 'run' com volume temporário
// Após docker:up: usa 'exec' no container rodando (evita criar containers sem porta)
function dockerRun(string $command, array $options = []): string {
    $projectName = get('docker_project_name');
    $timeout = $options['timeout'] ?? 1800;
    $env = $options['env'] ?? '';
    $forceRun = $options['force_run'] ?? false; // Força uso do 'run' mesmo se container estiver rodando
    
    // Se force_run estiver ativo, sempre usa 'run' (necessário durante o build)
    if (!$forceRun) {
        // Tenta usar exec se o container estiver rodando
        $checkCmd = "cd {{release_path}} 2>/dev/null && docker compose --project-name {$projectName} ps -q app 2>/dev/null | grep -q . && echo 'yes' || echo 'no'";
        
        try {
            $isRunning = run($checkCmd, ['timeout' => 10]);
            if (trim($isRunning) === 'yes') {
                info('🔄 Usando container existente (exec)...');
                $dockerCmd = "docker compose --project-name {$projectName} exec -T {$env} app {$command}";
                return run("cd {{release_path}} && {$dockerCmd}", ['timeout' => $timeout]);
            }
        } catch (\Exception $e) {
            // Se falhar a verificação, continua para usar 'run'
        }
    }
    
    // Container não está rodando ou force_run ativo - usa 'run' temporário
    $dockerCmd = "docker compose --project-name {$projectName} run --rm --no-deps --entrypoint \"\" -v \"{{release_path}}:/var/www/html\" -w /var/www/html {$env} app {$command}";
    return run("cd {{release_path}} && {$dockerCmd}", ['timeout' => $timeout]);
}

// ==========================================
// CONFIGURAÇÃO DO SERVIDOR
// ==========================================

host('production')
    ->set('remote_user', 'deployer')
    ->set('hostname', '173.249.1.40') // Altere para o IP do servidor de produção
    ->set('port', 22)
    ->set('deploy_path', '/var/www/devnity')
    ->set('branch', 'main')
    ->set('labels', ['stage' => 'production']);


// ==========================================
// ARQUIVOS E PASTAS COMPARTILHADAS
// ==========================================

add('shared_files', [
    '.env',
]);

add('writable_dirs', [
    'bootstrap/cache',
    'storage',
]);

// ==========================================
// TASKS CUSTOMIZADAS
// ==========================================

desc('Parar containers Docker');
task('docker:down', function () {
    run('[ -L {{deploy_path}}/current ] && cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} down || true');
});

desc('Build da imagem Docker');
task('docker:build', function () {
    info('🐳 Construindo imagem Docker...');
    run('cd {{release_path}} && docker compose --project-name {{docker_project_name}} build --no-cache', ['timeout' => 3600]);
});

desc('Instalar dependências Node.js');
task('npm:install', function () {
    info('📦 Instalando dependências Node.js...');
    dockerRun('npm ci', ['timeout' => 1800, 'force_run' => true]);
});

desc('Compilar assets com Vite');
task('npm:build', function () {
    info('⚡ Compilando assets com Vite...');
    dockerRun('npm run build', ['timeout' => 1800, 'force_run' => true]);
});

task('build:assets', [
    'npm:install',
    'npm:build',
])->desc('Instalar dependências NPM e compilar assets');

// Garante que o arquivo Vite "hot" não exista em produção (evita apontar para o dev server)
desc('Remover arquivo Vite hot');
task('vite:remove-hot', function () {
    // Remove de um release novo (se existir por engano)
    run('rm -f {{release_path}}/public/hot || true');
    // Remove também do release atual (por segurança)
    run('[ -L {{deploy_path}}/current ] && rm -f $(readlink -f {{deploy_path}}/current)/public/hot || true');
});


desc('Subir containers Docker');
task('docker:up', function () {
    // Para containers existentes antes de subir novos (garante que as portas sejam publicadas)
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} down || true');
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} up -d');
});

desc('Aguardar containers iniciarem');
task('docker:wait', function () {
    info('⏳ Aguardando containers estarem prontos...');
    
    // Aguarda até 30 segundos para o container estar rodando
    $maxAttempts = 30;
    $attempt = 0;
    
    while ($attempt < $maxAttempts) {
        $result = run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} ps -q app 2>/dev/null || echo ""', ['timeout' => 10]);
        
        if (!empty(trim($result))) {
            info('✅ Container está rodando!');
            sleep(2); // Aguarda mais 2 segundos para garantir que o PHP-FPM/Nginx estejam prontos
            return;
        }
        
        $attempt++;
        sleep(1);
    }
    
    warning('⚠️ Container pode não estar completamente pronto após 30 segundos.');
});

// Seeders opcionais (não executam automaticamente no deploy)
desc('Rodar seeder: CreatePermissionsFromRoutesSeeder');
task('seed:permissions', function () {
    $cmd = 'php artisan db:seed --class="Database\\Seeders\\CreatePermissionsFromRoutesSeeder" --force';
    $script = 'if [ -L {{deploy_path}}/current ]; then \
        cd $(readlink -f {{deploy_path}}/current); \
        if docker compose --project-name {{docker_project_name}} ps -q app | grep -q .; then \
            docker compose --project-name {{docker_project_name}} exec -T app ' . $cmd . '; \
        else \
            docker compose --project-name {{docker_project_name}} run --rm --no-deps --entrypoint "" -v "$(readlink -f {{deploy_path}}/current):/var/www/html" -w /var/www/html app ' . $cmd . '; \
        fi; \
    else \
        echo "Nenhum release atual encontrado. Execute um deploy primeiro."; exit 1; \
    fi';
    run($script);
});

desc('Rodar seeder: CreateRolesSeeder');
task('seed:roles', function () {
    $cmd = 'php artisan db:seed --class="Database\\Seeders\\CreateRolesSeeder" --force';
    $script = 'if [ -L {{deploy_path}}/current ]; then \
        cd $(readlink -f {{deploy_path}}/current); \
        if docker compose --project-name {{docker_project_name}} ps -q app | grep -q .; then \
            docker compose --project-name {{docker_project_name}} exec -T app ' . $cmd . '; \
        else \
            docker compose --project-name {{docker_project_name}} run --rm --no-deps --entrypoint "" -v "$(readlink -f {{deploy_path}}/current):/var/www/html" -w /var/www/html app ' . $cmd . '; \
        fi; \
    else \
        echo "Nenhum release atual encontrado. Execute um deploy primeiro."; exit 1; \
    fi';
    run($script);
});

desc('Rodar seeders de permissões e papéis');
task('seed:all', [
    'seed:permissions',
    'seed:roles',
]);

desc('Cachear configurações e rotas do Laravel (com fallback se container não estiver rodando)');
task('artisan:cache', function () {
    // Garante diretórios necessários no host (shared) antes de rodar no container
    run('mkdir -p {{deploy_path}}/shared/storage/{app/public,framework/{cache/data,sessions,views},logs} {{deploy_path}}/shared/bootstrap/cache');

    // Comandos a executar dentro do container (evita escrita em storage/logs durante o cache)
    // Observação: definimos LOG_CHANNEL=stderr para não depender de /var/www/html/storage/logs
    $cmd = 'export LOG_CHANNEL=stderr; \
mkdir -p bootstrap/cache || true; \
php artisan config:cache && \
php artisan route:cache || { echo "[warn] route:cache falhou, executando route:clear"; php artisan route:clear; }';

    $script = 'if [ -L {{deploy_path}}/current ]; then \
        cd $(readlink -f {{deploy_path}}/current); \
        if docker compose --project-name {{docker_project_name}} ps -q app | grep -q .; then \
            docker compose --project-name {{docker_project_name}} exec -T app bash -lc ' . escapeshellarg($cmd) . '; \
        else \
            docker compose --project-name {{docker_project_name}} run --rm --no-deps --entrypoint "" -v "$(readlink -f {{deploy_path}}/current):/var/www/html" -w /var/www/html app bash -lc ' . escapeshellarg($cmd) . '; \
        fi; \
    else \
        echo "Nenhum release atual encontrado. Execute um deploy primeiro."; exit 1; \
    fi';
    run($script);
    // view:cache removido - causa erro "View path not found" com storage symlink
    // As views serão compiladas on-demand durante o primeiro acesso
});

desc('Reiniciar queue worker');
task('queue:restart', function () {
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} exec -T app supervisorctl restart queue-worker || true');
    info('🔄 Queue worker reiniciado para aplicar alterações no código.');
});

desc('Corrigir permissões de escrita');
task('permissions:fix', function () {
    $dirs = [
        '{{deploy_path}}/shared/bootstrap/cache',
        '{{deploy_path}}/shared/storage/app/public',
        '{{deploy_path}}/shared/storage/framework/cache/data',
        '{{deploy_path}}/shared/storage/framework/sessions',
        '{{deploy_path}}/shared/storage/framework/views',
        '{{deploy_path}}/shared/storage/logs',
    ];
    $dirs_str = implode(' ', $dirs);
    run("mkdir -p $dirs_str"); // -p cria os diretórios pais se não existirem
    run("sudo chown -R www-data:www-data {{deploy_path}}/shared/bootstrap/cache {{deploy_path}}/shared/storage");
    run("sudo chmod -R 0775 {{deploy_path}}/shared/bootstrap/cache {{deploy_path}}/shared/storage");
    info('🔧 Permissões das pastas compartilhadas corrigidas (mkdir, chown & chmod).');
});

task('deploy:writable', function() {
    info('⏩ Pulando a tarefa "deploy:writable" padrão. As permissões são gerenciadas por "permissions:fix".');
})->desc('Sobrescrita para evitar conflito de permissões');

desc('Limpar caches Laravel');
task('artisan:clear', function () {
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} exec -T app php artisan cache:clear || true');
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} exec -T app php artisan config:clear || true');
});

desc('Limpar recursos Docker não utilizados');
task('docker:cleanup', function () {
    run('sudo docker system prune -f');
});

desc('Limpando releases antigos com sudo');
task('deploy:cleanup', function () {
    $releases = get('releases_list');
    $keep = get('keep_releases');

    while ($keep > 0) {
        array_shift($releases);
        --$keep;
    }

    foreach ($releases as $release) {
        run("sudo rm -rf {{deploy_path}}/releases/$release");
    }
});

// ... outras tasks ...
desc('Verificar status dos containers');
task('docker:status', function () {
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} ps');
});

desc('Recriar containers (down + up) para aplicar portas');
task('docker:recreate', function () {
    info('🔄 Recriando containers Docker...');
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} down');
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} up -d');
    info('✅ Containers recriados com sucesso!');
});

desc('Ver logs da aplicação');
task('logs', function () {
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} logs --tail=50 app');
});

desc('Verificar se portas estão publicadas');
task('docker:verify_ports', function () {
    info('🔍 Verificando portas publicadas...');
    $result = run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} ps');
    
    if (strpos($result, '8002->80') === false) {
        warning('⚠️ Porta 8002 não está publicada! Recriando containers...');
        run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} down');
        run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} up -d');
        sleep(3);
        info('✅ Containers recriados com portas!');
    } else {
        info('✅ Porta 8002 está publicada corretamente!');
    }
});

desc('Modo manutenção ON');
task('maintenance:on', function () {
    run('[ -L {{deploy_path}}/current ] && cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} exec -T app php artisan down --retry=60 || true');

});

desc('Modo manutenção OFF');
task('maintenance:off', function () {
    run('[ -L {{deploy_path}}/current ] && cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} exec -T app php artisan up || true');
    run('[ -L {{deploy_path}}/current ] && cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} exec -T app php artisan config:cache || true');
    run('[ -L {{deploy_path}}/current ] && cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} exec -T app php artisan route:cache || true');
    // view:cache removido - causa erro "View path not found" com storage symlink
});
// ==========================================
// FLUXO DE DEPLOY PRINCIPAL
// ==========================================

task('deploy', [
    'deploy:prepare',
    'docker:build',
    'deploy:shared',
    'permissions:fix',
    'deploy:vendors',
    'build:assets',
    'vite:remove-hot',
    'deploy:publish',
    'docker:up',
    'docker:wait',    
    'artisan:cache',
    'queue:restart',
    'docker:verify_ports', // Verifica se as portas estão publicadas
    'deploy:cleanup',
])->desc('Fluxo de deploy completo');

// Sobrescreve o deploy:vendors para executar dentro do container Docker
task('deploy:vendors', function () {
    info('📚 Instalando dependências PHP com Composer...');
    dockerRun('composer install --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader', [
        'timeout' => 1800,
        'env' => '-e COMPOSER_ALLOW_SUPERUSER=1',
        'force_run' => true
    ]);
})->desc('Instalar vendors com Composer dentro do Docker');

// ==========================================
// DEPLOY COM MODO MANUTENÇÃO
// ==========================================
task('deploy:safe', [
    'maintenance:on',
    'deploy',
    'maintenance:off',
])->desc('Deploy com modo de manutenção');

// ==========================================
// DEPLOY RÁPIDO (sem build de imagem/assets)
// ==========================================
task('deploy:quick', [
    'deploy:prepare',
    'deploy:shared',
    'permissions:fix',
    'deploy:vendors',
    'vite:remove-hot',
    'deploy:publish',
    'docker:up',
    'docker:wait',
    'artisan:cache',
    'queue:restart',
    'deploy:success',
    'deploy:cleanup',
])->desc('Deploy rápido (sem rebuild de imagem/assets)');

// ==========================================
// ROLLBACK CUSTOMIZADO
// ==========================================
Deployer::get()->tasks->remove('rollback');
task('rollback', [
    'deploy:rollback',
    'docker:down',
    'docker:up',
    'docker:wait',
    'artisan:cache',
])->desc('Reverter para versão anterior');

// ==========================================
// CALLBACKS
// ==========================================

after('deploy:failed', function () {
    warning('❌ Deploy falhou!');
    warning('Execute "vendor/bin/dep rollback {{hostname}}" para reverter.');
    invoke('maintenance:off');
});

after('deploy:success', function () {
    info('✅ Deploy concluído com sucesso!');
    info('🌐 Sua aplicação está online!');
    info('🔗 URL: ' . get('app_url', 'http://seu-servidor.com'));
    invoke('docker:cleanup');
});

// Adicionar verificação antes do deploy
before('deploy', function () {
    info('🚀 Iniciando deploy de {{application}} para {{hostname}}');
    info('📦 Branch: {{branch}}');
    info('📂 Path: {{deploy_path}}');
});

// ==========================================
// HARDEN: GARANTIR DIRETÓRIOS ANTES DO LOCK
// ==========================================
// Em alguns ambientes o deploy:setup pode não criar .dep por permissão/ausência do caminho base.
// Garantimos a estrutura mínima antes de executar o deploy:lock para evitar "No such file or directory".
desc('Garantir diretórios base (.dep, releases, shared)');
task('ensure:base_dirs', function () {
    run('sudo mkdir -p {{deploy_path}} {{deploy_path}}/.dep {{deploy_path}}/releases {{deploy_path}}/shared');
    // Garante que o usuário remoto tenha posse do diretório base do deploy
    run('sudo chown -R {{remote_user}}:{{remote_user}} {{deploy_path}}');
});

before('deploy:lock', 'ensure:base_dirs');

// ==========================================
// TASKS AUXILIARES
// ==========================================
desc('Conectar ao servidor via SSH');
task('ssh', function () {
    run('cd $(readlink -f {{deploy_path}}/current) && bash');
});

desc('Reiniciar containers');
task('restart', function () {
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} restart');
});

desc('Status completo do sistema');
task('status', function () {
    info('📊 Status do Sistema:');
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} ps');
    run('df -h | grep -E "Filesystem|/var/www"');
    run('free -h');
});

// Inspecionar symlinks e estrutura do release atual
desc('Inspecionar estrutura do release atual');
task('inspect:current', function () {
    run('[ -L {{deploy_path}}/current ] && echo Current -> $(readlink -f {{deploy_path}}/current) || echo "sem current"');
    run('ls -la {{deploy_path}}/current');
    run('ls -la {{deploy_path}}/shared');
    run('readlink -f {{deploy_path}}/current/storage || echo "storage não encontrado"');
});

desc('Inspecionar storage dentro do container');
task('inspect:container', function () {
    $cmd = 'set -xe; ls -la /var/www/html; echo "--- storage ---"; ls -la /var/www/html/storage || true; echo "---- readlink storage ----"; readlink -f /var/www/html/storage || true; echo "---- shared exists? ----"; ls -la /var/www/shared || true;';
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} exec -T app bash -lc '.escapeshellarg($cmd));
});

// ============================
// HEALTH CHECKS
// ============================
desc('Health: testar acesso via HOST (porta 8006)');
task('health:host', function () {
    // Mostra cabeçalhos e status ao acessar a porta mapeada pelo host
    run('curl -sS -I http://127.0.0.1:8006 | sed -n "1,20p" || true');
    run('curl -sS -o /dev/null -w "HOST HTTP %{http_code} -> %{url_effective}\n" http://127.0.0.1:8006 || true');
});

desc('Health: testar acesso dentro do CONTAINER');
task('health:container', function () {
    $cmd = 'curl -sS -I http://localhost | sed -n "1,20p" || true';
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} exec -T app bash -lc '.escapeshellarg($cmd));
    $cmd2 = 'curl -sS -o /dev/null -w "CONTAINER HTTP %{http_code} -> %{url_effective}\n" http://localhost || true';
    run('cd $(readlink -f {{deploy_path}}/current) && docker compose --project-name {{docker_project_name}} exec -T app bash -lc '.escapeshellarg($cmd2));
});

// Verificar se o arquivo public/hot existe no release atual remoto
desc('Checar arquivo Vite hot remoto');
task('check:hot', function () {
    run('[ -L {{deploy_path}}/current ] && ls -l $(readlink -f {{deploy_path}}/current)/public/hot || echo "(sem arquivo hot)"');
});

// Inspecionar Nginx do HOST (proxy reverso externo)
desc('Proxy: listar portas e processos (host)');
task('proxy:ss', function () {
    run('sudo ss -ltnp | grep -E ":80|:443|:8006" || true');
});

desc('Proxy: verificar configuração Nginx (host)');
task('proxy:nginx:config', function () {
    run('sudo nginx -T | sed -n "1,120p"');
});

desc('Proxy: últimos erros do Nginx (host)');
task('proxy:nginx:errors', function () {
    run('sudo tail -n 100 /var/log/nginx/error.log || true');
});

desc('Health: HOST com fallback (curl/wget)');
task('health:host2', function () {
    run('(command -v curl >/dev/null 2>&1 && curl -sS -I http://127.0.0.1:8006 | sed -n "1,20p") || (command -v wget >/dev/null 2>&1 && wget -S --spider http://127.0.0.1:8006 2>&1 | sed -n "1,20p") || echo "Nem curl nem wget disponíveis"');
});

// ============================
// VERIFICAÇÃO DE CONFIGURAÇÃO
// ============================
desc('Verificar configuração do deploy');
task('config:check', function () {
    info('🔍 Verificando configuração...');
    info('Application: {{application}}');
    info('Repository: {{repository}}');
    info('Branch: {{branch}}');
    info('Deploy Path: {{deploy_path}}');
    info('Remote User: {{remote_user}}');
    info('Hostname: {{hostname}}');
    info('Keep Releases: {{keep_releases}}');
    
    // Testar conexão SSH
    info('🔌 Testando conexão SSH...');
    run('echo "✅ Conexão SSH estabelecida com sucesso!"');
    
    // Verificar se Docker está instalado
    info('🐳 Verificando Docker...');
    run('docker --version');
    run('docker compose version');
    
    // Verificar estrutura de diretórios
    info('📂 Verificando estrutura de diretórios...');
    run('ls -la {{deploy_path}} || echo "⚠️ Diretório de deploy não existe ainda (será criado no primeiro deploy)"');
});

desc('Configuração inicial do servidor');
task('server:init', function () {
    info('🚀 Configurando servidor inicial...');
    
    // Criar estrutura de diretórios
    run('mkdir -p {{deploy_path}}/shared');
    run('mkdir -p {{deploy_path}}/shared/storage/{app,framework,logs}');
    run('mkdir -p {{deploy_path}}/shared/storage/framework/{cache,sessions,views}');
    run('mkdir -p {{deploy_path}}/shared/bootstrap/cache');
    
    info('✅ Estrutura de diretórios criada!');
    info('📝 Próximo passo: copie o arquivo .env para {{deploy_path}}/shared/.env');
    info('Comando: scp .env.production.example {{remote_user}}@{{hostname}}:{{deploy_path}}/shared/.env');
    info('Depois edite o arquivo no servidor e configure as variáveis de ambiente.');
});