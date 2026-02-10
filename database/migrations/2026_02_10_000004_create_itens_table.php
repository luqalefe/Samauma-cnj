<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens', function (Blueprint $table) {
            $table->id();
            $table->string('eixo');
            $table->string('artigo');
            $table->string('requisito');
            $table->text('descricao')->nullable();
            $table->text('alinea')->nullable();
            $table->integer('pontos_maximos')->default(0);
            $table->integer('pontos_obtidos')->default(0);
            $table->boolean('requer_documento')->default(false);
            $table->foreignId('parent_id')->nullable()->constrained('itens')->nullOnDelete();
            $table->foreignId('setor_id')->nullable()->constrained('setores')->nullOnDelete();
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ponto_focal')->nullable();
            $table->string('status')->default('nao_iniciado');
            $table->date('prazo_inicio')->nullable();
            $table->date('prazo_fim')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('eixo');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens');
    }
};
