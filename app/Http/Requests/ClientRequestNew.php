<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequestNew extends FormRequest
{
    public function authorize(): bool
    {
        // Verifica se o usuário tem permissão para criar/atualizar clientes
        if ($this->isMethod('POST')) {
            return $this->user()->can('create', Client::class);
        }
        
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $client = $this->route('client');
            return $client && $this->user()->can('update', $client);
        }
        
        return true;
    }

    public function rules(): array
    {
        $clientId = $this->route('client')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\.]+$/', // Apenas letras, espaços, hífens e pontos
            ],
            'type' => [
                'required',
                Rule::in([Client::TYPE_INDIVIDUAL, Client::TYPE_LEGAL])
            ],
            'document' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9\.\-\/]+$/', // Apenas números e caracteres de formatação
                Rule::unique('clients', 'document')->ignore($clientId),
                function ($attribute, $value, $fail) {
                    if (!Client::validateDocument($value)) {
                        $fail('O documento informado não é válido.');
                    }
                }
            ],
            'email' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                Rule::unique('clients', 'email')->ignore($clientId)
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9\s\(\)\-]+$/'
            ],
            'responsible' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-ZÀ-ÿ\s\-\.]+$/'
            ],
            'responsible_email' => [
                'nullable',
                'email:rfc,dns',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'responsible_phone' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[0-9\s\(\)\-]+$/'
            ],
            'state_registration' => [
                'nullable',
                'string',
                'max:50'
            ],
            'municipal_registration' => [
                'nullable',
                'string',
                'max:50'
            ],
            'zip_code' => [
                'nullable',
                'string',
                'regex:/^[0-9]{5}-?[0-9]{3}$/'
            ],
            'address' => [
                'nullable',
                'string',
                'max:255'
            ],
            'number' => [
                'nullable',
                'string',
                'max:20'
            ],
            'complement' => [
                'nullable',
                'string',
                'max:100'
            ],
            'neighborhood' => [
                'nullable',
                'string',
                'max:100'
            ],
            'city' => [
                'nullable',
                'string',
                'max:100'
            ],
            'state' => [
                'nullable',
                'string',
                'size:2',
                'regex:/^[A-Z]{2}$/'
            ],
            'country' => [
                'nullable',
                'string',
                'max:100'
            ],
            'status' => [
                'required',
                Rule::in([Client::STATUS_ACTIVE, Client::STATUS_INACTIVE])
            ],
            'notes' => [
                'nullable',
                'string',
                'max:5000'
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     * Sanitiza os dados antes da validação
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge([
                'name' => strip_tags(trim($this->name))
            ]);
        }

        if ($this->has('document')) {
            $this->merge([
                'document' => preg_replace('/[^0-9]/', '', $this->document)
            ]);
        }

        if ($this->has('phone')) {
            $this->merge([
                'phone' => preg_replace('/[^0-9]/', '', $this->phone)
            ]);
        }

        if ($this->has('responsible_phone')) {
            $this->merge([
                'responsible_phone' => preg_replace('/[^0-9]/', '', $this->responsible_phone)
            ]);
        }

        if ($this->has('zip_code')) {
            $this->merge([
                'zip_code' => preg_replace('/[^0-9]/', '', $this->zip_code)
            ]);
        }

        // Sanitiza campos de texto
        foreach (['responsible', 'address', 'complement', 'neighborhood', 'city', 'notes'] as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => strip_tags(trim($this->input($field)))
                ]);
            }
        }
    }

    /**
     * Get validated data with additional security measures.
     * Retorna dados validados com medidas de segurança adicionais
     */
    public function safeValidated(): array
    {
        $validated = $this->validated();
        
        // Remove qualquer campo que não deveria estar presente
        $allowedFields = [
            'name', 'type', 'document', 'email', 'phone', 'responsible',
            'responsible_email', 'responsible_phone', 'state_registration',
            'municipal_registration', 'zip_code', 'address', 'number',
            'complement', 'neighborhood', 'city', 'state', 'country',
            'status', 'notes'
        ];
        
        return array_intersect_key($validated, array_flip($allowedFields));
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
            'name.regex' => 'O nome deve conter apenas letras, espaços, hífens e pontos.',
            'type.required' => 'O tipo de cliente é obrigatório.',
            'type.in' => 'O tipo de cliente deve ser Pessoa Física ou Pessoa Jurídica.',
            'document.required' => 'O documento (CPF/CNPJ) é obrigatório.',
            'document.unique' => 'Este documento já está cadastrado.',
            'document.regex' => 'O documento deve conter apenas números.',
            'email.email' => 'O e-mail deve ter um formato válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'email.regex' => 'O formato do e-mail é inválido.',
            'phone.regex' => 'O telefone deve conter apenas números.',
            'responsible.regex' => 'O nome do responsável deve conter apenas letras, espaços, hífens e pontos.',
            'responsible_email.email' => 'O e-mail do responsável deve ter um formato válido.',
            'responsible_email.regex' => 'O formato do e-mail do responsável é inválido.',
            'responsible_phone.regex' => 'O telefone do responsável deve conter apenas números.',
            'zip_code.regex' => 'O CEP deve ter o formato 00000-000.',
            'state.size' => 'O estado deve ter exatamente 2 caracteres.',
            'state.regex' => 'O estado deve conter apenas letras maiúsculas.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser ativo ou inativo.',
            'notes.max' => 'As notas não podem exceder 5000 caracteres.',
        ];
    }
}