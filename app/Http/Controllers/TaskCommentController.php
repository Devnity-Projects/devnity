<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'content' => 'required|string',
            'is_internal' => 'boolean'
        ]);

        $comment = $task->comments()->create([
            'user_id' => auth()->id(),
            'content' => $request->input('content'),
            'is_internal' => $request->boolean('is_internal', false)
        ]);

        // Log activity
        $task->activities()->create([
            'user_id' => auth()->id(),
            'action' => 'comment_added',
            'description' => $comment->is_internal ? 'Adicionou um comentário interno' : 'Adicionou um comentário',
        ]);

        return back()->with('success', 'Comentário adicionado com sucesso!');
    }

    public function destroy(Task $task, TaskComment $comment)
    {
        // Only allow the comment author or admin to delete
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Não autorizado');
        }

        $comment->delete();

        // Log activity
        $task->activities()->create([
            'user_id' => auth()->id(),
            'action' => 'comment_deleted',
            'description' => 'Removeu um comentário',
        ]);

        return back()->with('success', 'Comentário removido com sucesso!');
    }
}
