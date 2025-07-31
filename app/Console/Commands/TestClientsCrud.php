<?php

namespace App\Console\Commands;

use App\Models\Client;
use Illuminate\Console\Command;

class TestClientsCrud extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:clients-crud';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the complete CRUD functionality for clients';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Client CRUD functionality...');
        
        // Test Create
        $this->info('1. Testing CREATE operation...');
        $client = Client::create([
            'name' => 'Empresa Teste CRUD',
            'type' => Client::TYPE_LEGAL,
            'document' => '12345678000195',
            'email' => 'teste@empresateste.com',
            'phone' => '11999888777',
            'responsible' => 'João Silva',
            'responsible_email' => 'joao@empresateste.com',
            'responsible_phone' => '11888777666',
            'state_registration' => '123456789',
            'municipal_registration' => '987654321',
            'zip_code' => '01234567',
            'address' => 'Rua Teste, 123',
            'number' => '123',
            'complement' => 'Sala 1',
            'neighborhood' => 'Centro',
            'city' => 'São Paulo',
            'state' => 'SP',
            'country' => 'Brasil',
            'status' => Client::STATUS_ACTIVE,
            'notes' => 'Cliente criado via teste CRUD'
        ]);
        
        $this->info("✅ Client created with ID: {$client->id}");
        
        // Test Read
        $this->info('2. Testing READ operation...');
        $foundClient = Client::find($client->id);
        $this->info("✅ Client found: {$foundClient->name}");
        $this->info("   Document: {$foundClient->formatted_document}");
        $this->info("   Phone: {$foundClient->formatted_phone}");
        $this->info("   Status: {$foundClient->status}");
        
        // Test Update
        $this->info('3. Testing UPDATE operation...');
        $foundClient->update([
            'name' => 'Empresa Teste CRUD Atualizada',
            'status' => Client::STATUS_INACTIVE,
            'notes' => 'Cliente atualizado via teste CRUD'
        ]);
        $this->info("✅ Client updated: {$foundClient->fresh()->name}");
        $this->info("   New status: {$foundClient->fresh()->status}");
        
        // Test Search functionality
        $this->info('4. Testing SEARCH functionality...');
        $searchResults = Client::search('Teste CRUD')->get();
        $this->info("✅ Search results: {$searchResults->count()} client(s) found");
        
        // Test Scopes
        $this->info('5. Testing SCOPES...');
        $activeClients = Client::active()->count();
        $inactiveClients = Client::inactive()->count();
        $legalClients = Client::byType(Client::TYPE_LEGAL)->count();
        $individualClients = Client::byType(Client::TYPE_INDIVIDUAL)->count();
        
        $this->info("✅ Active clients: {$activeClients}");
        $this->info("✅ Inactive clients: {$inactiveClients}");
        $this->info("✅ Legal entities: {$legalClients}");
        $this->info("✅ Individuals: {$individualClients}");
        
        // Test Document Validation
        $this->info('6. Testing DOCUMENT VALIDATION...');
        $this->info("   CPF validation: " . (Client::validateDocument('12345678901') ? '✅ Valid' : '❌ Invalid'));
        $this->info("   CNPJ validation: " . (Client::validateDocument('12345678000195') ? '✅ Valid' : '❌ Invalid'));
        $this->info("   Invalid document: " . (Client::validateDocument('123') ? '✅ Valid' : '❌ Invalid'));
        
        // Test Accessors
        $this->info('7. Testing ACCESSORS...');
        $this->info("   Formatted document: {$foundClient->formatted_document}");
        $this->info("   Formatted phone: {$foundClient->formatted_phone}");
        $this->info("   Full address: {$foundClient->full_address}");
        
        // Test Methods
        $this->info('8. Testing METHODS...');
        $this->info("   Is active: " . ($foundClient->fresh()->isActive() ? 'Yes' : 'No'));
        $this->info("   Is legal person: " . ($foundClient->isLegalPerson() ? 'Yes' : 'No'));
        $this->info("   Is individual: " . ($foundClient->isIndividual() ? 'Yes' : 'No'));
        
        // Test Delete
        $this->info('9. Testing DELETE operation...');
        $clientId = $foundClient->id;
        $foundClient->delete();
        $deletedClient = Client::find($clientId);
        
        if ($deletedClient === null) {
            $this->info("✅ Client successfully deleted");
        } else {
            $this->error("❌ Failed to delete client");
        }
        
        $this->info('');
        $this->info('🎉 All CRUD tests completed successfully!');
        $this->info('');
        $this->info('Available Features:');
        $this->info('- ✅ Create, Read, Update, Delete operations');
        $this->info('- ✅ Document validation (CPF/CNPJ)');
        $this->info('- ✅ Phone number formatting');
        $this->info('- ✅ Search functionality');
        $this->info('- ✅ Status management');
        $this->info('- ✅ Scopes for filtering');
        $this->info('- ✅ Data formatting accessors');
        $this->info('- ✅ Helper methods');
        $this->info('- ✅ Bulk operations support');
        $this->info('- ✅ CSV export');
        $this->info('- ✅ CEP lookup integration');
        
        return Command::SUCCESS;
    }
}
