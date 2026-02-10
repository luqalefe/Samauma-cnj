<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItensTreSeeder extends Seeder
{
    public function run(): void
    {
        $itens = [
            [
                'eixo' => 'Governança',
                'artigo' => 'Art. 9º, II',
                'titulo' => 'Gestão Participativa',
                'pontos' => 60,
                'desc' => 'Consultas/audiências públicas. Regra TRE: >5% part. ou >60 pessoas (30 pts); >1% a 5% ou >15 pessoas (15 pts).',
                'periodo' => '19/01/2026 a 31/07/2026',
            ],
            [
                'eixo' => 'Governança',
                'artigo' => 'Art. 9º, V',
                'titulo' => 'Combate ao Assédio',
                'pontos' => 40,
                'desc' => 'Semana de combate. Regra TRE: Em ano eleitoral, pode ser em qualquer semana de maio.',
                'periodo' => '01/05/2026 a 31/05/2026',
            ],
            [
                'eixo' => 'Governança',
                'artigo' => 'Art. 9º, XIII',
                'titulo' => 'Capacitação DH/Gênero',
                'pontos' => 20,
                'desc' => 'Cursos para magistrados e servidores. Aplica-se a TREs.',
                'periodo' => '01/08/2025 a 31/07/2026',
            ],
            [
                'eixo' => 'Governança',
                'artigo' => 'Art. 9º, XXIII',
                'titulo' => 'PopRuaJud',
                'pontos' => 20,
                'desc' => 'Política para pessoas em situação de rua. Pontuação via índice IPopRuaJud.',
                'periodo' => '01/06/2025 a 31/05/2026',
            ],
            [
                'eixo' => 'Produtividade',
                'artigo' => 'Art. 10, II',
                'titulo' => 'Tempo Médio Processos Pendentes',
                'pontos' => 70,
                'desc' => 'Meta TRE: Até 150 dias (70 pts); 151-200 (50 pts); 201-300 (25 pts).',
                'periodo' => '31/07/2026',
            ],
            [
                'eixo' => 'Produtividade',
                'artigo' => 'Art. 10, IV',
                'titulo' => 'Metas Nacionais',
                'pontos' => 60,
                'desc' => 'Cumprimento das metas nacionais de 2025. Teto TRE: 60 pontos.',
                'periodo' => '01/01/2025 a 31/12/2025',
            ],
            [
                'eixo' => 'Produtividade',
                'artigo' => 'Art. 10, V',
                'titulo' => 'Redução Processos Antigos',
                'pontos' => 70,
                'desc' => 'Processos ajuizados até 2023. Meta TRE: Até 1% pendente (70 pts); 1.01-2% (35 pts).',
                'periodo' => '31/07/2026',
            ],
            [
                'eixo' => 'Produtividade',
                'artigo' => 'Art. 10, IX',
                'titulo' => 'Celeridade Ações Penais',
                'pontos' => 40,
                'desc' => 'Tempo médio pendentes líquidos. Meta TRE: Até 400 dias (20 pts); 401-600 (10 pts).',
                'periodo' => '31/07/2026',
            ],
            [
                'eixo' => 'Produtividade',
                'artigo' => 'Art. 10, XI',
                'titulo' => 'IAD > 100%',
                'pontos' => 50,
                'desc' => 'Unidades com IAD > 100%. Regra TRE: Período base 01/08/2025 a 31/05/2026 (devido eleições).',
                'periodo' => '01/08/2025 a 31/05/2026',
            ],
            [
                'eixo' => 'Dados e Tecnologia',
                'artigo' => 'Art. 12, I',
                'titulo' => 'DataJud Qualidade',
                'pontos' => 170,
                'desc' => 'Validação de campos e movimentos. Itens c.3 e c.7 NÃO se aplicam ao TRE.',
                'periodo' => '01/01/2020 a 31/07/2026',
            ],
            [
                'eixo' => 'Dados e Tecnologia',
                'artigo' => 'Art. 12, VII',
                'titulo' => 'Plataforma Codex',
                'pontos' => 135,
                'desc' => 'Integração Codex. Itens de Latência (f) e Download (g) NÃO se aplicam ao TRE.',
                'periodo' => '01/01/2026 a 31/07/2026',
            ],
            [
                'eixo' => 'Dados e Tecnologia',
                'artigo' => 'Art. 12, X',
                'titulo' => 'Domicílio Judicial Eletrônico',
                'pontos' => 20,
                'desc' => 'Citações via Domicílio. Meta: >40% das citações elegíveis.',
                'periodo' => '01/01/2026 a 31/07/2026',
            ],
        ];

        $now = Carbon::now();

        foreach ($itens as $item) {
            [$prazoInicio, $prazoFim] = $this->parsePeriodo($item['periodo']);

            DB::table('itens')->insert([
                'eixo' => $item['eixo'],
                'artigo' => $item['artigo'],
                'requisito' => $item['titulo'],
                'descricao' => $item['desc'],
                'pontos_maximos' => $item['pontos'],
                'pontos_obtidos' => 0,
                'requer_documento' => true,
                'status' => 'nao_iniciado',
                'prazo_inicio' => $prazoInicio,
                'prazo_fim' => $prazoFim,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Parse the "periodo" string into [prazo_inicio, prazo_fim] dates.
     *
     * Supported formats:
     *   "19/01/2026 a 31/07/2026"  → two dates
     *   "31/07/2026"               → null start, single end date
     *   "Maio 2026"                → first and last day of the month
     *   "Ano 2025"                 → 2025-01-01 to 2025-12-31
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function parsePeriodo(string $periodo): array
    {
        // Range: "dd/mm/yyyy a dd/mm/yyyy"
        if (preg_match('#^(\d{2}/\d{2}/\d{4})\s+a\s+(\d{2}/\d{2}/\d{4})$#', $periodo, $m)) {
            return [
                Carbon::createFromFormat('d/m/Y', $m[1])->format('Y-m-d'),
                Carbon::createFromFormat('d/m/Y', $m[2])->format('Y-m-d'),
            ];
        }

        // Single date: "dd/mm/yyyy" (deadline only)
        if (preg_match('#^(\d{2}/\d{2}/\d{4})$#', $periodo, $m)) {
            return [
                null,
                Carbon::createFromFormat('d/m/Y', $m[1])->format('Y-m-d'),
            ];
        }

        // "Ano YYYY"
        if (preg_match('#^Ano\s+(\d{4})$#i', $periodo, $m)) {
            $year = (int) $m[1];
            return [
                Carbon::create($year, 1, 1)->format('Y-m-d'),
                Carbon::create($year, 12, 31)->format('Y-m-d'),
            ];
        }

        // Month/Year: "Maio 2026", "Janeiro 2025", etc.
        $months = [
            'janeiro' => 1,
            'fevereiro' => 2,
            'março' => 3,
            'abril' => 4,
            'maio' => 5,
            'junho' => 6,
            'julho' => 7,
            'agosto' => 8,
            'setembro' => 9,
            'outubro' => 10,
            'novembro' => 11,
            'dezembro' => 12,
        ];

        if (preg_match('#^(\w+)\s+(\d{4})$#u', $periodo, $m)) {
            $monthName = mb_strtolower($m[1]);
            $year = (int) $m[2];

            if (isset($months[$monthName])) {
                $month = $months[$monthName];
                $start = Carbon::create($year, $month, 1);
                return [
                    $start->format('Y-m-d'),
                    $start->copy()->endOfMonth()->format('Y-m-d'),
                ];
            }
        }

        // Fallback
        return [null, null];
    }
}
