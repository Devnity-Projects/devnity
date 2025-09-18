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
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB max
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,jpg,jpeg,png,gif,webp,zip,rar',
                function ($attribute, $value, $fail) {
                    // Verificar extensões perigosas
                    $dangerousExtensions = ['exe', 'bat', 'cmd', 'com', 'scr', 'pif', 'vbs', 'js', 'jar', 'php', 'asp', 'aspx', 'jsp'];
                    $extension = strtolower($value->getClientOriginalExtension());
                    
                    if (in_array($extension, $dangerousExtensions)) {
                        $fail('Tipo de arquivo não permitido por motivos de segurança.');
                    }
                    
                    // Verificar MIME type real do arquivo
                    $allowedMimes = [
                        'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        'text/plain', 'image/jpeg', 'image/png', 'image/gif', 'image/webp',
                        'application/zip', 'application/x-rar-compressed'
                    ];
                    
                    if (!in_array($value->getMimeType(), $allowedMimes)) {
                        $fail('Tipo de arquivo não suportado.');
                    }
                }
            ]
        ]);

        // Verificar se o usuário tem permissão para adicionar anexos à tarefa
        if (!$task->project || $task->project->user_id !== auth()->id()) {
            abort(403, 'Não autorizado a adicionar anexos a esta tarefa.');
        }

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
        // Verificar se o anexo pertence à tarefa
        if ($attachment->task_id !== $task->id) {
            abort(403, 'Anexo não pertence a esta tarefa.');
        }
        
        // Verificar se o usuário tem permissão para acessar a tarefa
        if (!$task->project || $task->project->user_id !== auth()->id()) {
            abort(403, 'Não autorizado a acessar este anexo.');
        }

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
        // Verificar se o anexo pertence à tarefa
        if ($attachment->task_id !== $task->id) {
            abort(403, 'Anexo não pertence a esta tarefa.');
        }
        
        // Verificar se o usuário tem permissão (proprietário da tarefa ou quem fez upload)
        $hasPermission = $attachment->user_id === auth()->id() || 
                        ($task->project && $task->project->user_id === auth()->id());
                        
        if (!$hasPermission) {
            abort(403, 'Não autorizado a remover este anexo.');
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
