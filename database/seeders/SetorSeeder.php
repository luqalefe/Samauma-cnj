<?php

namespace Database\Seeders;

use App\Models\Setor;
use Illuminate\Database\Seeder;

class SetorSeeder extends Seeder
{
    public function run(): void
    {
        $setores = [
            ['nome' => 'Secretaria de Gestão de Pessoas', 'sigla' => 'SEGEP'],
            ['nome' => 'Secretaria de Governança', 'sigla' => 'SEGOV'],
            ['nome' => 'Subsecretaria de Sustentabilidade', 'sigla' => 'SUESS'],
            ['nome' => 'Núcleo de Justiça da Saúde', 'sigla' => 'NATJUS'],
            ['nome' => 'Coordenadoria de Apoio ao 2º Grau', 'sigla' => 'COOES'],
            ['nome' => 'Escola do Poder Judiciário', 'sigla' => 'ESJUD'],
            ['nome' => 'Comissão Permanente de Acessibilidade', 'sigla' => 'COPAI'],
            ['nome' => 'Núcleo de Acessibilidade e Inclusão', 'sigla' => 'NUACI'],
            ['nome' => 'Comissão de Prevenção ao Assédio', 'sigla' => 'COPEA'],
            ['nome' => 'Comissão de Políticas de Diversidade', 'sigla' => 'COPED'],
            ['nome' => 'Coordenadoria de Gestão de Memória', 'sigla' => 'CGMEM'],
            ['nome' => 'Coordenadoria de Gestão Documental', 'sigla' => 'COGMA'],
            ['nome' => 'Núcleo de Justiça Restaurativa', 'sigla' => 'NUPJR'],
            ['nome' => 'Grupo de Monitoramento e Fiscalização', 'sigla' => 'GMFTJ'],
            ['nome' => 'Centro de Apoio às Vítimas', 'sigla' => 'CEAVI'],
            ['nome' => 'Corregedoria-Geral', 'sigla' => 'COGER'],
            ['nome' => 'Secretaria de Tecnologia da Informação', 'sigla' => 'SETIC'],
            ['nome' => 'Gabinete Auxiliar 1', 'sigla' => 'GAUX1'],
            ['nome' => 'Gabinete Auxiliar 2', 'sigla' => 'GAUX2'],
            ['nome' => 'Gabinete Auxiliar 3', 'sigla' => 'GAUX3'],
            ['nome' => 'Coordenadoria de Combate à Violência', 'sigla' => 'COSIV'],
            ['nome' => 'Secretaria de Licitações', 'sigla' => 'SELGA'],
            ['nome' => 'Coordenadoria de Benefícios', 'sigla' => 'COBES'],
            ['nome' => 'Coordenadoria de Diversidade', 'sigla' => 'CODIV'],
            ['nome' => 'Coordenadoria de Apoio aos Servidores', 'sigla' => 'COAPS'],
        ];

        foreach ($setores as $setor) {
            Setor::firstOrCreate(
                ['sigla' => $setor['sigla']],
                $setor
            );
        }
    }
}
