<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\UserSettings;

echo "=== TESTE DE ATUALIZAÇÃO DE CONFIGURAÇÕES ===\n";

$user = User::first();
if (!$user) {
    echo "❌ Nenhum usuário encontrado\n";
    exit(1);
}

echo "✅ Testando com usuário: {$user->name}\n";

$settings = $user->getOrCreateSettings();

echo "🔧 Configurações antes da atualização:\n";
echo "  Tema: {$settings->theme}\n";
echo "  Idioma: {$settings->language}\n";
echo "  Notificações email: " . ($settings->email_notifications ? 'Sim' : 'Não') . "\n";

// Simular atualização
$newData = [
    'theme' => 'dark',
    'language' => 'en-US',
    'email_notifications' => false,
    'browser_notifications' => true,
    'task_reminders' => false,
    'project_updates' => true,
    'timezone' => 'America/New_York',
];

echo "\n🔄 Atualizando configurações...\n";
$settings->update($newData);
$settings->refresh();

echo "✅ Configurações após a atualização:\n";
echo "  Tema: {$settings->theme}\n";
echo "  Idioma: {$settings->language}\n";
echo "  Notificações email: " . ($settings->email_notifications ? 'Sim' : 'Não') . "\n";
echo "  Fuso horário: {$settings->timezone}\n";

// Reverter para os valores originais
echo "\n🔙 Revertendo para configurações padrão...\n";
$settings->update(UserSettings::getDefaultSettings());

echo "✅ Teste concluído com sucesso!\n";
