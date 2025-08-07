<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'description',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Activity types
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_ASSIGNED = 'assigned';
    public const ACTION_UNASSIGNED = 'unassigned';
    public const ACTION_COMMENT_ADDED = 'comment_added';
    public const ACTION_ATTACHMENT_ADDED = 'attachment_added';
    public const ACTION_ATTACHMENT_REMOVED = 'attachment_removed';
    public const ACTION_CHECKLIST_ADDED = 'checklist_added';
    public const ACTION_CHECKLIST_COMPLETED = 'checklist_completed';
    public const ACTION_CHECKLIST_UNCOMPLETED = 'checklist_uncompleted';

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            self::ACTION_CREATED => 'criou a tarefa',
            self::ACTION_UPDATED => 'atualizou a tarefa',
            self::ACTION_STATUS_CHANGED => 'alterou o status',
            self::ACTION_ASSIGNED => 'atribuiu a tarefa',
            self::ACTION_UNASSIGNED => 'removeu a atribuição',
            self::ACTION_COMMENT_ADDED => 'adicionou um comentário',
            self::ACTION_ATTACHMENT_ADDED => 'adicionou um anexo',
            self::ACTION_ATTACHMENT_REMOVED => 'removeu um anexo',
            self::ACTION_CHECKLIST_ADDED => 'adicionou um item à checklist',
            self::ACTION_CHECKLIST_COMPLETED => 'marcou um item como concluído',
            self::ACTION_CHECKLIST_UNCOMPLETED => 'desmarcou um item da checklist',
            default => $this->action,
        };
    }
}
