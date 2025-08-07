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
        Schema::create('support_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('support_tickets')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Staff member
            
            $table->text('content');
            $table->enum('type', ['response', 'note', 'status_change', 'assignment'])->default('response');
            $table->boolean('is_public')->default(true); // Show to client
            $table->boolean('is_from_client')->default(false); // Response from client portal
            
            // Client info for client responses
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            
            // Attachments path (if any)
            $table->json('attachments')->nullable();
            
            // Status tracking
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->foreignId('previous_assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('new_assigned_to')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['ticket_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['is_public', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_responses');
    }
};
