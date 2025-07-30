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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('planning'); // planning, in_progress, completed, cancelled, on_hold
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('type')->default('development'); // development, maintenance, support, consultation
            $table->decimal('budget', 10, 2)->nullable();
            $table->decimal('hours_estimated', 8, 2)->nullable();
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('deadline')->nullable();
            $table->json('technologies')->nullable(); // JSON array of technologies used
            $table->string('repository_url')->nullable();
            $table->string('demo_url')->nullable();
            $table->string('production_url')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'priority']);
            $table->index(['client_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
