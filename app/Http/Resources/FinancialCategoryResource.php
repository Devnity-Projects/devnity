<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinancialCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->getTypeLabel(),
            'color' => $this->color,
            'icon' => $this->icon,
            'is_active' => $this->is_active,
            'status_label' => $this->is_active ? 'Ativo' : 'Inativo',
            'transactions_count' => $this->whenLoaded('transactions', fn() => $this->transactions->count()),
            'total_amount' => $this->whenLoaded('transactions', fn() => $this->getTotalTransactions()),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at_formatted' => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }
}
