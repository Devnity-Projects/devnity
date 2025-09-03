<?php

namespace App\Http\Requests;

use App\Models\FinancialCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FinancialCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('financial_categories', 'name')
                    ->ignore($categoryId)
                    ->where('type', $this->type)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'in:' . implode(',', [
                FinancialCategory::TYPE_INCOME,
                FinancialCategory::TYPE_EXPENSE,
            ])],
            'color' => ['required', 'string', 'regex:/^#[a-fA-F0-9]{6}$/'],
            'icon' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da categoria é obrigatório.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais de 255 caracteres.',
            'name.unique' => 'Já existe uma categoria com este nome para este tipo.',
            'description.string' => 'A descrição deve ser um texto válido.',
            'description.max' => 'A descrição não pode ter mais de 1000 caracteres.',
            'type.required' => 'O tipo da categoria é obrigatório.',
            'type.in' => 'O tipo selecionado é inválido.',
            'color.required' => 'A cor da categoria é obrigatória.',
            'color.string' => 'A cor deve ser um texto válido.',
            'color.regex' => 'A cor deve estar no formato hexadecimal (#RRGGBB).',
            'icon.string' => 'O ícone deve ser um texto válido.',
            'icon.max' => 'O ícone não pode ter mais de 50 caracteres.',
            'is_active.boolean' => 'O status deve ser verdadeiro ou falso.',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active') && is_string($this->is_active)) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)
            ]);
        }
    }
}
