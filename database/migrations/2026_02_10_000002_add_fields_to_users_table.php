<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('matricula')->unique()->nullable()->after('email');
            $table->foreignId('setor_id')->nullable()->after('matricula')->constrained('setores')->nullOnDelete();
            $table->string('status')->default('pendente')->after('setor_id');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['setor_id']);
            $table->dropColumn(['matricula', 'setor_id', 'status']);
            $table->dropSoftDeletes();
        });
    }
};
