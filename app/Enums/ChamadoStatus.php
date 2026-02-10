<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum ChamadoStatus: string implements HasLabel, HasColor, HasIcon
{
    case Pendente = 'pendente';
    case Resolvido = 'resolvido';

    public function getLabel(): string
    {
        return match ($this) {
            self::Pendente => 'Pendente',
            self::Resolvido => 'Resolvido',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Pendente => 'danger',
            self::Resolvido => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Pendente => 'heroicon-o-exclamation-triangle',
            self::Resolvido => 'heroicon-o-check-badge',
        };
    }
}
