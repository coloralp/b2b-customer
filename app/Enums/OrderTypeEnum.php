<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderTypeEnum: int implements HasLabel, HasColor
{
    case FROM_API = 1; //eski 2

    case TO_CUSTOMER = 2;
    case FROM_CUSTOMER_PANEL = 3;

    public static function defineName(string|int $id = null): string
    {
        if (is_null($id)) {
            return 'Belirlenemedi';
        }

        $id = is_int($id) ? $id : (int)$id;
        return match ($id) {
            self::FROM_API->value => self::FROM_API->name,
            self::TO_CUSTOMER->value => self::TO_CUSTOMER->name,
        };
    }

    public function getName(): string
    {
        return match ($this) {
            self::FROM_API => self::FROM_API->name,
            self::TO_CUSTOMER => self::TO_CUSTOMER->name,
            self::FROM_CUSTOMER_PANEL => self::FROM_CUSTOMER_PANEL->name,
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::FROM_API => 'From APi',
            self::TO_CUSTOMER => 'Created by us',
            self::FROM_CUSTOMER_PANEL => 'Created by own',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::FROM_API => 'danger',
            self::TO_CUSTOMER => 'warning',
            self::FROM_CUSTOMER_PANEL => 'success',
        };
    }
}
