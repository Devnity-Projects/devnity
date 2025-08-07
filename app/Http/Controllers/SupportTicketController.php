<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportCategory;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupportTicketController extends Controller
{
    public function admin(Request $request)
    {
        $query = SupportTicket::with(['user', 'category', 'assignedTo'])
            ->orderBy('created_at', 'desc');

        // Aplicar filtros
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('ticket_number', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('support_category_id', $request->category);
        }

        $tickets = $query->paginate(15)->withQueryString();
        $categories = SupportCategory::where('is_active', true)->get();

        // Estatísticas
        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', 'open')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
            'pending' => SupportTicket::whereIn('status', ['open', 'in_progress', 'pending_customer'])->count(),
            'overdue' => SupportTicket::whereNotNull('sla_due_at')
                ->where('sla_due_at', '<', now())
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count()
        ];

        return Inertia::render('Support/Admin', [
            'tickets' => $tickets,
            'categories' => $categories,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'priority', 'category'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = SupportCategory::where('is_active', true)->get();
        $clients = Client::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return Inertia::render('Support/Tickets/Create', [
            'categories' => $categories,
            'clients' => $clients,
            'users' => $users
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:20',
            'support_category_id' => 'required|exists:support_categories,id',
            'priority' => 'required|in:low,medium,high,critical',
            'client_id' => 'nullable|exists:clients,id',
            'assigned_to' => 'nullable|exists:users,id',
            'sla_due_at' => 'nullable|date|after:now'
        ]);

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'open';

        // Se não foi definido um responsável, atribuir automaticamente
        if (!$validated['assigned_to']) {
            // Lógica simples: pegar o usuário com menos tickets ativos atribuídos
            $validated['assigned_to'] = User::withCount(['assignedTickets' => function ($query) {
                $query->whereNotIn('status', ['resolved', 'closed']);
            }])->orderBy('assigned_tickets_count')->first()?->id;
        }

        $ticket = SupportTicket::create($validated);

        return redirect()->route('support.tickets.show', $ticket)
            ->with('success', 'Ticket criado com sucesso! Número: #' . $ticket->ticket_number);
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['client', 'category', 'assignedTo'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority) {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned user
        if ($request->has('assigned_to') && $request->assigned_to) {
            if ($request->assigned_to === 'unassigned') {
                $query->whereNull('assigned_to');
            } else {
                $query->where('assigned_to', $request->assigned_to);
            }
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('ticket_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhereHas('client', function($clientQuery) use ($search) {
                      $clientQuery->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $tickets = $query->paginate(15)->withQueryString();

        // Get filter options
        $categories = SupportCategory::active()->ordered()->get();
        $users = User::orderBy('name')->get();

        $stats = [
            'total' => SupportTicket::count(),
            'open' => SupportTicket::where('status', SupportTicket::STATUS_OPEN)->count(),
            'in_progress' => SupportTicket::where('status', SupportTicket::STATUS_IN_PROGRESS)->count(),
            'waiting_client' => SupportTicket::where('status', SupportTicket::STATUS_WAITING_CLIENT)->count(),
            'overdue' => SupportTicket::whereNotIn('status', [SupportTicket::STATUS_RESOLVED, SupportTicket::STATUS_CLOSED])
                ->get()
                ->filter(fn($ticket) => $ticket->is_overdue)
                ->count(),
        ];

        return Inertia::render('Support/Index', [
            'tickets' => $tickets,
            'categories' => $categories,
            'users' => $users,
            'stats' => $stats,
            'filters' => $request->only(['status', 'priority', 'assigned_to', 'category', 'search']),
            'statusOptions' => [
                SupportTicket::STATUS_OPEN => 'Aberto',
                SupportTicket::STATUS_IN_PROGRESS => 'Em Andamento',
                SupportTicket::STATUS_WAITING_CLIENT => 'Aguardando Cliente',
                SupportTicket::STATUS_WAITING_INTERNAL => 'Aguardando Interno',
                SupportTicket::STATUS_RESOLVED => 'Resolvido',
                SupportTicket::STATUS_CLOSED => 'Fechado',
            ],
            'priorityOptions' => [
                SupportTicket::PRIORITY_LOW => 'Baixa',
                SupportTicket::PRIORITY_MEDIUM => 'Média',
                SupportTicket::PRIORITY_HIGH => 'Alta',
                SupportTicket::PRIORITY_URGENT => 'Urgente',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(SupportTicket $ticket)
    {
        $ticket->load([
            'user',
            'client',
            'category',
            'assignedTo',
            'responses' => function ($query) {
                $query->with('user')->orderBy('created_at', 'asc');
            }
        ]);

        $users = User::orderBy('name')->get();

        return Inertia::render('Support/Tickets/Show', [
            'ticket' => $ticket,
            'users' => $users
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SupportTicket $supportTicket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:open,in_progress,pending_customer,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|min:20',
            'support_category_id' => 'sometimes|exists:support_categories,id'
        ]);

        $oldValues = $ticket->only(['status', 'priority', 'assigned_to']);
        $ticket->update($validated);

        // Criar registros de mudanças como respostas do sistema
        $changes = [];
        
        if (isset($validated['status']) && $oldValues['status'] !== $validated['status']) {
            $changes[] = "Status alterado de " . $this->getStatusLabel($oldValues['status']) . " para " . $this->getStatusLabel($validated['status']);
        }
        
        if (isset($validated['priority']) && $oldValues['priority'] !== $validated['priority']) {
            $changes[] = "Prioridade alterada de " . $this->getPriorityLabel($oldValues['priority']) . " para " . $this->getPriorityLabel($validated['priority']);
        }
        
        if (isset($validated['assigned_to']) && $oldValues['assigned_to'] !== $validated['assigned_to']) {
            $oldUser = $oldValues['assigned_to'] ? User::find($oldValues['assigned_to'])->name : 'Não atribuído';
            $newUser = $validated['assigned_to'] ? User::find($validated['assigned_to'])->name : 'Não atribuído';
            $changes[] = "Responsável alterado de {$oldUser} para {$newUser}";
        }

        // Se houver mudanças, criar uma resposta automática
        if (!empty($changes)) {
            $ticket->responses()->create([
                'user_id' => auth()->id(),
                'message' => implode("\n", $changes),
                'is_internal' => true,
                'is_system' => true
            ]);
        }

        return back()->with('success', 'Ticket atualizado com sucesso!');
    }

    private function getStatusLabel($status)
    {
        $labels = [
            'open' => 'Aberto',
            'in_progress' => 'Em Progresso', 
            'pending_customer' => 'Aguardando Cliente',
            'resolved' => 'Resolvido',
            'closed' => 'Fechado'
        ];
        return $labels[$status] ?? $status;
    }

    private function getPriorityLabel($priority)
    {
        $labels = [
            'low' => 'Baixa',
            'medium' => 'Média',
            'high' => 'Alta',
            'critical' => 'Crítica'
        ];
        return $labels[$priority] ?? $priority;
    }

    /**
     * Remove the specified resource from storage.
                $content = "Ticket removido da atribuição.";
            }

            $ticket->responses()->create([
                'user_id' => auth()->id(),
                'content' => $content,
                'type' => 'assignment',
                'is_public' => false,
                'previous_assigned_to' => $originalAssigned,
                'new_assigned_to' => $request->assigned_to,
            ]);
        }

        return back()->with('success', 'Ticket atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupportTicket $ticket)
    {
        $ticketNumber = $ticket->ticket_number;
        $ticket->delete();

        return redirect()->route('support.index')->with('success', "Ticket {$ticketNumber} removido com sucesso!");
    }
}
