<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 12, 2); // valor orçado
            $table->decimal('spent', 12, 2)->default(0); // valor gasto
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('period', ['monthly', 'quarterly', 'biannual', 'yearly', 'custom']);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->foreignId('category_id')->nullable()->constrained('financial_categories')->onDelete('set null');
            $table->boolean('alert_on_90_percent')->default(true); // alerta aos 90%
            $table->boolean('alert_on_100_percent')->default(true); // alerta aos 100%
            $table->timestamps();

            $table->index(['status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_budgets');
    }
};
