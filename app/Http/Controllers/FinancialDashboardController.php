<?php

namespace App\Http\Controllers;

use App\Models\FinancialTransaction;
use App\Models\FinancialCategory;
use App\Models\FinancialBudget;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Carbon\Carbon;

class FinancialDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:financial.view|financial.manage')->only(['index']);
        $this->middleware('permission:financial.manage')->only(['export']);
    }
    public function index(Request $request): InertiaResponse
    {
        $period = $request->get('period', 'month'); // month, quarter, year
        $startDate = $this->getStartDate($period);
        $endDate = $this->getEndDate($period);

        // Calculate financial stats
        $stats = $this->calculateFinancialStats($startDate, $endDate);
        
        // Get recent transactions
        $recentTransactions = FinancialTransaction::with(['category', 'client', 'project'])
            ->latest()
            ->take(10)
            ->get();

        // Get overdue transactions
        $overdueTransactions = FinancialTransaction::with(['category', 'client'])
            ->overdue()
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Get upcoming transactions (next 7 days)
        $upcomingTransactions = FinancialTransaction::with(['category', 'client'])
            ->where('status', FinancialTransaction::STATUS_PENDING)
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        // Revenue vs Expenses chart data
        $chartData = $this->getChartData($startDate, $endDate);

        // Categories performance
        $categoriesPerformance = $this->getCategoriesPerformance($startDate, $endDate);

        // Budget alerts
        $budgetAlerts = $this->getBudgetAlerts();

        return Inertia::render('Financial/Dashboard', [
            'stats' => $stats,
            'recentTransactions' => $recentTransactions,
            'overdueTransactions' => $overdueTransactions,
            'upcomingTransactions' => $upcomingTransactions,
            'chartData' => $chartData,
            'categoriesPerformance' => $categoriesPerformance,
            'budgetAlerts' => $budgetAlerts,
            'period' => $period,
            'filters' => [
                'period' => $period,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
            'periodOptions' => [
                'month' => 'Este Mês',
                'quarter' => 'Este Trimestre',
                'year' => 'Este Ano',
            ],
        ]);
    }

    private function calculateFinancialStats(Carbon $startDate, Carbon $endDate): array
    {
        $totalIncome = FinancialTransaction::income()
            ->paid()
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        $totalExpenses = FinancialTransaction::expense()
            ->paid()
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->sum('amount');

        $pendingIncome = FinancialTransaction::income()
            ->pending()
            ->whereBetween('due_date', [$startDate, $endDate])
            ->sum('amount');

        $pendingExpenses = FinancialTransaction::expense()
            ->pending()
            ->whereBetween('due_date', [$startDate, $endDate])
            ->sum('amount');

        $overdueAmount = FinancialTransaction::overdue()
            ->sum('amount');

        $netIncome = $totalIncome - $totalExpenses;

        // Previous period comparison
        $previousStartDate = $startDate->copy()->subDays($endDate->diffInDays($startDate));
        $previousEndDate = $startDate->copy()->subDay();

        $previousIncome = FinancialTransaction::income()
            ->paid()
            ->whereBetween('payment_date', [$previousStartDate, $previousEndDate])
            ->sum('amount');

        $previousExpenses = FinancialTransaction::expense()
            ->paid()
            ->whereBetween('payment_date', [$previousStartDate, $previousEndDate])
            ->sum('amount');

        $incomeGrowth = $previousIncome > 0 ? 
            (($totalIncome - $previousIncome) / $previousIncome) * 100 : 0;

        $expensesGrowth = $previousExpenses > 0 ? 
            (($totalExpenses - $previousExpenses) / $previousExpenses) * 100 : 0;

        return [
            'total_income' => (float) $totalIncome,
            'total_expenses' => (float) $totalExpenses,
            'net_income' => (float) $netIncome,
            'pending_income' => (float) $pendingIncome,
            'pending_expenses' => (float) $pendingExpenses,
            'overdue_amount' => (float) $overdueAmount,
            'income_growth' => round($incomeGrowth, 2),
            'expenses_growth' => round($expensesGrowth, 2),
            'overdue_count' => FinancialTransaction::overdue()->count(),
            'formatted' => [
                'total_income' => 'R$ ' . number_format($totalIncome, 2, ',', '.'),
                'total_expenses' => 'R$ ' . number_format($totalExpenses, 2, ',', '.'),
                'net_income' => 'R$ ' . number_format($netIncome, 2, ',', '.'),
                'pending_income' => 'R$ ' . number_format($pendingIncome, 2, ',', '.'),
                'pending_expenses' => 'R$ ' . number_format($pendingExpenses, 2, ',', '.'),
                'overdue_amount' => 'R$ ' . number_format($overdueAmount, 2, ',', '.'),
            ],
        ];
    }

    private function getChartData(Carbon $startDate, Carbon $endDate): array
    {
        $data = [];
        $current = $startDate->copy();

        while ($current <= $endDate) {
            $nextPeriod = $current->copy()->addDays(7);
            
            $income = FinancialTransaction::income()
                ->paid()
                ->whereBetween('payment_date', [$current, min($nextPeriod, $endDate)])
                ->sum('amount');

            $expenses = FinancialTransaction::expense()
                ->paid()
                ->whereBetween('payment_date', [$current, min($nextPeriod, $endDate)])
                ->sum('amount');

            $data[] = [
                'period' => $current->format('d/m'),
                'income' => (float) $income,
                'expenses' => (float) $expenses,
            ];

            $current = $nextPeriod;
        }

        return $data;
    }

    private function getCategoriesPerformance(Carbon $startDate, Carbon $endDate): array
    {
        $categories = FinancialCategory::with(['transactions' => function($query) use ($startDate, $endDate) {
            $query->paid()
                  ->whereBetween('payment_date', [$startDate, $endDate]);
        }])->get();

        return $categories->map(function($category) {
            $total = $category->transactions->sum('amount');
            return [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
                'color' => $category->color,
                'icon' => $category->icon,
                'total' => (float) $total,
                'formatted_total' => 'R$ ' . number_format($total, 2, ',', '.'),
                'transactions_count' => $category->transactions->count(),
            ];
        })->sortByDesc('total')->values()->toArray();
    }

    private function getBudgetAlerts(): array
    {
        $budgets = FinancialBudget::with('category')
            ->active()
            ->current()
            ->get();

        $alerts = [];

        foreach ($budgets as $budget) {
            $budget->updateSpentAmount();
            
            if ($budget->shouldAlert90Percent()) {
                $alerts[] = [
                    'type' => 'warning',
                    'budget_id' => $budget->id,
                    'title' => 'Orçamento próximo do limite',
                    'message' => "O orçamento '{$budget->name}' atingiu {$budget->progress_percentage}% do valor planejado.",
                    'percentage' => $budget->progress_percentage,
                ];
            }

            if ($budget->shouldAlert100Percent()) {
                $alerts[] = [
                    'type' => 'error',
                    'budget_id' => $budget->id,
                    'title' => 'Orçamento excedido',
                    'message' => "O orçamento '{$budget->name}' foi excedido em R$ " . 
                              number_format($budget->spent - $budget->amount, 2, ',', '.'),
                    'percentage' => $budget->progress_percentage,
                ];
            }
        }

        return $alerts;
    }

    private function getStartDate(string $period): Carbon
    {
        return match($period) {
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    private function getEndDate(string $period): Carbon
    {
        return match($period) {
            'month' => now()->endOfMonth(),
            'quarter' => now()->endOfQuarter(),
            'year' => now()->endOfYear(),
            default => now()->endOfMonth(),
        };
    }

    public function export(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = $this->getEndDate($period);

        $transactions = FinancialTransaction::with(['category', 'client', 'project'])
            ->whereBetween('due_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get();

        $csv = "Título,Tipo,Categoria,Valor,Vencimento,Status,Cliente,Projeto,Método de Pagamento\n";

        foreach ($transactions as $transaction) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $transaction->title,
                $transaction->type_label,
                $transaction->category->name ?? '',
                number_format((float) $transaction->amount, 2, ',', '.'),
                Carbon::parse($transaction->due_date)->format('d/m/Y'),
                $transaction->status_label,
                $transaction->client->name ?? '',
                $transaction->project->name ?? '',
                $transaction->payment_method ?? ''
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="relatorio_financeiro_' . 
                     $period . '_' . date('Y-m-d_H-i-s') . '.csv"');
    }
}
