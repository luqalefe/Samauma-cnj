<?php

namespace App\Filament\Resources\SetorResource\Pages;

use App\Filament\Resources\SetorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSetor extends CreateRecord
{
    protected static string $resource = SetorResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
