<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinancialTransactionRequest;
use App\Http\Resources\FinancialTransactionResource;
use App\Models\FinancialTransaction;
use App\Models\FinancialCategory;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Carbon\Carbon;

class FinancialTransactionController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = FinancialTransaction::with(['category', 'client', 'project']);

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'overdue') {
                $query->overdue();
            } else {
                $query->byStatus($request->status);
            }
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Client filter
        if ($request->filled('client')) {
            $query->where('client_id', $request->client);
        }

        // Project filter
        if ($request->filled('project')) {
            $query->where('project_id', $request->project);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('due_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Get filter options
        $categories = FinancialCategory::active()->orderBy('name')->get();
        $clients = Client::active()->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        // Calculate stats
        $stats = [
            'total_income' => FinancialTransaction::income()->paid()->sum('amount'),
            'total_expenses' => FinancialTransaction::expense()->paid()->sum('amount'),
            'pending_income' => FinancialTransaction::income()->pending()->sum('amount'),
            'pending_expenses' => FinancialTransaction::expense()->pending()->sum('amount'),
            'overdue_count' => FinancialTransaction::overdue()->count(),
        ];

        return Inertia::render('Financial/Transactions/Index', [
            'transactions' => FinancialTransactionResource::collection($transactions),
            'categories' => $categories,
            'clients' => $clients,
            'projects' => $projects,
            'stats' => $stats,
            'filters' => $request->only([
                'search', 'type', 'status', 'category', 'client', 'project', 
                'date_from', 'date_to'
            ]),
            'typeOptions' => [
                FinancialTransaction::TYPE_INCOME => 'Receita',
                FinancialTransaction::TYPE_EXPENSE => 'Despesa',
            ],
            'statusOptions' => [
                FinancialTransaction::STATUS_PENDING => 'Pendente',
                FinancialTransaction::STATUS_PAID => 'Pago',
                FinancialTransaction::STATUS_OVERDUE => 'Vencido',
                FinancialTransaction::STATUS_CANCELLED => 'Cancelado',
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        $categories = FinancialCategory::active()->orderBy('name')->get();
        $clients = Client::active()->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return Inertia::render('Financial/Transactions/Create', [
            'categories' => $categories,
            'clients' => $clients,
            'projects' => $projects,
            'typeOptions' => [
                FinancialTransaction::TYPE_INCOME => 'Receita',
                FinancialTransaction::TYPE_EXPENSE => 'Despesa',
            ],
            'recurrenceOptions' => [
                FinancialTransaction::RECURRENCE_NONE => 'Única',
                FinancialTransaction::RECURRENCE_MONTHLY => 'Mensal',
                FinancialTransaction::RECURRENCE_QUARTERLY => 'Trimestral',
                FinancialTransaction::RECURRENCE_BIANNUAL => 'Semestral',
                FinancialTransaction::RECURRENCE_YEARLY => 'Anual',
            ],
        ]);
    }

    public function store(FinancialTransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        // Create main transaction
        $transaction = FinancialTransaction::create($data);

        // Handle installments
        if ($data['installments'] > 1) {
            $this->createInstallments($transaction, $data);
        }

        return Redirect::route('financial.transactions.index')
            ->with('success', 'Transação criada com sucesso!');
    }

    public function show(FinancialTransaction $transaction): InertiaResponse
    {
        $transaction->load(['category', 'client', 'project']);

        return Inertia::render('Financial/Transactions/Show', [
            'transaction' => new FinancialTransactionResource($transaction),
        ]);
    }

    public function edit(FinancialTransaction $transaction): InertiaResponse
    {
        $categories = FinancialCategory::active()->orderBy('name')->get();
        $clients = Client::active()->orderBy('name')->get();
        $projects = Project::orderBy('name')->get();

        return Inertia::render('Financial/Transactions/Edit', [
            'transaction' => new FinancialTransactionResource($transaction),
            'categories' => $categories,
            'clients' => $clients,
            'projects' => $projects,
            'typeOptions' => [
                FinancialTransaction::TYPE_INCOME => 'Receita',
                FinancialTransaction::TYPE_EXPENSE => 'Despesa',
            ],
            'statusOptions' => [
                FinancialTransaction::STATUS_PENDING => 'Pendente',
                FinancialTransaction::STATUS_PAID => 'Pago',
                FinancialTransaction::STATUS_OVERDUE => 'Vencido',
                FinancialTransaction::STATUS_CANCELLED => 'Cancelado',
            ],
        ]);
    }

    public function update(FinancialTransactionRequest $request, FinancialTransaction $transaction): RedirectResponse
    {
        $transaction->update($request->validated());

        return Redirect::route('financial.transactions.index')
            ->with('success', 'Transação atualizada com sucesso!');
    }

    public function destroy(FinancialTransaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return Redirect::route('financial.transactions.index')
            ->with('success', 'Transação excluída com sucesso!');
    }

    public function markAsPaid(FinancialTransaction $transaction, Request $request): RedirectResponse
    {
        $request->validate([
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $paymentDate = $request->payment_date ? 
            Carbon::parse($request->payment_date) : 
            now();

        $transaction->update([
            'status' => FinancialTransaction::STATUS_PAID,
            'payment_date' => $paymentDate,
            'payment_method' => $request->payment_method ?? $transaction->payment_method,
        ]);

        return Redirect::back()
            ->with('success', 'Transação marcada como paga!');
    }

    public function markAsPending(FinancialTransaction $transaction): RedirectResponse
    {
        $transaction->update([
            'status' => FinancialTransaction::STATUS_PENDING,
            'payment_date' => null,
        ]);

        return Redirect::back()
            ->with('success', 'Transação marcada como pendente!');
    }

    public function cancel(FinancialTransaction $transaction): RedirectResponse
    {
        $transaction->cancel();

        return Redirect::back()
            ->with('success', 'Transação cancelada!');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:financial_transactions,id'
        ]);

        $count = FinancialTransaction::whereIn('id', $request->ids)->delete();

        return Redirect::back()
            ->with('success', "{$count} transações excluídas com sucesso!");
    }

    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:financial_transactions,id',
            'status' => 'required|in:' . implode(',', [
                FinancialTransaction::STATUS_PENDING,
                FinancialTransaction::STATUS_PAID,
                FinancialTransaction::STATUS_CANCELLED,
            ]),
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string|max:255',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === FinancialTransaction::STATUS_PAID) {
            $updateData['payment_date'] = $request->payment_date ?? now();
            if ($request->payment_method) {
                $updateData['payment_method'] = $request->payment_method;
            }
        } elseif ($request->status === FinancialTransaction::STATUS_PENDING) {
            $updateData['payment_date'] = null;
        }

        $count = FinancialTransaction::whereIn('id', $request->ids)
            ->update($updateData);

        $statusLabel = match($request->status) {
            FinancialTransaction::STATUS_PENDING => 'pendentes',
            FinancialTransaction::STATUS_PAID => 'pagas',
            FinancialTransaction::STATUS_CANCELLED => 'canceladas',
            default => 'atualizadas'
        };

        return Redirect::back()
            ->with('success', "{$count} transações marcadas como {$statusLabel}!");
    }

    private function createInstallments(FinancialTransaction $transaction, array $data): void
    {
        $installmentAmount = $data['amount'] / $data['installments'];
        $dueDate = Carbon::parse($data['due_date']);

        for ($i = 2; $i <= $data['installments']; $i++) {
            // Calculate next due date based on recurrence
            $nextDueDate = match($data['recurrence']) {
                FinancialTransaction::RECURRENCE_MONTHLY => $dueDate->copy()->addMonths($i - 1),
                FinancialTransaction::RECURRENCE_QUARTERLY => $dueDate->copy()->addMonths(($i - 1) * 3),
                FinancialTransaction::RECURRENCE_BIANNUAL => $dueDate->copy()->addMonths(($i - 1) * 6),
                FinancialTransaction::RECURRENCE_YEARLY => $dueDate->copy()->addYears($i - 1),
                default => $dueDate->copy()->addMonths($i - 1), // Default to monthly
            };

            FinancialTransaction::create([
                ...$data,
                'title' => $data['title'] . " ({$i}/{$data['installments']})",
                'amount' => $installmentAmount,
                'due_date' => $nextDueDate,
                'current_installment' => $i,
            ]);
        }

        // Update the original transaction
        $transaction->update([
            'title' => $data['title'] . " (1/{$data['installments']})",
            'amount' => $installmentAmount,
        ]);
    }
}
