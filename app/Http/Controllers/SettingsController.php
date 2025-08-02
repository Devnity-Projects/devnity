<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    /**
     * Show the user's settings page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $settings = $user->getOrCreateSettings();
        
        return Inertia::render('Settings/Index', [
            'settings' => $settings,
            'timezones' => $this->getTimezones(),
            'languages' => $this->getLanguages(),
            'themes' => $this->getThemes(),
            'dateFormats' => $this->getDateFormats(),
            'timeFormats' => $this->getTimeFormats(),
        ]);
    }

    /**
     * Update the user's settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'theme' => ['required', 'in:light,dark'],
            'language' => ['required', 'in:pt-BR,en-US'],
            'email_notifications' => ['boolean'],
            'browser_notifications' => ['boolean'],
            'task_reminders' => ['boolean'],
            'project_updates' => ['boolean'],
            'timezone' => ['required', 'string'],
            'date_format' => ['required', 'in:d/m/Y,m/d/Y,Y-m-d'],
            'time_format' => ['required', 'in:H:i,h:i A'],
        ]);

        $user = $request->user();
        $settings = $user->getOrCreateSettings();
        
        $settings->update($validated);

        return back()->with('status', 'settings-updated');
    }

    /**
     * Reset settings to defaults.
     */
    public function reset(Request $request): RedirectResponse
    {
        $user = $request->user();
        $settings = $user->getOrCreateSettings();
        
        $settings->update(\App\Models\UserSettings::getDefaultSettings());

        return back()->with('status', 'settings-reset');
    }

    private function getTimezones(): array
    {
        return [
            'America/Sao_Paulo' => 'São Paulo (UTC-3)',
            'America/New_York' => 'Nova York (UTC-5)',
            'Europe/London' => 'Londres (UTC+0)',
            'Europe/Paris' => 'Paris (UTC+1)',
            'Asia/Tokyo' => 'Tóquio (UTC+9)',
        ];
    }

    private function getLanguages(): array
    {
        return [
            'pt-BR' => 'Português (Brasil)',
            'en-US' => 'English (United States)',
        ];
    }

    private function getThemes(): array
    {
        return [
            'light' => 'Claro',
            'dark' => 'Escuro',
        ];
    }

    private function getDateFormats(): array
    {
        return [
            'd/m/Y' => 'DD/MM/AAAA',
            'm/d/Y' => 'MM/DD/AAAA',
            'Y-m-d' => 'AAAA-MM-DD',
        ];
    }

    private function getTimeFormats(): array
    {
        return [
            'H:i' => '24 horas (14:30)',
            'h:i A' => '12 horas (02:30 PM)',
        ];
    }
}
