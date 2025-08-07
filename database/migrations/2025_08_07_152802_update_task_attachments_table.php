<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            // Renomear colunas para corresponder ao modelo
            $table->renameColumn('uploaded_by', 'user_id');
            $table->renameColumn('name', 'original_name');
            $table->renameColumn('file_path', 'path');
            $table->renameColumn('file_size', 'size');
            
            // Adicionar nova coluna filename
            $table->string('filename')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_attachments', function (Blueprint $table) {
            // Reverter as alterações
            $table->renameColumn('user_id', 'uploaded_by');
            $table->renameColumn('original_name', 'name');
            $table->renameColumn('path', 'file_path');
            $table->renameColumn('size', 'file_size');
            
            // Remover coluna filename
            $table->dropColumn('filename');
        });
    }
};
