<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adicionar índices FULLTEXT apenas para MySQL/MariaDB
        // SQLite não suporta FULLTEXT, mas a busca LIKE ainda funcionará
        $driver = DB::connection()->getDriverName();
        
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE projects ADD FULLTEXT INDEX projects_search_idx (name, description, notes)');
        }
        
        // Adicionar índices simples para URLs (útil para buscas LIKE)
        Schema::table('projects', function (Blueprint $table) {
            $table->index('repository_url');
            $table->index('demo_url');
            $table->index('production_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        
        if (in_array($driver, ['mysql', 'mariadb'])) {
            DB::statement('ALTER TABLE projects DROP INDEX projects_search_idx');
        }
        
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['repository_url']);
            $table->dropIndex(['demo_url']);
            $table->dropIndex(['production_url']);
        });
    }
};
