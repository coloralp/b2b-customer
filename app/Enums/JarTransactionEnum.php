<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JarTransactionEnum: int implements HasLabel, HasColor
{
    case INCOME = 1;
    case EXPENSE = 2;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::INCOME => self::INCOME->name,
            self::EXPENSE => self::EXPENSE->name,
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::INCOME => 'success',
            self::EXPENSE => 'danger',
        };
    }
}
