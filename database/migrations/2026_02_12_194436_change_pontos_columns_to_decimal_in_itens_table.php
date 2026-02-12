<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('itens', function (Blueprint $table) {
            $table->decimal('pontos_maximos', 8, 2)->default(0)->change();
            $table->decimal('pontos_obtidos', 8, 2)->default(0)->change();

            // Verifica se a coluna existe antes de tentar mudar (ela foi criada na migration anterior)
            if (Schema::hasColumn('itens', 'pontos_teto_grupo')) {
                $table->decimal('pontos_teto_grupo', 8, 2)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('itens', function (Blueprint $table) {
            $table->integer('pontos_maximos')->default(0)->change();
            $table->integer('pontos_obtidos')->default(0)->change();

            if (Schema::hasColumn('itens', 'pontos_teto_grupo')) {
                $table->integer('pontos_teto_grupo')->nullable()->change();
            }
        });
    }
};
