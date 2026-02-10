<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TarefaStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pendente = 'pendente';
    case EmAndamento = 'em_andamento';
    case Concluida = 'concluida';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::EmAndamento => 'Em Andamento',
            self::Concluida => 'Concluída',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pendente => 'danger',
            self::EmAndamento => 'warning',
            self::Concluida => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pendente => 'heroicon-o-exclamation-circle',
            self::EmAndamento => 'heroicon-o-arrow-path',
            self::Concluida => 'heroicon-o-check-circle',
        };
    }
}
