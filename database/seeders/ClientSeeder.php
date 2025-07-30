<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            [
                'name' => 'João Silva',
                'type' => Client::TYPE_INDIVIDUAL,
                'document' => '12345678901',
                'email' => 'joao.silva@email.com',
                'phone' => '11987654321',
                'zip_code' => '01234567',
                'address' => 'Rua das Flores, 123',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'status' => Client::STATUS_ACTIVE,
                'notes' => 'Cliente VIP, preferência por contato via WhatsApp.',
            ],
            [
                'name' => 'Maria Santos',
                'type' => Client::TYPE_INDIVIDUAL,
                'document' => '98765432100',
                'email' => 'maria.santos@email.com',
                'phone' => '11976543210',
                'zip_code' => '04567890',
                'address' => 'Av. Paulista, 1000',
                'number' => '1000',
                'complement' => 'Apto 501',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'status' => Client::STATUS_ACTIVE,
            ],
            [
                'name' => 'TechCorp Soluções Ltda',
                'type' => Client::TYPE_LEGAL,
                'document' => '12345678000195',
                'email' => 'contato@techcorp.com.br',
                'phone' => '1133334444',
                'responsible' => 'Carlos Oliveira',
                'responsible_email' => 'carlos@techcorp.com.br',
                'responsible_phone' => '11999888777',
                'state_registration' => '123456789',
                'municipal_registration' => '987654321',
                'zip_code' => '01310100',
                'address' => 'Av. Brigadeiro Faria Lima',
                'number' => '1500',
                'complement' => '10º andar',
                'neighborhood' => 'Jardim Paulistano',
                'city' => 'São Paulo',
                'state' => 'SP',
                'status' => Client::STATUS_ACTIVE,
                'notes' => 'Empresa especializada em desenvolvimento de software.',
            ],
            [
                'name' => 'Pedro Fernandes',
                'type' => Client::TYPE_INDIVIDUAL,
                'document' => '11122233344',
                'email' => 'pedro.fernandes@email.com',
                'phone' => '11955566677',
                'zip_code' => '22071900',
                'address' => 'Rua das Laranjeiras',
                'number' => '500',
                'neighborhood' => 'Laranjeiras',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'status' => Client::STATUS_INACTIVE,
                'notes' => 'Cliente inativo desde janeiro.',
            ],
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }
    }
}
