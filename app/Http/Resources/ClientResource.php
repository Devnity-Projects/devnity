<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    use \App\Support\AuthorizesFields;

    /**
     * Campos sensíveis e as permissões necessárias para visualizá-los.
     * Exemplo: 'document' => 'clients.view_document'
     * Vários perms: 'notes' => ['clients.view_notes', 'clients.manage']
     *
     * @var array<string,string|array<int,string>>
     */
    protected array $fieldPermissions = [
        'document' => 'clients.view_document',
        'formatted_document' => 'clients.view_document',
        'email' => 'clients.view_contact',
        'phone' => 'clients.view_contact',
        'formatted_phone' => 'clients.view_contact',
        'responsible_email' => 'clients.view_contact',
        'responsible_phone' => 'clients.view_contact',
        'state_registration' => 'clients.view_tax_info',
        'municipal_registration' => 'clients.view_tax_info',
        'zip_code' => 'clients.view_address',
        'formatted_zip_code' => 'clients.view_address',
        'address' => 'clients.view_address',
        'number' => 'clients.view_address',
        'complement' => 'clients.view_address',
        'neighborhood' => 'clients.view_address',
        'city' => 'clients.view_address',
        'state' => 'clients.view_address',
        'country' => 'clients.view_address',
        'full_address' => 'clients.view_address',
        'notes' => ['clients.view_notes', 'clients.manage'],
    ];
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'document' => $this->document,
            'formatted_document' => $this->formatted_document,
            'email' => $this->email,
            'phone' => $this->phone,
            'formatted_phone' => $this->formatted_phone,
            'responsible' => $this->responsible,
            'responsible_email' => $this->responsible_email,
            'responsible_phone' => $this->responsible_phone,
            'state_registration' => $this->state_registration,
            'municipal_registration' => $this->municipal_registration,
            'zip_code' => $this->zip_code,
            'formatted_zip_code' => $this->formatted_zip_code,
            'address' => $this->address,
            'number' => $this->number,
            'complement' => $this->complement,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'full_address' => $this->full_address,
            'status' => $this->status,
            'status_label' => ucfirst($this->status),
            'notes' => $this->notes,
            'is_active' => $this->isActive(),
            'is_legal_person' => $this->isLegalPerson(),
            'is_individual' => $this->isIndividual(),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'created_at_formatted' => $this->created_at?->format('d/m/Y H:i'),
            'updated_at_formatted' => $this->updated_at?->format('d/m/Y H:i'),
            'created_at_diff' => $this->created_at?->diffForHumans(),
        ];

        // Filtrar campos por permissões
        return $this->filterFieldsByPermissions($data, $this->fieldPermissions, $request);
    }
}
