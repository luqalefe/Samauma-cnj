<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;

class ImportarMetasCnj extends Command
{
    protected $signature = 'metas:importar-pdf
                            {--file= : Caminho do PDF (padrão: data/anexos-premio-cnj-de-qualidade-2026-pos-edital-marcas.pdf)}';

    protected $description = 'Importa metas do Prêmio CNJ (Apenas TRE) a partir de PDF e popula a tabela itens';

    private const EIXO_MAP = [
        '9' => 'Governança',
        '10' => 'Produtividade',
        '11' => 'Transparência',
        '12' => 'Dados e Tecnologia',
    ];

    public function handle(): int
    {
        $filePath = $this->option('file')
            ?? base_path('data/anexos-premio-cnj-de-qualidade-2026-pos-edital-marcas.pdf');

        if (!file_exists($filePath)) {
            $this->error("Arquivo não encontrado: {$filePath}");
            return self::FAILURE;
        }

        $this->info('🔄 Parsing PDF...');

        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();
        $text = preg_replace('/[ \t]+/', ' ', $text); // Normaliza espaços

        $this->info('📄 Texto extraído.');

        if ($this->confirm('Deseja limpar a tabela itens (e relacionadas) antes de importar?', true)) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('itens')->truncate();
            DB::table('itens_regras_tecnicas')->truncate();
            DB::table('itens_excecoes')->truncate();
            DB::table('avaliacoes_mensais')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->info('🗑️  Tabelas limpas.');
        }

        // Divide o texto pelos Artigos
        $articlePattern = '/(?=Art\.\s*\d+[º°]?(?:\s*,\s*[IVXLCDM]+)?)/u';
        $rawBlocks = preg_split($articlePattern, $text, -1, PREG_SPLIT_NO_EMPTY);

        $this->info('📦 Blocos encontrados: ' . count($rawBlocks));

        $insertedCount = 0;
        $now = now();

