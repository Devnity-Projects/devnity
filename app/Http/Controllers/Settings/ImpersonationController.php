<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating a target user (only allowed for users who can manage users).
     */
    public function start(Request $request): RedirectResponse
    {
        abort_unless($request->user()->can('manage users'), 403);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        // Prevent nested impersonation
        if ($request->session()->has('impersonator_id')) {
            return back()->with('warning', 'Você já está visualizando como outro usuário.');
        }

        $target = User::findOrFail($data['user_id']);

        // Store the original admin id and switch auth to target
    $request->session()->put('impersonator_id', $request->user()->id);
    Auth::login($target);

        return redirect()->route('dashboard')->with('success', 'Agora você está visualizando como '.$target->name);
    }

    /**
     * Stop impersonation and return to the original admin.
     */
    public function stop(Request $request): RedirectResponse
    {
        $impersonatorId = $request->session()->pull('impersonator_id');
        if ($impersonatorId) {
            $admin = User::find($impersonatorId);
            if ($admin) {
                Auth::login($admin);
            }
            return redirect()->route('settings.permissions')->with('success', 'Você voltou para sua sessão de administrador.');
        }

        return back()->with('warning', 'Nenhuma sessão de visualização ativa.');
    }
}
