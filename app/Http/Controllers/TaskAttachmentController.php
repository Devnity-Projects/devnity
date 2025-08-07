<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TaskAttachmentController extends Controller
{
    public function store(Request $request, Task $task)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('task-attachments', $filename, 'public');

        $attachment = $task->attachments()->create([
            'user_id' => auth()->id(),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'path' => $path
        ]);

        // Log activity
        $task->activities()->create([
            'user_id' => auth()->id(),
            'action' => 'attachment_added',
            'description' => "Adicionou o arquivo: {$attachment->original_name}",
        ]);

        return back()->with('success', 'Arquivo enviado com sucesso!');
    }

    public function download(Task $task, TaskAttachment $attachment)
    {
        if (!Storage::disk('public')->exists($attachment->path)) {
            abort(404, 'Arquivo não encontrado');
        }

        return response()->download(
            Storage::disk('public')->path($attachment->path),
            $attachment->original_name
        );
    }

    public function destroy(Task $task, TaskAttachment $attachment)
    {
        // Only allow the attachment uploader to delete
        if ($attachment->user_id !== auth()->id()) {
            abort(403, 'Não autorizado');
        }

        // Delete file from storage
        if (Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }

        $attachment->delete();

        // Log activity
        $task->activities()->create([
            'user_id' => auth()->id(),
            'action' => 'attachment_removed',
            'description' => "Removeu o arquivo: {$attachment->original_name}",
        ]);

        return back()->with('success', 'Arquivo removido com sucesso!');
    }
}