        foreach ($rawBlocks as $block) {
            $block = trim($block);
            if (empty($block))
                continue;

            // Filtro TRE
            if (!$this->ehRelevanteParaTRE($block)) {
                continue;
            }

            if (!preg_match('/^(Art\.\s*(\d+)[º°]?(?:\s*,\s*[IVXLCDM]+(?:\s*,\s*[a-z])?)?)[\s\-–—.]/u', $block, $artMatch)) {
                continue;
            }

            $codigoExibicaoArtigo = trim($artMatch[1]); // Ex: "Art. 9º"
            $artNumber = $artMatch[2]; // Ex: "9"
            $eixo = self::EIXO_MAP[$artNumber] ?? 'Outros';

            $afterArt = mb_substr($block, mb_strlen($artMatch[0]));
            $nomeArtigo = $this->extractRequisito($afterArt); // "Requisito" agora é o Nome/Título
            $pontosMaximos = $this->extractPontos($block);
            $alineas = $this->extractAlineasHierarquicas($block);

            // ── Insere o item pai (TIPO GRUPO/ARTIGO) ──
            $parentId = DB::table('itens')->insertGetId([
                // Campos Novos
                'codigo_exibicao' => $codigoExibicaoArtigo,
                'nome' => $nomeArtigo,
                'tipo' => 'grupo', // Artigo é um grupo de critérios
                'ano_exercicio' => 2026,
                'tipo_calculo' => 'soma_com_teto', // Padrão
                'formula_expressao' => null,
                'pontos_teto_grupo' => $pontosMaximos, // Pontos do artigo muitas vezes são o teto da soma das alíneas

                // Campos Antigos/Compatibilidade
                'eixo' => $eixo,
                'artigo' => $codigoExibicaoArtigo,
                'requisito' => $nomeArtigo,
                'alinea' => null,
                'descricao' => $this->cleanText($afterArt),
                'pontos_maximos' => $pontosMaximos,
                'pontos_obtidos' => 0,
                'parent_id' => null,
                'requer_documento' => false, // Grupo geralmente não requer doc, os filhos sim
                'status' => 'nao_iniciado',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $insertedCount++;

            if (empty($alineas))
                continue;

            // ── Insere as alíneas com hierarquia (TIPO CRITERIO) ──
            // Detecta letras excluídas para TRE no nível do bloco
            $letrasExcluidas = $this->detectarLetrasExcluidasTRE($block);

            // Mapa de letras para IDs: ['a' => 5, 'b' => 8, ...]
            $letraParaId = [];

            foreach ($alineas as $alinea) {
                // Pula alíneas cuja letra está na lista de exclusão do bloco
                if (in_array($alinea['letra_base'], $letrasExcluidas))
                    continue;

                // Pula sub-alíneas cujo pai está na lista de exclusão
                if ($alinea['letra_pai'] && in_array($alinea['letra_pai'], $letrasExcluidas))
                    continue;

                // Filtra alíneas explicitamente excluídas para TRE
                if ($this->ehExcluidoParaTRE($alinea['texto']))
                    continue;

                $pontosAlinea = $this->extractPontos($alinea['texto']) ?: 0;
                $nomeAlinea = $this->extractRequisito($alinea['texto']);

                // Determina o parent_id baseado no nível
                if ($alinea['nivel'] === 1) {
                    // Alínea de letra (a, b, c) → pai é o artigo (Grupo)
                    $alineaParentId = $parentId;
                } else {
                    // Sub-alínea (a.1, a.2) → pai é a alínea da letra (Critério Pai)
                    $letraPai = $alinea['letra_pai'];
                    $alineaParentId = $letraParaId[$letraPai] ?? $parentId;
                }

                $alineaId = DB::table('itens')->insertGetId([
                    // Campos Novos
                    'codigo_exibicao' => $alinea['letra'], // Ex: "a)", "a.1)"
                    'nome' => $nomeAlinea,
                    'tipo' => 'criterio',
                    'ano_exercicio' => 2026,
                    'tipo_calculo' => 'manual', // Critério folha geralmente é manual ou booleano
                    'formula_expressao' => null,
                    'pontos_teto_grupo' => null,

                    // Campos Antigos
                    'eixo' => $eixo,
                    'artigo' => $codigoExibicaoArtigo,
                    'requisito' => $nomeAlinea,
                    'alinea' => $alinea['letra'],
                    'descricao' => $this->cleanText($alinea['texto']),
                    'pontos_maximos' => $pontosAlinea,
                    'pontos_obtidos' => 0,
                    'parent_id' => $alineaParentId,
                    'requer_documento' => true,
                    'status' => 'nao_iniciado',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $insertedCount++;

                // Registra o ID da alínea de letra para lookup dos filhos
                if ($alinea['nivel'] === 1) {
                    $letraParaId[$alinea['letra_base']] = $alineaId;
                }
            }
        }

        $this->info("✅ Importação (TRE) concluída! {$insertedCount} itens inseridos na nova estrutura.");
        return self::SUCCESS;
    }

    /**
     * Verifica se o texto menciona critérios aplicáveis a TREs.
     */
    private function ehRelevanteParaTRE(string $texto): bool
    {
        $t = mb_strtolower($texto);

        $temInclusao = str_contains($t, 'eleitoral') ||
            str_contains($t, 'eleitorais') ||
            str_contains($t, 'todos');

        $temExclusao = (str_contains($t, 'exceto') && (str_contains($t, 'eleitoral') || str_contains($t, 'eleitorais'))) ||
            str_contains($t, 'não se aplica aos tribunais regionais eleitorais') ||
            str_contains($t, 'não se aplica à justiça eleitoral') ||
            str_contains($t, 'não se aplica aos tribunais eleitorais');

        return $temInclusao && !$temExclusao;
    }

    /**
     * Verifica se o texto da alínea exclui explicitamente o TRE.
     * Usado para filtrar sub-itens como f) e g) do Codex.
     */
    private function ehExcluidoParaTRE(string $texto): bool
    {
        $t = mb_strtolower($texto);

        return (str_contains($t, 'não se aplica') && (str_contains($t, 'eleitoral') || str_contains($t, 'eleitorais'))) ||
            (str_contains($t, 'exceto') && (str_contains($t, 'eleitoral') || str_contains($t, 'eleitorais')));
    }

    /**
     * Detecta quais letras de alínea são excluídas para TRE no texto do bloco.
     * Ex: "Os itens (f) e (g) não se aplicam à Justiça Eleitoral" → ['f', 'g']
     */
    private function detectarLetrasExcluidasTRE(string $block): array
    {
        $letras = [];
        $t = mb_strtolower($block);

        // Procura frases que contenham "não se aplica" + "eleitoral"
        // e extrai letras entre parênteses somente dessas frases
        // Captura trecho: "(f) e (g) não se aplicam à Justiça Eleitoral"
        $patterns = [
            // "itens (f) e (g) não se aplicam à Justiça Eleitoral"
            '/ite(?:m|ns)\s+(?:\([a-z]\)\s*(?:e\s*)?)+.*?não\s+se\s+aplica.*?eleitoral/iu',
            // "não se aplicam à Justiça Eleitoral" com letras antes
            '/\([a-z]\)(?:\s+e\s+\([a-z]\))*\s+não\s+se\s+aplica.*?eleitoral/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $t, $sentenceMatches)) {
                foreach ($sentenceMatches[0] as $sentence) {
                    // Extrai apenas as letras dessa frase específica
                    if (preg_match_all('/\(([a-z])\)/u', $sentence, $letraMatches)) {
                        $letras = array_merge($letras, $letraMatches[1]);
                    }
                }
            }
        }

        return array_unique($letras);
    }

    private function extractRequisito(string $text): string
    {
        $text = trim($text);
        $lines = preg_split('/\r?\n/', $text);

        $titleParts = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (empty($line))
                break;
            if (preg_match('/^\d+\s*(?:pontos?|pts)/iu', $line))
                break;
            if (preg_match('/^(?:Até|Atribuir|Serão?|Pela?|Pelo)\s/iu', $line))
                break;
            if (preg_match('/^[a-z](?:\.\d+)?\)\s/u', $line))
                break;

            $titleParts[] = $line;

            if (preg_match('/\.\s*$/u', $line))
                break;
        }

        $title = implode(' ', $titleParts);
        $title = rtrim($title, '., ');

        if (empty($title))
            return mb_substr($text, 0, 100) . '...';

        if (mb_strlen($title) > 200)
            $title = mb_substr($title, 0, 200) . '...';

        return $title;
    }

    private function extractPontos(string $text): float
    {
        // Padrões de pontuação comuns
        // 1. "Até 10 pontos", "10 pontos", "10 pts"
        // 2. "valendo 5 pontos"
        // 3. "5,5 pontos" (converter vírgula para ponto)
        // 4. Pontuação isolada no final de linha ou string: "Pontos: 10"

        $patterns = [
            '/(?:Até|valendo|valor)?\s*(\d+(?:[.,]\d+)?)\s*(?:pontos?|pts)/iu',
            '/Pontua(?:ção|cao)\s*(?:máxima)?\s*[:=]\s*(\d+(?:[.,]\d+)?)/iu',
            '/(\d+(?:[.,]\d+)?)\s*pontos/iu',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $valor = str_replace(',', '.', $m[1]);
                return (float) $valor;
            }
        }

        return 0.0;
    }

