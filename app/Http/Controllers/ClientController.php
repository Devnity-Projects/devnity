<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class ClientController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Client::query();

        // Aplicar filtros
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Ordenação
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSorts = ['name', 'email', 'created_at', 'status', 'type'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        } else {
            $query->orderBy('name', 'asc');
        }

        $clients = $query->paginate($request->get('per_page', 15))
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'type' => $request->type,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
            'stats' => [
                'total' => Client::count(),
                'active' => Client::active()->count(),
                'inactive' => Client::inactive()->count(),
                'individual' => Client::byType(Client::TYPE_INDIVIDUAL)->count(),
                'legal' => Client::byType(Client::TYPE_LEGAL)->count(),
            ]
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Clients/Create', [
            'types' => [
                Client::TYPE_INDIVIDUAL,
                Client::TYPE_LEGAL,
            ],
            'statuses' => [
                Client::STATUS_ACTIVE,
                Client::STATUS_INACTIVE,
            ]
        ]);
    }

    public function store(ClientRequest $request): RedirectResponse
    {
        $client = Client::create($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function show(Client $client): Response
    {
        return Inertia::render('Clients/Show', [
            'client' => new ClientResource($client),
        ]);
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('Clients/Edit', [
            'client' => new ClientResource($client),
            'types' => [
                Client::TYPE_INDIVIDUAL,
                Client::TYPE_LEGAL,
            ],
            'statuses' => [
                Client::STATUS_ACTIVE,
                Client::STATUS_INACTIVE,
            ]
        ]);
    }

    public function update(ClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Client $client): RedirectResponse
    {
        try {
            $client->delete();
            
            return redirect()
                ->route('clients.index')
                ->with('success', 'Cliente removido com sucesso!');
        } catch (\Exception $e) {
            return redirect()
                ->route('clients.index')
                ->with('error', 'Não foi possível remover o cliente. Verifique se não há dependências.');
        }
    }

    public function export(Request $request)
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        $clients = $query->orderBy('name')->get();

        $csv = [];
        $csv[] = [
            'Nome',
            'Tipo',
            'Documento',
            'Email',
            'Telefone',
            'Responsável',
            'Status',
            'Cidade',
            'Estado',
            'Data de Cadastro'
        ];

        foreach ($clients as $client) {
            $csv[] = [
                $client->name,
                $client->type,
                $client->formatted_document,
                $client->email,
                $client->formatted_phone,
                $client->responsible,
                ucfirst($client->status),
                $client->city,
                $client->state,
                $client->created_at->format('d/m/Y')
            ];
        }

        $filename = 'clientes_' . date('Y-m-d_H-i-s') . '.csv';
        
        $handle = fopen('php://output', 'w');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        
        foreach ($csv as $row) {
            fputcsv($handle, $row, ';');
        }
        
        fclose($handle);

        return response()->stream(function() use ($csv) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            foreach ($csv as $row) {
                fputcsv($handle, $row, ';');
            }
            
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function toggleStatus(Client $client): RedirectResponse
    {
        $newStatus = $client->status === Client::STATUS_ACTIVE 
            ? Client::STATUS_INACTIVE 
            : Client::STATUS_ACTIVE;
            
        $client->update(['status' => $newStatus]);

        $statusText = $newStatus === Client::STATUS_ACTIVE ? 'ativado' : 'desativado';
        
        return redirect()
            ->back()
            ->with('success', "Cliente {$statusText} com sucesso!");
    }
}
