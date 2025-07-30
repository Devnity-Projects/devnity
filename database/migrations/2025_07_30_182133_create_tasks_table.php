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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('todo'); // todo, in_progress, review, completed, cancelled
            $table->string('priority')->default('medium'); // low, medium, high, urgent
            $table->string('type')->default('feature'); // feature, bug, enhancement, documentation, testing
            $table->decimal('hours_estimated', 8, 2)->nullable();
            $table->decimal('hours_worked', 8, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->integer('order')->default(0); // For task ordering within project
            $table->json('labels')->nullable(); // JSON array of labels/tags
            $table->text('acceptance_criteria')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index(['priority', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
