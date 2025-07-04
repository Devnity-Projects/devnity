<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    // Listagem de clientes (Index)
    public function index()
    {
        $clients = Client::orderBy('name')->get();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    // Página de detalhes de cliente (Show)
    public function show(Client $client)
    {
        return Inertia::render('Clients/Show', [
            'client' => $client,
        ]);
    }

    // Criação de novo cliente (Store)
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Pessoa Física,Pessoa Jurídica',
            'document' => 'required|string|max:20|unique:clients,document',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'responsible' => 'nullable|string|max:255',
            'responsible_email' => 'nullable|email|max:255',
            'responsible_phone' => 'nullable|string|max:20',
            'state_registration' => 'nullable|string|max:20',
            'municipal_registration' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:50',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:ativo,inativo',
            'notes' => 'nullable|string|max:1000',
        ]);

        Client::create($data);

        return redirect()->route('clients.index')->with('success', 'Cliente cadastrado!');
    }

    // Atualização de cliente (Update)
    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:Pessoa Física,Pessoa Jurídica',
            'document' => 'required|string|max:20|unique:clients,document,'.$client->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'responsible' => 'nullable|string|max:255',
            'responsible_email' => 'nullable|email|max:255',
            'responsible_phone' => 'nullable|string|max:20',
            'state_registration' => 'nullable|string|max:20',
            'municipal_registration' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:50',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'country' => 'nullable|string|max:100',
            'status' => 'required|in:ativo,inativo',
            'notes' => 'nullable|string|max:1000',
        ]);

        $client->update($data);

        return redirect()->route('clients.index')->with('success', 'Cliente atualizado!');
    }

    // Excluir cliente (Destroy)
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Cliente removido!');
    }
}
