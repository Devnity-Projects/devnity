<?php

namespace App\Http\Requests;

use App\Models\FinancialTransaction;
use Illuminate\Foundation\Http\FormRequest;

class FinancialTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'type' => ['required', 'in:' . implode(',', [
                FinancialTransaction::TYPE_INCOME,
                FinancialTransaction::TYPE_EXPENSE,
            ])],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'due_date' => ['required', 'date'],
            'payment_date' => ['nullable', 'date'],
            'status' => ['nullable', 'in:' . implode(',', [
                FinancialTransaction::STATUS_PENDING,
                FinancialTransaction::STATUS_PAID,
                FinancialTransaction::STATUS_OVERDUE,
                FinancialTransaction::STATUS_CANCELLED,
            ])],
            'recurrence' => ['required', 'in:' . implode(',', [
                FinancialTransaction::RECURRENCE_NONE,
                FinancialTransaction::RECURRENCE_MONTHLY,
                FinancialTransaction::RECURRENCE_QUARTERLY,
                FinancialTransaction::RECURRENCE_BIANNUAL,
                FinancialTransaction::RECURRENCE_YEARLY,
            ])],
            'installments' => ['required', 'integer', 'min:1', 'max:360'],
            'current_installment' => ['nullable', 'integer', 'min:1'],
            'category_id' => ['required', 'exists:financial_categories,id'],
            'client_id' => ['nullable', 'exists:clients,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'O título é obrigatório.',
            'title.string' => 'O título deve ser um texto válido.',
            'title.max' => 'O título não pode ter mais de 255 caracteres.',
            'description.string' => 'A descrição deve ser um texto válido.',
            'description.max' => 'A descrição não pode ter mais de 2000 caracteres.',
            'type.required' => 'O tipo é obrigatório.',
            'type.in' => 'O tipo selecionado é inválido.',
            'amount.required' => 'O valor é obrigatório.',
            'amount.numeric' => 'O valor deve ser um número.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'amount.max' => 'O valor é muito alto.',
            'due_date.required' => 'A data de vencimento é obrigatória.',
            'due_date.date' => 'A data de vencimento deve ser uma data válida.',
            'payment_date.date' => 'A data de pagamento deve ser uma data válida.',
            'status.in' => 'O status selecionado é inválido.',
            'recurrence.required' => 'A recorrência é obrigatória.',
            'recurrence.in' => 'A recorrência selecionada é inválida.',
            'installments.required' => 'O número de parcelas é obrigatório.',
            'installments.integer' => 'O número de parcelas deve ser um número inteiro.',
            'installments.min' => 'Deve ter pelo menos 1 parcela.',
            'installments.max' => 'Não pode ter mais de 360 parcelas.',
            'current_installment.integer' => 'A parcela atual deve ser um número inteiro.',
            'current_installment.min' => 'A parcela atual deve ser pelo menos 1.',
            'category_id.required' => 'A categoria é obrigatória.',
            'category_id.exists' => 'A categoria selecionada não existe.',
            'client_id.exists' => 'O cliente selecionado não existe.',
            'project_id.exists' => 'O projeto selecionado não existe.',
            'payment_method.string' => 'O método de pagamento deve ser um texto válido.',
            'payment_method.max' => 'O método de pagamento não pode ter mais de 255 caracteres.',
            'notes.string' => 'As observações devem ser um texto válido.',
            'notes.max' => 'As observações não podem ter mais de 2000 caracteres.',
            'attachments.array' => 'Os anexos devem ser uma lista.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Set default values
        if (!$this->has('status')) {
            $this->merge(['status' => FinancialTransaction::STATUS_PENDING]);
        }

        if (!$this->has('current_installment')) {
            $this->merge(['current_installment' => 1]);
        }

        if (!$this->has('recurrence')) {
            $this->merge(['recurrence' => FinancialTransaction::RECURRENCE_NONE]);
        }

        if (!$this->has('installments')) {
            $this->merge(['installments' => 1]);
        }

        // Convert amount to decimal format
        if ($this->has('amount') && is_string($this->amount)) {
            $amount = str_replace(',', '.', str_replace('.', '', $this->amount));
            $this->merge(['amount' => (float) $amount]);
        }
    }
}
