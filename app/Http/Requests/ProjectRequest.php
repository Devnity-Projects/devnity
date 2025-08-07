<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'client_id' => ['required', 'exists:clients,id'],
            'status' => ['required', 'in:' . implode(',', [
                Project::STATUS_PLANNING,
                Project::STATUS_IN_PROGRESS,
                Project::STATUS_ON_HOLD,
                Project::STATUS_COMPLETED,
                Project::STATUS_CANCELLED,
            ])],
            'priority' => ['required', 'in:' . implode(',', [
                Project::PRIORITY_LOW,
                Project::PRIORITY_MEDIUM,
                Project::PRIORITY_HIGH,
                Project::PRIORITY_URGENT,
            ])],
            'type' => ['required', 'in:' . implode(',', [
                Project::TYPE_DEVELOPMENT,
                Project::TYPE_MAINTENANCE,
                Project::TYPE_SUPPORT,
                Project::TYPE_CONSULTATION,
            ])],
            'budget' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'hours_estimated' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'hours_worked' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'deadline' => ['nullable', 'date'],
            'technologies' => ['nullable', 'array'],
            'technologies.*' => ['string', 'max:255'],
            'repository_url' => ['nullable', 'url', 'max:255'],
            'demo_url' => ['nullable', 'url', 'max:255'],
            'production_url' => ['nullable', 'url', 'max:255'],
            'notes' => ['nullable', 'string', 'max:65535'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'description' => 'descrição',
            'client_id' => 'cliente',
            'status' => 'status',
            'priority' => 'prioridade',
            'type' => 'tipo',
            'budget' => 'orçamento',
            'hours_estimated' => 'horas estimadas',
            'hours_worked' => 'horas trabalhadas',
            'start_date' => 'data de início',
            'end_date' => 'data de fim',
            'deadline' => 'prazo',
            'technologies' => 'tecnologias',
            'repository_url' => 'URL do repositório',
            'demo_url' => 'URL da demonstração',
            'production_url' => 'URL de produção',
            'notes' => 'observações',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'client_id.required' => 'O campo cliente é obrigatório.',
            'client_id.exists' => 'O cliente selecionado não existe.',
            'end_date.after_or_equal' => 'A data de fim deve ser posterior ou igual à data de início.',
            'budget.numeric' => 'O orçamento deve ser um valor numérico.',
            'budget.min' => 'O orçamento não pode ser negativo.',
            'hours_estimated.numeric' => 'As horas estimadas devem ser um valor numérico.',
            'hours_estimated.min' => 'As horas estimadas não podem ser negativas.',
            'hours_worked.numeric' => 'As horas trabalhadas devem ser um valor numérico.',
            'hours_worked.min' => 'As horas trabalhadas não podem ser negativas.',
            'repository_url.url' => 'A URL do repositório deve ser válida.',
            'demo_url.url' => 'A URL da demonstração deve ser válida.',
            'production_url.url' => 'A URL de produção deve ser válida.',
        ];
    }
}
