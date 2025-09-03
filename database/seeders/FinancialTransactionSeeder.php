<?php

namespace Database\Seeders;

use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class FinancialTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Get categories
        $incomeCategories = FinancialCategory::where('type', 'income')->get();
        $expenseCategories = FinancialCategory::where('type', 'expense')->get();
        
        // Get clients and projects
        $clients = Client::all();
        $projects = Project::all();

        // Create sample income transactions
        $incomeTransactions = [
            [
                'title' => 'Desenvolvimento de Sistema CRM',
                'description' => 'Projeto de desenvolvimento de sistema CRM personalizado',
                'amount' => 15000.00,
                'due_date' => now()->addDays(30),
                'status' => 'pending',
                'payment_method' => 'PIX',
                'type' => 'income',
            ],
            [
                'title' => 'Manutenção Sistema E-commerce',
                'description' => 'Manutenção mensal do sistema de e-commerce',
                'amount' => 2500.00,
                'due_date' => now()->subDays(5),
                'payment_date' => now()->subDays(3),
                'status' => 'paid',
                'payment_method' => 'Transferência Bancária',
                'type' => 'income',
            ],
            [
                'title' => 'Consultoria em Arquitetura de Software',
                'description' => 'Consultoria para definição de arquitetura de microserviços',
                'amount' => 8000.00,
                'due_date' => now()->addDays(15),
                'status' => 'pending',
                'payment_method' => 'Boleto',
                'type' => 'income',
            ],
            [
                'title' => 'Licença de Software - Sistema de Gestão',
                'description' => 'Licença anual do sistema de gestão empresarial',
                'amount' => 5000.00,
                'due_date' => now()->addDays(60),
                'status' => 'pending',
                'payment_method' => 'Cartão de Crédito',
                'type' => 'income',
            ],
            [
                'title' => 'Desenvolvimento de App Mobile',
                'description' => 'Aplicativo mobile para iOS e Android',
                'amount' => 25000.00,
                'due_date' => now()->subDays(10),
                'status' => 'overdue',
                'payment_method' => 'PIX',
                'type' => 'income',
            ],
        ];

        // Create sample expense transactions
        $expenseTransactions = [
            [
                'title' => 'Servidor AWS - Hospedagem',
                'description' => 'Custos mensais de hospedagem na AWS',
                'amount' => 450.00,
                'due_date' => now()->addDays(5),
                'status' => 'pending',
                'payment_method' => 'Cartão de Crédito',
                'type' => 'expense',
            ],
            [
                'title' => 'Licença Adobe Creative Suite',
                'description' => 'Licença mensal do pacote Adobe para design',
                'amount' => 180.00,
                'due_date' => now()->subDays(2),
                'payment_date' => now()->subDays(1),
                'status' => 'paid',
                'payment_method' => 'Débito Automático',
                'type' => 'expense',
            ],
            [
                'title' => 'Google Ads - Marketing Digital',
                'description' => 'Investimento em anúncios no Google Ads',
                'amount' => 1200.00,
                'due_date' => now()->addDays(10),
                'status' => 'pending',
                'payment_method' => 'Cartão de Crédito',
                'type' => 'expense',
            ],
            [
                'title' => 'Curso de DevOps - Udemy',
                'description' => 'Curso online de DevOps e Kubernetes',
                'amount' => 89.90,
                'due_date' => now()->subDays(7),
                'payment_date' => now()->subDays(7),
                'status' => 'paid',
                'payment_method' => 'Cartão de Crédito',
                'type' => 'expense',
            ],
            [
                'title' => 'Notebook Dell XPS 13',
                'description' => 'Notebook para desenvolvimento móvel',
                'amount' => 6500.00,
                'due_date' => now()->addDays(20),
                'status' => 'pending',
                'payment_method' => 'Boleto',
                'type' => 'expense',
            ],
            [
                'title' => 'Imposto de Renda - Pessoa Jurídica',
                'description' => 'Pagamento trimestral do imposto de renda',
                'amount' => 3200.00,
                'due_date' => now()->subDays(15),
                'status' => 'overdue',
                'payment_method' => 'DARF',
                'type' => 'expense',
            ],
            [
                'title' => 'Aluguel do Escritório',
                'description' => 'Aluguel mensal do escritório comercial',
                'amount' => 2800.00,
                'due_date' => now()->addDays(3),
                'status' => 'pending',
                'payment_method' => 'Transferência Bancária',
                'type' => 'expense',
            ],
            [
                'title' => 'Freelancer - Design UI/UX',
                'description' => 'Pagamento para designer freelancer',
                'amount' => 1500.00,
                'due_date' => now()->subDays(1),
                'payment_date' => now(),
                'status' => 'paid',
                'payment_method' => 'PIX',
                'type' => 'expense',
            ],
        ];

        // Create income transactions
        foreach ($incomeTransactions as $index => $transactionData) {
            $category = $incomeCategories->random();
            $client = $clients->count() > 0 ? $clients->random() : null;
            $project = $projects->count() > 0 ? $projects->random() : null;

            FinancialTransaction::create([
                ...$transactionData,
                'category_id' => $category->id,
                'client_id' => $client?->id,
                'project_id' => $project?->id,
                'recurrence' => 'none',
                'installments' => 1,
                'current_installment' => 1,
                'notes' => $index % 2 === 0 ? 'Transação criada automaticamente para demonstração do sistema.' : null,
            ]);
        }

        // Create expense transactions
        foreach ($expenseTransactions as $index => $transactionData) {
            $category = $expenseCategories->random();

            FinancialTransaction::create([
                ...$transactionData,
                'category_id' => $category->id,
                'client_id' => null, // Expenses usually don't have clients
                'project_id' => null,
                'recurrence' => 'none',
                'installments' => 1,
                'current_installment' => 1,
                'notes' => $index % 3 === 0 ? 'Despesa recorrente importante para o funcionamento da empresa.' : null,
            ]);
        }

        // Create some recurring transactions (installments)
        $this->createRecurringTransactions($incomeCategories, $expenseCategories, $clients, $projects);
    }

    private function createRecurringTransactions($incomeCategories, $expenseCategories, $clients, $projects): void
    {
        // Monthly subscription expense (3 installments)
        $category = $expenseCategories->where('name', 'Ferramentas e Software')->first() ?? $expenseCategories->first();
        $baseAmount = 299.90;
        
        for ($i = 1; $i <= 3; $i++) {
            FinancialTransaction::create([
                'title' => "Assinatura GitHub Enterprise ({$i}/3)",
                'description' => 'Assinatura mensal do GitHub Enterprise para a equipe',
                'type' => 'expense',
                'amount' => $baseAmount,
                'due_date' => now()->addMonths($i - 1)->startOfMonth()->addDays(4),
                'status' => $i === 1 ? 'paid' : 'pending',
                'payment_date' => $i === 1 ? now()->subDays(5) : null,
                'payment_method' => 'Cartão de Crédito',
                'category_id' => $category->id,
                'recurrence' => 'monthly',
                'installments' => 3,
                'current_installment' => $i,
                'notes' => 'Assinatura parcelada em 3x para facilitar o fluxo de caixa.',
            ]);
        }

        // Quarterly income (4 installments)
        $category = $incomeCategories->where('name', 'Desenvolvimento de Software')->first() ?? $incomeCategories->first();
        $client = $clients->count() > 0 ? $clients->random() : null;
        $project = $projects->count() > 0 ? $projects->random() : null;
        $baseAmount = 12000.00;
        
        for ($i = 1; $i <= 4; $i++) {
            FinancialTransaction::create([
                'title' => "Desenvolvimento Sistema ERP - Parcela {$i}/4",
                'description' => 'Projeto de desenvolvimento de sistema ERP completo',
                'type' => 'income',
                'amount' => $baseAmount,
                'due_date' => now()->addMonths(($i - 1) * 3)->addDays(15),
                'status' => $i <= 2 ? 'paid' : 'pending',
                'payment_date' => $i <= 2 ? now()->subDays(30 - ($i * 10)) : null,
                'payment_method' => 'Transferência Bancária',
                'category_id' => $category->id,
                'client_id' => $client?->id,
                'project_id' => $project?->id,
                'recurrence' => 'quarterly',
                'installments' => 4,
                'current_installment' => $i,
                'notes' => 'Projeto de grande porte com pagamento trimestral.',
            ]);
        }
    }
}
