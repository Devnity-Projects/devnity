<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskChecklist;
use Illuminate\Http\Request;

class TaskChecklistController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'title' => 'required|string|max:255'
        ]);

        $maxOrder = $task->checklist()->max('order') ?? 0;

        $item = $task->checklist()->create([
            'title' => $request->input('title'),
            'order' => $maxOrder + 1
        ]);

        // Log activity
        $task->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'checklist_item_added',
            'description' => "Adicionou item da checklist: {$item->title}",
        ]);

        return back()->with('success', 'Item adicionado com sucesso!');
    }

    public function update(Request $request, Task $task, TaskChecklist $checklist)
    {
        $request->validate([
            'is_completed' => 'required|boolean'
        ]);

        $wasCompleted = $checklist->is_completed;
        $checklist->is_completed = $request->boolean('is_completed');
        
        if ($checklist->is_completed && !$wasCompleted) {
            $checklist->completed_at = now();
            $checklist->completed_by_id = auth()->id();
        } elseif (!$checklist->is_completed && $wasCompleted) {
            $checklist->completed_at = null;
            $checklist->completed_by_id = null;
        }

        $checklist->save();

        // Log activity
        $task->activities()->create([
            'user_id' => auth()->id(),
            'type' => $checklist->is_completed ? 'checklist_item_completed' : 'checklist_item_unchecked',
            'description' => ($checklist->is_completed ? 'Marcou como concluído' : 'Desmarcou') . " o item: {$checklist->title}",
        ]);

        return back()->with('success', 'Item atualizado com sucesso!');
    }

    public function destroy(Task $task, TaskChecklist $checklist)
    {
        $title = $checklist->title;
        $checklist->delete();

        // Log activity
        $task->activities()->create([
            'user_id' => auth()->id(),
            'type' => 'checklist_item_removed',
            'description' => "Removeu item da checklist: {$title}",
        ]);

        return back()->with('success', 'Item removido com sucesso!');
    }
}
