<?php

namespace App\Http\Requests;

use App\Models\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
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
                'min:2'
            ],
            'type' => [
                'required',
                Rule::in([Client::TYPE_INDIVIDUAL, Client::TYPE_LEGAL])
            ],
            'document' => [
                'required',
                'string',
                'max:20',
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
                Rule::unique('clients', 'email')->ignore($clientId)
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[\(\)\d\s\-\+]+$/'
            ],
            'responsible' => [
                'nullable',
                'string',
                'max:255',
                'min:2'
            ],
            'responsible_email' => [
                'nullable',
                'email:rfc,dns',
                'max:255'
            ],
            'responsible_phone' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[\(\)\d\s\-\+]+$/'
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
                'max:10',
                'regex:/^\d{5}-?\d{3}$/'
            ],
            'address' => [
                'nullable',
                'string',
                'max:255'
            ],
            'number' => [
                'nullable',
                'string',
                'max:10'
            ],
            'complement' => [
                'nullable',
                'string',
                'max:255'
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

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.min' => 'O nome deve ter pelo menos 2 caracteres.',
            'type.required' => 'O tipo de cliente é obrigatório.',
            'type.in' => 'O tipo de cliente deve ser Pessoa Física ou Pessoa Jurídica.',
            'document.required' => 'O documento (CPF/CNPJ) é obrigatório.',
            'document.unique' => 'Este documento já está cadastrado.',
            'email.email' => 'O e-mail deve ter um formato válido.',
            'email.unique' => 'Este e-mail já está cadastrado.',
            'phone.regex' => 'O telefone deve conter apenas números, espaços, parênteses e hífens.',
            'responsible_email.email' => 'O e-mail do responsável deve ter um formato válido.',
            'responsible_phone.regex' => 'O telefone do responsável deve conter apenas números, espaços, parênteses e hífens.',
            'zip_code.regex' => 'O CEP deve ter o formato 00000-000.',
            'state.size' => 'O estado deve ter exatamente 2 caracteres.',
            'state.regex' => 'O estado deve conter apenas letras maiúsculas.',
            'status.required' => 'O status é obrigatório.',
            'status.in' => 'O status deve ser ativo ou inativo.',
            'notes.max' => 'As notas não podem exceder 5000 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'document' => preg_replace('/\D/', '', $this->document ?? ''),
            'phone' => $this->phone ? preg_replace('/[^\d\+]/', '', $this->phone) : null,
            'responsible_phone' => $this->responsible_phone ? preg_replace('/[^\d\+]/', '', $this->responsible_phone) : null,
            'zip_code' => $this->zip_code ? preg_replace('/\D/', '', $this->zip_code) : null,
            'state' => $this->state ? strtoupper($this->state) : null,
        ]);
    }
}
