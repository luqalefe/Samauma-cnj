<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserStatus: string implements HasLabel, HasColor
{
    case Ativo = 'ativo';
    case Pendente = 'pendente';
    case Bloqueado = 'bloqueado';

    public function getLabel(): string
    {
        return match ($this) {
            self::Ativo => 'Ativo',
            self::Pendente => 'Pendente',
            self::Bloqueado => 'Bloqueado',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Ativo => 'success',
            self::Pendente => 'warning',
            self::Bloqueado => 'danger',
        };
    }
}
