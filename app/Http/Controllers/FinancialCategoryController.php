<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinancialCategoryRequest;
use App\Http\Resources\FinancialCategoryResource;
use App\Models\FinancialCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class FinancialCategoryController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = FinancialCategory::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Type filter
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        $categories = $query->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Financial/Categories/Index', [
            'categories' => FinancialCategoryResource::collection($categories),
            'filters' => $request->only(['search', 'type', 'status']),
            'typeOptions' => [
                FinancialCategory::TYPE_INCOME => 'Receita',
                FinancialCategory::TYPE_EXPENSE => 'Despesa',
            ],
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Financial/Categories/Create', [
            'typeOptions' => [
                FinancialCategory::TYPE_INCOME => 'Receita',
                FinancialCategory::TYPE_EXPENSE => 'Despesa',
            ],
        ]);
    }

    public function store(FinancialCategoryRequest $request): RedirectResponse
    {
        FinancialCategory::create($request->validated());

        return Redirect::route('financial.categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function show(FinancialCategory $category): InertiaResponse
    {
        $category->load(['transactions' => function($query) {
            $query->latest()->take(10);
        }]);

        return Inertia::render('Financial/Categories/Show', [
            'category' => new FinancialCategoryResource($category),
            'stats' => [
                'total_transactions' => $category->transactions()->count(),
                'total_amount' => $category->getTotalTransactions(),
                'pending_amount' => $category->transactions()
                    ->where('status', 'pending')
                    ->sum('amount'),
                'paid_amount' => $category->transactions()
                    ->where('status', 'paid')
                    ->sum('amount'),
            ],
        ]);
    }

    public function edit(FinancialCategory $category): InertiaResponse
    {
        return Inertia::render('Financial/Categories/Edit', [
            'category' => new FinancialCategoryResource($category),
            'typeOptions' => [
                FinancialCategory::TYPE_INCOME => 'Receita',
                FinancialCategory::TYPE_EXPENSE => 'Despesa',
            ],
        ]);
    }

    public function update(FinancialCategoryRequest $request, FinancialCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return Redirect::route('financial.categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(FinancialCategory $category): RedirectResponse
    {
        if ($category->transactions()->exists()) {
            return Redirect::back()
                ->with('error', 'Não é possível excluir uma categoria que possui transações associadas.');
        }

        $category->delete();

        return Redirect::route('financial.categories.index')
            ->with('success', 'Categoria excluída com sucesso!');
    }

    public function toggleStatus(FinancialCategory $category): RedirectResponse
    {
        $category->update(['is_active' => !$category->is_active]);

        $status = $category->is_active ? 'ativada' : 'desativada';
        
        return Redirect::back()
            ->with('success', "Categoria {$status} com sucesso!");
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:financial_categories,id'
        ]);

        $categories = FinancialCategory::whereIn('id', $request->ids);
        
        // Check if any category has transactions
        $categoriesWithTransactions = $categories->whereHas('transactions')->count();
        
        if ($categoriesWithTransactions > 0) {
            return Redirect::back()
                ->with('error', 'Não é possível excluir categorias que possuem transações associadas.');
        }

        $count = $categories->count();
        $categories->delete();

        return Redirect::back()
            ->with('success', "{$count} categorias excluídas com sucesso!");
    }

    public function bulkToggleStatus(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:financial_categories,id',
            'status' => 'required|boolean'
        ]);

        $count = FinancialCategory::whereIn('id', $request->ids)
            ->update(['is_active' => $request->status]);

        $action = $request->status ? 'ativadas' : 'desativadas';

        return Redirect::back()
            ->with('success', "{$count} categorias {$action} com sucesso!");
    }
}
