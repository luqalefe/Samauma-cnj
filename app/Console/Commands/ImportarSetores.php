<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportarSetores extends Command
{
    protected $signature = 'setores:importar
                            {--truncate : Limpa a tabela antes de importar}';

    protected $description = 'Importa setores do TRE-AC a partir das APIs de lotação e férias (visao.tre-ac.jus.br), com hierarquia pai/filho';

    private const API_LOTACAO = 'https://visao.tre-ac.jus.br/painel/view/api/lotacao/';
    private const API_FERIAS = 'https://visao.tre-ac.jus.br/painel/view/api/ferias/setor/index.php';

    /**
     * Mapeamento sigla → nome completo dos setores do TRE-AC.
     */
    private const NOMES = [
        // Presidência / Gabinetes
        'ASPRES' => 'Assessoria da Presidência',
        'GAPRES' => 'Gabinete da Presidência',
        'GACRE' => 'Gabinete da Corregedoria',
        'GADG' => 'Gabinete da Diretoria-Geral',
        'GAJUD' => 'Gabinete de Juiz Auxiliar',
        'GASAOF' => 'Gabinete da SAOF',
        'DG' => 'Diretoria-Geral',

        // Assessorias
        'ASCOM' => 'Assessoria de Comunicação Social',
        'ASCRE' => 'Assessoria da Corregedoria',
        'ASGIM' => 'Assessoria de Gestão e Inovação em Métodos',
        'ASGOVSAOF' => 'Assessoria de Governança e SAOF',
        'ASJUIZ' => 'Assessoria de Juízes',
        'ASJUR' => 'Assessoria Jurídica',
        'ASPLAN' => 'Assessoria de Planejamento Estratégico',
        'AGECON' => 'Assessoria de Gestão de Contratos',
        'AGEL' => 'Assessoria de Gestão Eleitoral',

        // Coordenadorias
        'COAUDI' => 'Coordenadoria de Auditoria Interna',
        'COCRE' => 'Coordenadoria da Corregedoria',
        'COFIN' => 'Coordenadoria de Finanças',
        'COGEP' => 'Coordenadoria de Gestão de Pessoas',
        'COMAP' => 'Coordenadoria de Material e Patrimônio',
        'COSEG' => 'Coordenadoria de Segurança',
        'COSES' => 'Coordenadoria de Serviços e Engenharia',

        // Outros setores
        'CIE' => 'Comissão de Informática e Eleições',
        'CRIP' => 'Central de Registro e Informações Processuais',
        'CSCOR' => 'Cartório da Corregedoria',
        'EJE' => 'Escola Judiciária Eleitoral',
        'GSTI' => 'Gestão de Segurança da Tecnologia da Informação',
        'NISIPJ' => 'Núcleo de Inteligência e Segurança Institucional do PJ',
        'NULAB' => 'Núcleo de Laboratório',
        'OUVIDORIA' => 'Ouvidoria Regional Eleitoral',
        'SAOF' => 'Secretaria de Administração, Orçamento e Finanças',
        'SAREMI' => 'Seção de Arquivo e Memória Institucional',
        'SASBEN' => 'Seção de Assistência e Saúde e Bem-Estar',
        'SCPE' => 'Seção de Controle de Pessoal e Estágio',
        'SCSEG' => 'Seção de Controle de Segurança',
        'SDBD' => 'Seção de Desenvolvimento e Banco de Dados',
        'SDP' => 'Seção de Desenvolvimento de Pessoas',
        'SEADE' => 'Seção de Administração de Edifícios',
        'SEANT' => 'Seção de Análise e Tratamento',
        'SEAPTIC' => 'Seção de Apoio à TIC',
        'SECAP' => 'Seção de Capacitação',
        'SECARF' => 'Seção de Controle e Análise de Registros Financeiros',
        'SECEP' => 'Seção de Contratos, Editais e Planejamento',
        'SECON' => 'Seção de Contabilidade',
        'SEDES' => 'Seção de Desenvolvimento de Sistemas',
        'SEGLOF' => 'Seção de Gestão de Logística e Frotas',
        'SEJUD' => 'Secretaria Judiciária',
        'SEMAP' => 'Seção de Material e Patrimônio',
        'SEPAG' => 'Seção de Pagamento',
        'SEREDE' => 'Seção de Redes',
        'SETRAN' => 'Seção de Transporte',
        'SEUE' => 'Seção de Urbanização e Engenharia',
        'SGEC' => 'Secretaria de Gestão e Comunicação',
        'SJIP' => 'Seção Judiciária de Informações Processuais',
        'SLC' => 'Seção de Licitações e Compras',
        'SLDAG' => 'Seção de Legislação e Dados em Gestão',
        'SOC' => 'Seção de Orçamento e Custos',
        'SPEF' => 'Seção de Programação e Execução Financeira',
        'SPEO' => 'Seção de Pessoal e Operações',
        'SRDP' => 'Seção de Registros e Diligências Processuais',
        'SRJAR' => 'Seção de Registros Judiciais e Arquivo',
        'SSEC' => 'Secretaria da Sessão',
        'SSU' => 'Seção de Suporte',
        'STI' => 'Secretaria de Tecnologia da Informação',
        'DST' => 'Divisão de Suporte Técnico',
    ];

    public function handle(): int
    {
        // ══════════════════════════════════════════════
        // PASSO 1: Importar setores do API de lotação
        // ══════════════════════════════════════════════
        $this->info('🔄 Passo 1: Buscando setores da API de lotação...');

        $response = Http::timeout(10)->get(self::API_LOTACAO);

        if ($response->failed()) {
            $this->error('❌ Falha ao acessar a API de lotação.');
            return self::FAILURE;
        }

        $lotacoes = $response->json();

        if (empty($lotacoes)) {
            $this->error('❌ API retornou dados vazios.');
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('setores')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->warn('🗑️  Tabela setores truncada.');
        }

        // Inserir/atualizar todos os setores (flat, sem hierarquia ainda)
        $siglas = [];
        foreach ($lotacoes as $lotacao) {
            $sigla = trim($lotacao['SIGLA_UNID_TSE']);
            $nome = self::NOMES[$sigla] ?? $this->gerarNome($sigla);
            $siglas[] = $sigla;

            $setor = DB::table('setores')->where('sigla', $sigla)->first();

            if ($setor) {
                DB::table('setores')->where('id', $setor->id)->update([
                    'nome' => $nome,
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('setores')->insert([
                    'nome' => $nome,
                    'sigla' => $sigla,
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->info("  ✅ " . count($siglas) . " setores inseridos/atualizados.");

        // ══════════════════════════════════════════════
        // PASSO 2: Descobrir hierarquia via API de férias
        // ══════════════════════════════════════════════
        $this->info('🔄 Passo 2: Descobrindo hierarquia pai/filho via API de férias...');
        $this->newLine();

        // Mapear sigla → id
        $setorIdMap = DB::table('setores')
            ->pluck('id', 'sigla')
            ->toArray();

        // Para cada setor, chamar a API de férias e ver quais sub-siglas aparecem
        $childrenMap = []; // pai_sigla => [filho_sigla, ...]
        $alreadyChild = []; // siglas que já foram identificadas como filhas

        $bar = $this->output->createProgressBar(count($siglas));
        $bar->setFormat(" %current%/%max% [%bar%] %message%");

        foreach ($siglas as $sigla) {
            $bar->setMessage($sigla);
            $bar->advance();

            try {
                $resp = Http::timeout(8)->get(self::API_FERIAS, ['sigla' => $sigla]);

                if ($resp->failed()) {
                    continue;
                }

                $data = $resp->json();

                if (empty($data) || !is_array($data)) {
                    continue;
                }

                // Extrair siglas únicas das respostas
                $subSiglas = collect($data)
                    ->pluck('SIGLA_UNID_TSE')
                    ->map(fn($s) => trim($s))
                    ->unique()
                    ->filter(fn($s) => $s !== $sigla) // Remover o próprio setor
                    ->values()
                    ->toArray();

                if (!empty($subSiglas)) {
                    $childrenMap[$sigla] = $subSiglas;
                }
            } catch (\Exception $e) {
                // Silenciosamente continuar se API falhar para um setor
                continue;
            }
        }

        $bar->finish();
        $this->newLine(2);

        // ══════════════════════════════════════════════
        // PASSO 3: Resolver hierarquia e atualizar parent_id
        // ══════════════════════════════════════════════
        $this->info('🔄 Passo 3: Atualizando parent_id...');

        // Primeiro resetar todos os parent_id
        DB::table('setores')->update(['parent_id' => null]);

        // Resolver: se uma sigla aparece como filha de MUITOS pais,
        // escolher o pai mais "específico" (que tenha menos filhos)
        // Isso evita que setores que aparecem em vários níveis fiquem no nível errado
        $bestParent = []; // filho => pai mais específico

        // Ordenar pais por número de filhos (menos filhos = mais específico)
        $sortedParents = collect($childrenMap)
            ->sortBy(fn($children) => count($children))
            ->toArray();

        foreach ($sortedParents as $pai => $filhos) {
            foreach ($filhos as $filho) {
                // Se esse filho já é "pai" de outros, não devemos atribuí-lo como filho
                // a menos que o atual pai tenha MAIS filhos (é mais genérico)
                if (!isset($bestParent[$filho])) {
                    $bestParent[$filho] = $pai;
                }
                // Se já tem um pai, manter o que tem MAIS filhos (é mais genérico = é o pai real)
                // Na verdade, o contrário: o pai com MENOS filhos é mais específico,
                // mas queremos o pai DIRETO, que geralmente é o que lista esse filho
                // junto com seus irmãos.
            }
        }

        // Inserir setores que aparecem na API de férias mas não na lotação (ex: DST)
        foreach ($bestParent as $filho => $pai) {
            if (!isset($setorIdMap[$filho])) {
                $nome = self::NOMES[$filho] ?? $filho;
                $id = DB::table('setores')->insertGetId([
                    'nome' => $nome,
                    'sigla' => $filho,
                    'parent_id' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $setorIdMap[$filho] = $id;
                $this->line("  + Novo setor descoberto: {$filho} ({$nome})");
            }
        }

        // Atualizar parent_id
        $updates = 0;
        foreach ($bestParent as $filho => $pai) {
            if (isset($setorIdMap[$filho]) && isset($setorIdMap[$pai])) {
                DB::table('setores')
                    ->where('id', $setorIdMap[$filho])
                    ->update(['parent_id' => $setorIdMap[$pai]]);
                $updates++;
            }
        }

        $this->info("  ✅ {$updates} setores com parent_id atualizado.");

        // ══════════════════════════════════════════════
        // RELATÓRIO FINAL
        // ══════════════════════════════════════════════
        $this->newLine();
        $this->info('📊 Estrutura hierárquica:');
        $this->newLine();

        // Mostrar árvore
        $raizes = DB::table('setores')
            ->whereNull('parent_id')
            ->orderBy('sigla')
            ->get();

        foreach ($raizes as $raiz) {
            $this->line("  📁 <info>{$raiz->sigla}</info> — {$raiz->nome}");

            $filhos = DB::table('setores')
                ->where('parent_id', $raiz->id)
                ->orderBy('sigla')
                ->get();

            foreach ($filhos as $filho) {
                $this->line("     ├── <comment>{$filho->sigla}</comment> — {$filho->nome}");

                $netos = DB::table('setores')
                    ->where('parent_id', $filho->id)
                    ->orderBy('sigla')
                    ->get();

                foreach ($netos as $neto) {
                    $this->line("     │   └── {$neto->sigla} — {$neto->nome}");
                }
            }
        }

        $totalSetores = DB::table('setores')->count();
        $comPai = DB::table('setores')->whereNotNull('parent_id')->count();
        $this->newLine();
        $this->info("✅ Importação concluída! Total: {$totalSetores} setores ({$comPai} com parent_id).");

        return self::SUCCESS;
    }

    /**
     * Gera um nome legível para zonas eleitorais ou siglas desconhecidas.
     */
    private function gerarNome(string $sigla): string
    {
        if (preg_match('/^(\d+)ª?\s*ZE$/i', $sigla, $m)) {
            return "{$m[1]}ª Zona Eleitoral";
        }

        return $sigla;
    }
}
