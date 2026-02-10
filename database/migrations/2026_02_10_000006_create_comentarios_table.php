<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->morphs('commentable'); // commentable_id + commentable_type
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('mensagem');
            $table->string('tipo')->default('orientacao'); // orientacao, cobranca, elogio
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};
