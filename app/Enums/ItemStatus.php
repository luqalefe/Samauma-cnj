<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ItemStatus: string implements HasLabel, HasColor, HasIcon
{
    case NaoIniciado = 'nao_iniciado';
    case EmAndamento = 'em_andamento';
    case Concluido = 'concluido';

    public function getLabel(): string
    {
        return match ($this) {
            self::NaoIniciado => 'Não Iniciado',
            self::EmAndamento => 'Em Andamento',
            self::Concluido => 'Concluído',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::NaoIniciado => 'gray',
            self::EmAndamento => 'warning',
            self::Concluido => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::NaoIniciado => 'heroicon-o-clock',
            self::EmAndamento => 'heroicon-o-arrow-path',
            self::Concluido => 'heroicon-o-check-circle',
        };
    }
}
