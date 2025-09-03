<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\UserSettings;

echo "=== TESTE DE CONFIGURAÇÕES ===\n";

// Verificar se existe usuário
$user = User::first();
if (!$user) {
    echo "❌ Nenhum usuário encontrado no banco\n";
    exit(1);
}

echo "✅ Usuário encontrado: {$user->name} ({$user->email})\n";

// Verificar se o usuário tem configurações
$settings = $user->settings;
if (!$settings) {
    echo "⚠️ Usuário não tem configurações, criando...\n";
    $settings = $user->settings()->create(UserSettings::getDefaultSettings());
    echo "✅ Configurações criadas com sucesso\n";
} else {
    echo "✅ Configurações existem\n";
}

// Exibir as configurações atuais
echo "\n=== CONFIGURAÇÕES ATUAIS ===\n";
echo "Tema: {$settings->theme}\n";
echo "Idioma: {$settings->language}\n";
echo "Fuso horário: {$settings->timezone}\n";
echo "Notificações por email: " . ($settings->email_notifications ? 'Sim' : 'Não') . "\n";
echo "Notificações do navegador: " . ($settings->browser_notifications ? 'Sim' : 'Não') . "\n";
echo "Lembretes de tarefas: " . ($settings->task_reminders ? 'Sim' : 'Não') . "\n";
echo "Atualizações de projetos: " . ($settings->project_updates ? 'Sim' : 'Não') . "\n";

echo "\n✅ Teste concluído com sucesso!\n";
