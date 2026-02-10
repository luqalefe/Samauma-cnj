<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Setor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CsvImportSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = database_path('data/itens_premio.csv');

        if (!file_exists($csvPath)) {
            $this->command->warn("CSV file not found at: {$csvPath}");
            $this->command->info("Copy the legacy CSV to database/data/itens_premio.csv to import items.");
            return;
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) return;

        // Skip header line
        $header = fgetcsv($handle);

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 10) continue;

            $eixo = trim($row[1] ?? '');
            $pontos = (int) trim($row[2] ?? '0');
            $artigo = trim($row[3] ?? '');
            $requisito = trim($row[4] ?? '');
            $alinea = trim($row[5] ?? '');
            $requerDoc = Str::lower(trim($row[6] ?? '')) === 'sim';
            $setorNome = trim($row[7] ?? '');
            $pontoFocal = trim($row[8] ?? '');
            $statusRaw = trim($row[9] ?? '');

            if (empty($eixo) || empty($artigo)) continue;

            // Find or create setor by sigla
            $setor = null;
            if (!empty($setorNome)) {
                $sigla = preg_replace('/[\/\s].+/', '', $setorNome);
                $sigla = trim($sigla);
                $setor = Setor::where('sigla', $sigla)->first();
            }

            // Map status
            $status = match (Str::lower($statusRaw)) {
                'concluído', 'concluido' => 'concluido',
                'em andamento' => 'em_andamento',
                default => 'nao_iniciado',
            };

            Item::create([
                'eixo' => $eixo,
                'artigo' => $artigo,
                'requisito' => $requisito,
                'alinea' => $alinea,
                'pontos_maximos' => $pontos,
                'requer_documento' => $requerDoc,
                'setor_id' => $setor?->id,
                'ponto_focal' => $pontoFocal ?: null,
                'status' => $status,
                'prazo_inicio' => '2026-01-01',
                'prazo_fim' => '2026-07-31',
            ]);

            $count++;
        }

        fclose($handle);
        $this->command->info("Imported {$count} items from CSV.");
    }
}
