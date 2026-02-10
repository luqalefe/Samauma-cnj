<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chamados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('itens')->cascadeOnDelete();
            $table->foreignId('solicitante_id')->constrained('users')->cascadeOnDelete();
            $table->string('nivel_destino')->default('gerente'); // gerente, admin
            $table->text('mensagem');
            $table->text('resposta')->nullable();
            $table->string('status')->default('pendente'); // pendente, resolvido
            $table->foreignId('respondido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->datetime('respondido_em')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chamados');
    }
};
