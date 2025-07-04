<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // Razão Social ou Nome completo
            $table->enum('type', ['Pessoa Física', 'Pessoa Jurídica']);
            $table->string('document', 20);                 // CPF ou CNPJ
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('responsible')->nullable();      // Responsável pelo contrato/empresa
            $table->string('responsible_email')->nullable();
            $table->string('responsible_phone')->nullable();
            $table->string('state_registration')->nullable(); // IE (Pessoa Jurídica)
            $table->string('municipal_registration')->nullable(); // IM (Pessoa Jurídica)
            $table->string('zip_code', 10)->nullable();
            $table->string('address')->nullable();
            $table->string('number', 10)->nullable();
            $table->string('complement')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('country')->default('Brasil');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
