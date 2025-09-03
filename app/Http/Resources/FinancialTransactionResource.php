<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FinancialTransactionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'amount' => (float) $this->amount,
            'formatted_amount' => $this->formatted_amount,
            'due_date' => $this->due_date?->toDateString(),
            'due_date_formatted' => $this->due_date?->format('d/m/Y'),
            'payment_date' => $this->payment_date?->toDateString(),
            'payment_date_formatted' => $this->payment_date?->format('d/m/Y'),
            'status' => $this->status,
            'status_label' => $this->status_label,
            'recurrence' => $this->recurrence,
            'recurrence_label' => $this->recurrence_label,
            'installments' => $this->installments,
            'current_installment' => $this->current_installment,
            'installment_info' => $this->installment_info,
            'payment_method' => $this->payment_method,
            'notes' => $this->notes,
            'attachments' => $this->attachments,
            'is_overdue' => $this->isOverdue(),
            'days_until_due' => $this->getDaysUntilDue(),
            'days_overdue' => $this->getDaysOverdue(),
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                    'type' => $this->category->type,
                    'color' => $this->category->color,
                    'icon' => $this->category->icon,
                ];
            }),
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                    'type' => $this->client->type,
                ];
            }),
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'status' => $this->project->status,
                ];
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at_formatted' => $this->updated_at?->format('d/m/Y H:i'),
        ];
    }
}
