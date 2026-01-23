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
    Schema::create('codigo_de_propostas', function (Blueprint $table) {
        $table->id();
        $table->string('codigo_proposta', 255)->nullable()->unique();
        $table->dateTime('data')->nullable();
        $table->string('descricao', 100);
        $table->string('filial_nome', 255)->nullable(); 

        // Chaves Estrangeiras
        $table->foreignId('user_id')->nullable()->constrained('users'); 
        $table->foreignId('cliente_id')->nullable()->constrained('clientes');
        $table->foreignId('parceiro_id')->nullable()->constrained('parceiros');
        $table->foreignId('produto_id')->nullable()->constrained('produtos');
        $table->unsignedBigInteger('distribuidor_id')->nullable();
        $table->unsignedBigInteger('fornecedor_id')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('codigo_de_propostas');
    }
};
