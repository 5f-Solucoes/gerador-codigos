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
    Schema::create('produtos', function (Blueprint $table) {
        $table->id();
        $table->string('nome', 128);
        $table->string('sigla', 16)->nullable();
        $table->foreignId('parceiro_id')->nullable()->constrained('parceiros');
        $table->foreignId('cadastrado_por')->nullable()->constrained('users');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
