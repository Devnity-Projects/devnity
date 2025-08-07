<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportResponse;
use Illuminate\Http\Request;

class SupportResponseController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, SupportTicket $ticket)
    {
        $validated = $request->validate([
            'message' => 'required|string|min:5',
            'is_internal' => 'boolean'
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_system'] = false;

        $ticket->responses()->create($validated);

        // Se não for uma nota interna e o ticket estiver com status 'pending_customer',
        // alterar para 'in_progress'
        if (!$validated['is_internal'] && $ticket->status === 'pending_customer') {
            $ticket->update(['status' => 'in_progress']);
            
            // Criar uma resposta automática sobre a mudança de status
            $ticket->responses()->create([
                'user_id' => auth()->id(),
                'message' => 'Status alterado automaticamente de "Aguardando Cliente" para "Em Progresso" devido à nova resposta.',
                'is_internal' => true,
                'is_system' => true
            ]);
        }

        return back()->with('success', 'Resposta adicionada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SupportTicket $ticket, SupportResponse $response)
    {
        // Verificar se o usuário pode excluir esta resposta
        if ($response->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Você não tem permissão para excluir esta resposta.');
        }

        // Não permitir exclusão de respostas do sistema
        if ($response->is_system) {
            abort(403, 'Respostas do sistema não podem ser excluídas.');
        }

        $response->delete();

        return back()->with('success', 'Resposta excluída com sucesso!');
    }
}
