<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::orderBy('name')->paginate(15);
        return Inertia::render('Clients/Index', ['clients' => $clients]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    public function store(ClientRequest $request)
    {
        Client::create($request->validated());
        return redirect()->route('clients.index')->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show(Client $client)
    {
        return Inertia::render('Clients/Show', ['client' => $client]);
    }

    public function edit(Client $client)
    {
        return Inertia::render('Clients/Edit', ['client' => $client]);
    }

    public function update(ClientRequest $request, Client $client)
    {
        $client->update($request->validated());
        return redirect()->route('clients.index')->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Cliente removido com sucesso!');
    }
}
