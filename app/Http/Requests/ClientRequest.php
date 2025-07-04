<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        // Se estiver editando, ignora o próprio ID no unique do document/email
        $clientId = $this->route('client');

        return [
            'name'          => 'required|string|max:255',
            'type'          => 'required|in:Pessoa Física,Pessoa Jurídica',
            'document'      => 'required|string|max:20|unique:clients,document,' . $clientId,
            'email'         => 'nullable|email|max:255|unique:clients,email,' . $clientId,
            'phone'         => 'nullable|string|max:30',
            'responsible'   => 'nullable|string|max:255',
            'responsible_email' => 'nullable|email|max:255',
            'responsible_phone' => 'nullable|string|max:30',
            'state_registration' => 'nullable|string|max:50',
            'municipal_registration' => 'nullable|string|max:50',
            'zip_code'      => 'nullable|string|max:10',
            'address'       => 'nullable|string|max:255',
            'number'        => 'nullable|string|max:10',
            'complement'    => 'nullable|string|max:255',
            'neighborhood'  => 'nullable|string|max:100',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:2',
            'country'       => 'nullable|string|max:100',
            'status'        => 'required|in:ativo,inativo',
            'notes'         => 'nullable|string',
        ];
    }
}
