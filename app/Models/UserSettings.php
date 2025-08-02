<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSettings extends Model
{
    protected $fillable = [
        'user_id',
        'theme',
        'language',
        'email_notifications',
        'browser_notifications',
        'task_reminders',
        'project_updates',
        'dashboard_layout',
        'timezone',
        'date_format',
        'time_format',
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'browser_notifications' => 'boolean',
        'task_reminders' => 'boolean',
        'project_updates' => 'boolean',
        'dashboard_layout' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getDefaultSettings(): array
    {
        return [
            'theme' => 'light',
            'language' => 'pt-BR',
            'email_notifications' => true,
            'browser_notifications' => true,
            'task_reminders' => true,
            'project_updates' => true,
            'timezone' => 'America/Sao_Paulo',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
        ];
    }
}
