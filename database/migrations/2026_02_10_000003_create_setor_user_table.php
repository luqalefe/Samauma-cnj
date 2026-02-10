<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setor_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setor_id')->constrained('setores')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['setor_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setor_user');
    }
};
