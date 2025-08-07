<?php

namespace App\Http\Controllers;

use App\Models\SupportCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportCategoryController extends Controller
{
    public function index()
    {
        $categories = SupportCategory::withCount('tickets')->get();
        
        return Inertia::render('Support/Categories/Index', [
            'categories' => $categories
        ]);
    }

    public function create()
    {
        return Inertia::render('Support/Categories/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean'
        ]);

        $category = SupportCategory::create($validated);

        return redirect()->route('support.categories.index')
            ->with('success', 'Categoria criada com sucesso!');
    }

    public function edit(SupportCategory $category)
    {
        return Inertia::render('Support/Categories/Edit', [
            'category' => $category
        ]);
    }

    public function update(Request $request, SupportCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean'
        ]);

        $category->update($validated);

        return redirect()->route('support.categories.index')
            ->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(SupportCategory $category)
    {
        if ($category->tickets()->exists()) {
            return back()->with('error', 'Não é possível excluir uma categoria que possui tickets associados.');
        }

        $category->delete();

        return back()->with('success', 'Categoria excluída com sucesso!');
    }
}
