<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('itens')->cascadeOnDelete();
            $table->text('descricao');
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('responsavel_nome')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim_prevista');
            $table->datetime('data_entrega_real')->nullable();
            $table->string('status')->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('data_fim_prevista');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
