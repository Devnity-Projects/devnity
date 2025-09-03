<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FinancialBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'spent',
        'start_date',
        'end_date',
        'period',
        'status',
        'category_id',
        'alert_on_90_percent',
        'alert_on_100_percent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'alert_on_90_percent' => 'boolean',
        'alert_on_100_percent' => 'boolean',
    ];

    // Constants
    const PERIOD_MONTHLY = 'monthly';
    const PERIOD_QUARTERLY = 'quarterly';
    const PERIOD_BIANNUAL = 'biannual';
    const PERIOD_YEARLY = 'yearly';
    const PERIOD_CUSTOM = 'custom';

    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now())
                    ->where('status', self::STATUS_ACTIVE);
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(FinancialCategory::class, 'category_id');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'Ativo',
            self::STATUS_COMPLETED => 'Concluído',
            self::STATUS_CANCELLED => 'Cancelado',
            default => 'Indefinido'
        };
    }

    public function getPeriodLabelAttribute(): string
    {
        return match($this->period) {
            self::PERIOD_MONTHLY => 'Mensal',
            self::PERIOD_QUARTERLY => 'Trimestral',
            self::PERIOD_BIANNUAL => 'Semestral',
            self::PERIOD_YEARLY => 'Anual',
            self::PERIOD_CUSTOM => 'Personalizado',
            default => 'Personalizado'
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->amount, 2, ',', '.');
    }

    public function getFormattedSpentAttribute(): string
    {
        return 'R$ ' . number_format((float) $this->spent, 2, ',', '.');
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->amount - (float) $this->spent);
    }

    public function getFormattedRemainingAttribute(): string
    {
        return 'R$ ' . number_format($this->remaining_amount, 2, ',', '.');
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }
        return min(100, ((float) $this->spent / (float) $this->amount) * 100);
    }

    public function getIsOverBudgetAttribute(): bool
    {
        return (float) $this->spent > (float) $this->amount;
    }

    public function getIsNearLimitAttribute(): bool
    {
        return $this->progress_percentage >= 90 && !$this->is_over_budget;
    }

    // Methods
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isCurrent(): bool
    {
        return $this->start_date <= now() && 
               $this->end_date >= now() && 
               $this->isActive();
    }

    public function updateSpentAmount(): void
    {
        $transactions = FinancialTransaction::where('category_id', $this->category_id)
            ->where('type', FinancialTransaction::TYPE_EXPENSE)
            ->where('status', FinancialTransaction::STATUS_PAID)
            ->whereBetween('payment_date', [$this->start_date, $this->end_date])
            ->sum('amount');

        $this->update(['spent' => $transactions]);
    }

    public function addExpense(float $amount): void
    {
        $this->increment('spent', $amount);
    }

    public function removeExpense(float $amount): void
    {
        $this->decrement('spent', $amount);
    }

    public function complete(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    public function shouldAlert90Percent(): bool
    {
        return $this->alert_on_90_percent && 
               $this->progress_percentage >= 90 && 
               $this->progress_percentage < 100 &&
               $this->isActive();
    }

    public function shouldAlert100Percent(): bool
    {
        return $this->alert_on_100_percent && 
               $this->is_over_budget &&
               $this->isActive();
    }

    public function getDaysRemaining(): int
    {
        if ($this->end_date < now()) {
            return 0;
        }
        return Carbon::parse($this->end_date)->diffInDays(now());
    }
}
