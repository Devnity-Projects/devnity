<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequestNew;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ClientController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): InertiaResponse
    {
        $this->authorize('viewAny', Client::class);

        $query = Client::query();

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Type filter
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'name');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $validSortColumns = ['name', 'email', 'type', 'status', 'created_at'];
        if (in_array($sortBy, $validSortColumns)) {
            $query->orderBy($sortBy, $sortDirection);
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

    public function create(): InertiaResponse
    {
        $this->authorize('create', Client::class);
        
        return Inertia::render('Clients/Create');
    }

    public function store(ClientRequestNew $request): RedirectResponse
    {
        $this->authorize('create', Client::class);

        $client = Client::create($request->safeValidated());

        return Redirect::route('clients.show', $client)
            ->with('success', 'Cliente criado com sucesso!');
    }

    public function show(Client $client): InertiaResponse
    {
        $this->authorize('view', $client);

        $client->load(['projects' => function($query) {
            $query->orderBy('created_at', 'desc')->limit(5);
        }]);

        return Inertia::render('Clients/Show', [
            'client' => new ClientResource($client),
            'recentProjects' => $client->projects,
            'projectsCount' => $client->projects()->count(),
        ]);
    }

    public function edit(Client $client): InertiaResponse
    {
        $this->authorize('update', $client);
        
        return Inertia::render('Clients/Edit', [
            'client' => new ClientResource($client),
        ]);
    }

    public function update(ClientRequestNew $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $client->update($request->safeValidated());

        return Redirect::route('clients.show', $client)
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return Redirect::route('clients.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorize('bulkOperations', Client::class);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:clients,id'
        ]);

        Client::whereIn('id', $request->ids)->delete();

        return Redirect::route('clients.index')
            ->with('success', count($request->ids) . ' cliente(s) excluído(s) com sucesso!');
    }

    public function bulkToggleStatus(Request $request): RedirectResponse
    {
        $this->authorize('bulkOperations', Client::class);

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:clients,id',
            'status' => 'required|in:ativo,inativo'
        ]);

        Client::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        $statusText = $request->status === 'ativo' ? 'ativados' : 'desativados';
        
        return Redirect::route('clients.index')
            ->with('success', count($request->ids) . ' cliente(s) ' . $statusText . ' com sucesso!');
    }

    public function toggleStatus(Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $newStatus = $client->status === 'ativo' ? 'inativo' : 'ativo';
        $client->update(['status' => $newStatus]);

        $statusText = $newStatus === 'ativo' ? 'ativado' : 'desativado';

        return Redirect::back()
            ->with('success', "Cliente {$statusText} com sucesso!");
    }

    public function export(Request $request): Response
    {
        $this->authorize('export', Client::class);

        $query = Client::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        $clients = $query->orderBy('name')->get();

        $csv = "Nome,Tipo,Documento,Email,Telefone,Status,Endereço,Cidade,Estado,CEP,Criado em\n";
        
        foreach ($clients as $client) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $client->name,
                $client->type,
                $client->formatted_document ?: $client->document,
                $client->email ?: '',
                $client->formatted_phone ?: $client->phone ?: '',
                ucfirst($client->status),
                $client->address ?: '',
                $client->city ?: '',
                $client->state ?: '',
                $client->formatted_zip_code ?: $client->zip_code ?: '',
                $client->created_at?->format('d/m/Y H:i') ?: ''
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="clientes_' . date('Y-m-d_H-i-s') . '.csv"');
    }

    public function bulkExport(Request $request): Response
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:clients,id'
        ]);

        $clients = Client::whereIn('id', $request->ids)
            ->orderBy('name')
            ->get();

        $csv = "Nome,Tipo,Documento,Email,Telefone,Status,Endereço,Cidade,Estado,CEP,Criado em\n";
        
        foreach ($clients as $client) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $client->name,
                $client->type,
                $client->formatted_document ?: $client->document,
                $client->email ?: '',
                $client->formatted_phone ?: $client->phone ?: '',
                ucfirst($client->status),
                $client->address ?: '',
                $client->city ?: '',
                $client->state ?: '',
                $client->formatted_zip_code ?: $client->zip_code ?: '',
                $client->created_at?->format('d/m/Y H:i') ?: ''
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="clientes_selecionados_' . date('Y-m-d_H-i-s') . '.csv"');
    }
}