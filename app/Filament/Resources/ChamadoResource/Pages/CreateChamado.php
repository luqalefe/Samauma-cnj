<?php

namespace App\Filament\Resources\ChamadoResource\Pages;

use App\Filament\Resources\ChamadoResource;
use Filament\Resources\Pages\CreateRecord;

class CreateChamado extends CreateRecord
{
    protected static string $resource = ChamadoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['solicitante_id'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
