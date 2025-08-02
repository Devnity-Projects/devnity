<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Show the user's general settings page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $settings = $user->getOrCreateSettings();
        
        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
            ],
        ]);
    }

    /**
     * Update the user's settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'string', 'in:light,dark'],
            'language' => ['required', 'string', 'in:pt-BR,en-US'],
            'email_notifications' => ['boolean'],
            'browser_notifications' => ['boolean'],
            'task_reminders' => ['boolean'],
            'project_updates' => ['boolean'],
            'timezone' => ['required', 'string'],
            'date_format' => ['required', 'string', 'in:d/m/Y,m/d/Y,Y-m-d'],
            'time_format' => ['required', 'string', 'in:H:i,h:i A'],
        ]);

        $user = $request->user();
        $settings = $user->getOrCreateSettings();
        
        $settings->update($validated);

        return back()->with('status', 'Configurações atualizadas com sucesso!');
    }

    /**
     * Reset settings to default values.
     */
    public function reset(Request $request): RedirectResponse
    {
        $user = $request->user();
        $settings = $user->getOrCreateSettings();
        
        $settings->update(\App\Models\UserSettings::getDefaultSettings());

        return back()->with('status', 'Configurações resetadas para o padrão!');
    }
}
