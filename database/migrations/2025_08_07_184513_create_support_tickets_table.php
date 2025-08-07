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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique(); // ST-2025-001
            $table->string('title');
            $table->text('description');
            
            // Relationships
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('support_categories')->onDelete('restrict');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Admin who created
            
            // Status and Priority
            $table->enum('status', ['open', 'in_progress', 'waiting_client', 'waiting_internal', 'resolved', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            
            // Contact info (in case client details change)
            $table->string('contact_name');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            
            // Tracking
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->decimal('resolution_time_hours', 8, 2)->nullable(); // For metrics
            
            // Additional info
            $table->json('tags')->nullable(); // ["bug", "urgent", "billing"]
            $table->boolean('is_public')->default(true); // Show to client or internal only
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['status', 'priority']);
            $table->index(['client_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
