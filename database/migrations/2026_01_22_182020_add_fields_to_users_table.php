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
    Schema::table('users', function (Blueprint $table) {
        $table->string('sigla', 16)->nullable()->after('name');
        $table->string('username', 60)->unique()->after('email');
        $table->string('celular', 20)->nullable();
        $table->enum('perfil', ['VENDEDOR', 'GERENTE', 'ADMIN'])->default('VENDEDOR');
        $table->enum('status', ['INATIVO', 'ATIVO'])->default('INATIVO');
        $table->boolean('must_change_password')->default(true);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
