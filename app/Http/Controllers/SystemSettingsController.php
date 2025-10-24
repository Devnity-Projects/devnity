<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class SystemSettingsController extends Controller
{
    public function index(): InertiaResponse
    {
        // Apenas admins podem acessar
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $menuVisibility = SystemSetting::get('menu_visibility', [
            'tasks' => true,
            'projects' => true,
            'clients' => true,
            'financial' => false,
            'support' => false,
        ]);

        return Inertia::render('Settings/SystemSettings', [
            'menuVisibility' => $menuVisibility,
        ]);
    }

    public function updateMenuVisibility(Request $request): RedirectResponse
    {
        // Apenas admins podem atualizar
        abort_unless(auth()->user()->hasRole('admin'), 403);

        $validated = $request->validate([
            'tasks' => 'required|boolean',
            'projects' => 'required|boolean',
            'clients' => 'required|boolean',
            'financial' => 'required|boolean',
            'support' => 'required|boolean',
        ]);

        SystemSetting::set('menu_visibility', $validated, 'json', 'Controla a visibilidade dos itens do menu principal');

        return redirect()->back()->with('success', 'Configurações de menu atualizadas com sucesso!');
    }
}
