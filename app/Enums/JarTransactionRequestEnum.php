<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum JarTransactionRequestEnum: int implements HasColor, HasLabel
{
    case CREATED = 1;
    case APPROVED = 2;
    case REJECTED = 3;


    public function getColor(): string|array|null
    {
        return match ($this) {
            self::CREATED => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::CREATED => self::CREATED->name,
            self::APPROVED => self::APPROVED->name,
            self::REJECTED => self::REJECTED->name
        };
    }
}
