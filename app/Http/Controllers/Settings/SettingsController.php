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
            'settings' => [
                'theme' => $settings->theme,
                'language' => $settings->language,
                'timezone' => $settings->timezone,
                'date_format' => $settings->date_format,
                'time_format' => $settings->time_format,
                'email_notifications' => $settings->email_notifications,
                'browser_notifications' => $settings->browser_notifications,
                'task_reminders' => $settings->task_reminders,
                'project_updates' => $settings->project_updates,
            ],
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
            'theme' => ['required', 'string', 'in:light,dark,system'],
            'language' => ['required', 'string', 'in:pt-BR,en-US,es-ES'],
            'timezone' => ['required', 'string'],
            'date_format' => ['nullable', 'string', 'in:d/m/Y,m/d/Y,Y-m-d'],
            'time_format' => ['nullable', 'string', 'in:H:i,h:i A'],
            'email_notifications' => ['boolean'],
            'browser_notifications' => ['boolean'],
            'task_reminders' => ['boolean'],
            'project_updates' => ['boolean'],
        ]);

        $user = $request->user();
        $settings = $user->getOrCreateSettings();
        
        // Definir valores padrão se não fornecidos
        $validated['date_format'] = $validated['date_format'] ?? 'd/m/Y';
        $validated['time_format'] = $validated['time_format'] ?? 'H:i';
        
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
