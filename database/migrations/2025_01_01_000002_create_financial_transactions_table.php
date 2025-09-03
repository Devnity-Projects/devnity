<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['income', 'expense']); // receita ou despesa
            $table->decimal('amount', 12, 2); // valor
            $table->date('due_date'); // data de vencimento
            $table->date('payment_date')->nullable(); // data de pagamento
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->enum('recurrence', ['none', 'monthly', 'quarterly', 'biannual', 'yearly'])->default('none');
            $table->integer('installments')->default(1); // número de parcelas
            $table->integer('current_installment')->default(1); // parcela atual
            $table->foreignId('category_id')->constrained('financial_categories')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->string('payment_method')->nullable(); // dinheiro, cartão, pix, boleto, etc
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable(); // documentos anexos
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['due_date']);
            $table->index(['payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
