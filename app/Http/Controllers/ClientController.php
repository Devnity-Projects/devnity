<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderBy('name')->paginate(15);

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email',
            'phone' => 'nullable|string|max:30',
            'status' => 'required|string',
            'company' => 'nullable|string|max:255',
            'document' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        Client::create($data);

        return redirect()->route('clients.index')->with('success', 'Cliente cadastrado!');
    }

    public function show(Client $client)
    {
        return Inertia::render('Clients/Show', [
            'client' => $client,
        ]);
    }

    public function edit(Client $client)
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email,'.$client->id,
            'phone' => 'nullable|string|max:30',
            'status' => 'required|string',
            'company' => 'nullable|string|max:255',
            'document' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $client->update($data);

        return redirect()->route('clients.show', $client)->with('success', 'Cliente atualizado!');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Cliente removido.');
    }
}
