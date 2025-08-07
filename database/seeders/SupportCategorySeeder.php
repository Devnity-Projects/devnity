<?php

namespace Database\Seeders;

use App\Models\SupportCategory;
use Illuminate\Database\Seeder;

class SupportCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Problemas Técnicos',
                'slug' => 'technical-issues',
                'description' => 'Problemas relacionados a bugs, falhas no sistema, erros técnicos',
                'color' => '#EF4444',
                'icon' => 'AlertTriangle',
                'sort_order' => 1,
            ],
            [
                'name' => 'Solicitação de Funcionalidade',
                'slug' => 'feature-request',
                'description' => 'Pedidos de novas funcionalidades ou melhorias no sistema',
                'color' => '#8B5CF6',
                'icon' => 'Lightbulb',
                'sort_order' => 2,
            ],
            [
                'name' => 'Dúvidas Gerais',
                'slug' => 'general-questions',
                'description' => 'Dúvidas sobre como usar o sistema, configurações, etc.',
                'color' => '#3B82F6',
                'icon' => 'HelpCircle',
                'sort_order' => 3,
            ],
            [
                'name' => 'Problemas de Acesso',
                'slug' => 'access-issues',
                'description' => 'Problemas com login, senha, permissões de acesso',
                'color' => '#F59E0B',
                'icon' => 'Lock',
                'sort_order' => 4,
            ],
            [
                'name' => 'Problemas de Performance',
                'slug' => 'performance-issues',
                'description' => 'Sistema lento, timeout, problemas de performance',
                'color' => '#FB923C',
                'icon' => 'Zap',
                'sort_order' => 5,
            ],
            [
                'name' => 'Questões Financeiras',
                'slug' => 'billing-issues',
                'description' => 'Dúvidas sobre faturas, pagamentos, planos',
                'color' => '#10B981',
                'icon' => 'CreditCard',
                'sort_order' => 6,
            ],
            [
                'name' => 'Treinamento',
                'slug' => 'training',
                'description' => 'Solicitações de treinamento, onboarding, capacitação',
                'color' => '#06B6D4',
                'icon' => 'GraduationCap',
                'sort_order' => 7,
            ],
            [
                'name' => 'Outros',
                'slug' => 'others',
                'description' => 'Assuntos que não se encaixam nas outras categorias',
                'color' => '#6B7280',
                'icon' => 'MessageCircle',
                'sort_order' => 999,
            ],
        ];

        foreach ($categories as $category) {
            SupportCategory::create($category);
        }
    }
}
