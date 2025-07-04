<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'name'                  => $this->name,
            'type'                  => $this->type,
            'document'              => $this->document,
            'email'                 => $this->email,
            'phone'                 => $this->phone,
            'responsible'           => $this->responsible,
            'responsible_email'     => $this->responsible_email,
            'responsible_phone'     => $this->responsible_phone,
            'state_registration'    => $this->state_registration,
            'municipal_registration'=> $this->municipal_registration,
            'zip_code'              => $this->zip_code,
            'address'               => $this->address,
            'number'                => $this->number,
            'complement'            => $this->complement,
            'neighborhood'          => $this->neighborhood,
            'city'                  => $this->city,
            'state'                 => $this->state,
            'country'               => $this->country,
            'status'                => $this->status,
            'notes'                 => $this->notes,
            'created_at'            => $this->created_at?->toDateTimeString(),
            'updated_at'            => $this->updated_at?->toDateTimeString(),
        ];
    }
}
