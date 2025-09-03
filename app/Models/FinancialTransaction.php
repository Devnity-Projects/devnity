<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'amount',
        'due_date',
        'payment_date',
        'status',
        'recurrence',
        'installments',
        'current_installment',
        'category_id',
        'client_id',
        'project_id',
        'payment_method',
        'notes',
        'attachments',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'payment_date' => 'date',
        'attachments' => 'array',
        'installments' => 'integer',
        'current_installment' => 'integer',
    ];

    // Constants
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    const RECURRENCE_NONE = 'none';
    const RECURRENCE_MONTHLY = 'monthly';
    const RECURRENCE_QUARTERLY = 'quarterly';
    const RECURRENCE_BIANNUAL = 'biannual';
    const RECURRENCE_YEARLY = 'yearly';

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', self::TYPE_INCOME);
    }

    public function scopeExpense($query)
    {
        return $query->where('type', self::TYPE_EXPENSE);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
                    ->orWhere(function($q) {
                        $q->where('status', self::STATUS_PENDING)
                          ->where('due_date', '<', now());
                    });
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('due_date', [$startDate, $endDate]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('due_date', now()->month)
                    ->whereYear('due_date', now()->year);
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function($q) use ($term) {
            $q->where('title', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%")
              ->orWhere('notes', 'like', "%{$term}%");
        });
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'category_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendente',
            self::STATUS_PAID => 'Pago',
            self::STATUS_OVERDUE => 'Vencido',
            self::STATUS_CANCELLED => 'Cancelado',
            default => 'Indefinido'
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            self::TYPE_INCOME => 'Receita',
            self::TYPE_EXPENSE => 'Despesa',
            default => 'Indefinido'
        };
    }

    public function getRecurrenceLabelAttribute(): string
    {
        return match($this->recurrence) {
            self::RECURRENCE_NONE => 'Única',
            self::RECURRENCE_MONTHLY => 'Mensal',
            self::RECURRENCE_QUARTERLY => 'Trimestral',
            self::RECURRENCE_BIANNUAL => 'Semestral',
            self::RECURRENCE_YEARLY => 'Anual',
            default => 'Única'
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->amount, 2, ',', '.');
    }

    public function getInstallmentInfoAttribute(): string
    {
        if ($this->installments <= 1) {
            return 'Parcela única';
        }
        return "{$this->current_installment}/{$this->installments}";
    }

    // Methods
    public function isIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE || 
               ($this->status === self::STATUS_PENDING && $this->due_date < now());
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function markAsPaid(Carbon $paymentDate = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'payment_date' => $paymentDate ?? now()
        ]);
    }

    public function markAsOverdue(): void
    {
        if ($this->isPending() && $this->due_date < now()) {
            $this->update(['status' => self::STATUS_OVERDUE]);
        }
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    public function getDaysUntilDue(): int
    {
        return Carbon::parse($this->due_date)->diffInDays(now(), false);
    }

    public function getDaysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays(Carbon::parse($this->due_date));
    }
}
