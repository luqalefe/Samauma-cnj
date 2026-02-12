<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // --- ALTERAÇÕES NA TABELA ITENS ---
        Schema::table('itens', function (Blueprint $table) {
            // Identificação e Classificação
            $table->string('codigo_exibicao')->nullable()->after('id')->comment('Ex: A, b.1, 12.VII');
            $table->string('nome')->nullable()->after('codigo_exibicao')->comment('Ex: Validação Codex');
            $table->string('tipo')->default('criterio')->after('descricao')->comment("enum: 'eixo', 'grupo', 'criterio'");
            $table->integer('ano_exercicio')->default(2026)->after('tipo');

            // Lógica de Cálculo
            $table->string('tipo_calculo')->default('soma_com_teto')->after('ano_exercicio')
                ->comment("enum: 'soma_simples', 'soma_com_teto', 'melhor_nota', 'faixa_percentual', 'formula_customizada', 'booleano'");
            $table->string('formula_expressao')->nullable()->after('tipo_calculo')->comment('Ex: (PAmbos / (PAmbos + PCodex)) * 100');

            // Pontuação
            // pontos_maximos já existe, renomeando ou mantendo? O DBML pede 'pontos_maximos_item'. 
            // Vou assumir que 'pontos_maximos' existente serve, mas adicionarei o teto do grupo.
            $table->integer('pontos_teto_grupo')->nullable()->after('pontos_maximos')->comment('Trava de pontuação máxima para grupos');

            // Gestão (setor_id e responsavel_id já existem)
            // prazo_limite mapeia para prazo_fim (já existe)

            // Soft Deletes já existe
        });

        // --- TABELA ITENS_REGRAS_TECNICAS ---
        Schema::create('itens_regras_tecnicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('itens')->onDelete('cascade');

            // Segmentação
            $table->string('segmento_justica')->default('TODOS')
                ->comment("enum: 'ESTADUAL', 'FEDERAL', 'TRABALHO', 'ELEITORAL', 'MILITAR', 'SUPERIOR', 'TODOS'");

            // Para Fórmulas
            $table->string('variavel_codinome')->nullable()->comment('Ex: PAmbos, PCodex. Null se for regra simples');

            // Definição da Regra
            $table->string('campo_analise')->nullable()->comment('Ex: Pessoa.numeroDocumentoPrincipal');
            $table->string('operador_logico')->default('>=');

            // Para Faixas de Pontuação
            $table->decimal('meta_percentual', 5, 2)->nullable();
            $table->integer('pontos_se_atingido')->nullable();

            // Instruções Técnicas
            $table->text('descricao_tecnica')->nullable();
            $table->text('query_config_json')->nullable()->comment('JSON técnico para configurar a query/API');

            $table->timestamps();
        });

        // --- TABELA ITENS_EXCECOES ---
        Schema::create('itens_excecoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('itens')->onDelete('cascade');

            $table->string('tipo_excecao')->comment("enum: 'classe', 'movimento', 'assunto'");
            $table->string('codigo_referencia')->comment('Ex: 1682, 32, 51');
            $table->string('descricao')->nullable();
            $table->string('segmento_justica')->default('TODOS');

            $table->timestamps();
        });

        // --- TABELA AVALIACOES_MENSAIS ---
        Schema::create('avaliacoes_mensais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('itens')->onDelete('cascade');

            $table->date('mes_referencia');
            $table->decimal('percentual_alcancado', 5, 2)->nullable();
            $table->integer('pontos_conquistados')->nullable();

            $table->dateTime('data_calculo')->nullable();
            $table->text('log_calculo')->nullable()->comment('JSON com os valores das variáveis usadas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avaliacoes_mensais');
        Schema::dropIfExists('itens_excecoes');
        Schema::dropIfExists('itens_regras_tecnicas');

        Schema::table('itens', function (Blueprint $table) {
            $table->dropColumn([
                'codigo_exibicao',
                'nome',
                'tipo',
                'ano_exercicio',
                'tipo_calculo',
                'formula_expressao',
                'pontos_teto_grupo',
            ]);
        });
    }
};