    /**
     * Extrai alíneas com informação hierárquica.
     *
     * Retorna array com:
     *   'letra'      => 'a)' ou 'a.1)'
     *   'letra_base' => 'a' (apenas a letra, sem número)
     *   'letra_pai'  => 'a' (para sub-alíneas) ou null (para alíneas de letra)
     *   'nivel'      => 1 (letra) ou 2 (sub-alínea)
     *   'texto'      => conteúdo
     */
    private function extractAlineasHierarquicas(string $block): array
    {
        $alineas = [];
        // Captura "a)", "b)", "a.1)", "c.3)" etc.
        $pattern = '/(?:^|\n)\s*([a-z])(?:\.(\d+))?\)\s+/u';

        if (!preg_match_all($pattern, $block, $matches, PREG_OFFSET_CAPTURE))
            return [];

        $count = count($matches[0]);

        for ($i = 0; $i < $count; $i++) {
            $letraBase = $matches[1][$i][0]; // 'a', 'b', 'c'...
            $subNumero = ($matches[2][$i][0] ?? '');  // '1', '2'... or ''
            $startPos = $matches[0][$i][1] + strlen($matches[0][$i][0]);
            $endPos = ($i + 1 < $count) ? $matches[0][$i + 1][1] : strlen($block);
            $texto = substr($block, $startPos, $endPos - $startPos);

            if ($subNumero !== '') {
                // Sub-alínea: a.1), a.2), b.1)...
                $alineas[] = [
                    'letra' => "{$letraBase}.{$subNumero})",
                    'letra_base' => $letraBase,
                    'letra_pai' => $letraBase,
                    'nivel' => 2,
                    'texto' => trim($texto),
                ];
            } else {
                // Alínea principal: a), b), c)...
                $alineas[] = [
                    'letra' => "{$letraBase})",
                    'letra_base' => $letraBase,
                    'letra_pai' => null,
                    'nivel' => 1,
                    'texto' => trim($texto),
                ];
            }
        }

        return $alineas;
    }

    private function cleanText(string $text): string
    {
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $lines = array_map('trim', explode("\n", $text));
        $text = implode("\n", $lines);
        $text = trim($text);
        if (mb_strlen($text) > 5000)
            $text = mb_substr($text, 0, 5000) . '…';
        return $text;
    }
}
