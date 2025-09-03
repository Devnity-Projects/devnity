<?php

namespace Database\Seeders;

use App\Models\FinancialCategory;
use Illuminate\Database\Seeder;

class FinancialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            // Receitas
            [
                'name' => 'Desenvolvimento de Software',
                'description' => 'Receitas de projetos de desenvolvimento',
                'type' => FinancialCategory::TYPE_INCOME,
                'color' => '#10b981',
                'icon' => 'Code',
                'is_active' => true,
            ],
            [
                'name' => 'Consultoria',
                'description' => 'Receitas de serviços de consultoria',
                'type' => FinancialCategory::TYPE_INCOME,
                'color' => '#3b82f6',
                'icon' => 'Users',
                'is_active' => true,
            ],
            [
                'name' => 'Manutenção',
                'description' => 'Receitas de manutenção de sistemas',
                'type' => FinancialCategory::TYPE_INCOME,
                'color' => '#8b5cf6',
                'icon' => 'Tool',
                'is_active' => true,
            ],
            [
                'name' => 'Licenças de Software',
                'description' => 'Receitas de licenciamento',
                'type' => FinancialCategory::TYPE_INCOME,
                'color' => '#f59e0b',
                'icon' => 'Key',
                'is_active' => true,
            ],

            // Despesas
            [
                'name' => 'Infraestrutura',
                'description' => 'Custos com servidores, hospedagem e infraestrutura',
                'type' => FinancialCategory::TYPE_EXPENSE,
                'color' => '#ef4444',
                'icon' => 'Server',
                'is_active' => true,
            ],
            [
                'name' => 'Ferramentas e Software',
                'description' => 'Licenças de ferramentas de desenvolvimento',
                'type' => FinancialCategory::TYPE_EXPENSE,
                'color' => '#f97316',
                'icon' => 'Settings',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing e Publicidade',
                'description' => 'Gastos com marketing digital e publicidade',
                'type' => FinancialCategory::TYPE_EXPENSE,
                'color' => '#ec4899',
                'icon' => 'Megaphone',
                'is_active' => true,
            ],
            [
                'name' => 'Educação e Treinamento',
                'description' => 'Cursos, certificações e treinamentos',
                'type' => FinancialCategory::TYPE_EXPENSE,
                'color' => '#6366f1',
                'icon' => 'BookOpen',
                'is_active' => true,
            ],
            [
                'name' => 'Equipamentos',
                'description' => 'Computadores, periféricos e equipamentos',
                'type' => FinancialCategory::TYPE_EXPENSE,
                'color' => '#64748b',
                'icon' => 'Monitor',
                'is_active' => true,
            ],
            [
                'name' => 'Impostos e Taxas',
                'description' => 'Impostos, taxas governamentais e contábeis',
                'type' => FinancialCategory::TYPE_EXPENSE,
                'color' => '#dc2626',
                'icon' => 'Receipt',
                'is_active' => true,
            ],
            [
                'name' => 'Escritório',
                'description' => 'Aluguel, energia, internet e despesas de escritório',
                'type' => FinancialCategory::TYPE_EXPENSE,
                'color' => '#059669',
                'icon' => 'Building',
                'is_active' => true,
            ],
            [
                'name' => 'Terceirizados',
                'description' => 'Pagamentos para freelancers e prestadores de serviço',
                'type' => FinancialCategory::TYPE_EXPENSE,
                'color' => '#7c3aed',
                'icon' => 'UserPlus',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            FinancialCategory::firstOrCreate(
                ['name' => $category['name'], 'type' => $category['type']],
                $category
            );
        }
    }
}
