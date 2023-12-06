<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: int implements HasColor, HasLabel
{
    case APPROVE = 1;
    case REJECT = 2;
    case DECLINE = 3;
    case CREATED = 4;

    case DELIVERED = 5;

    case RESERVE = 6;

    case AUTO_REJECT = 7;
    case RETURNED = 8;

    case PENDING = 9;

    case ALL = -1;


    public static function defineStatus($id)
    {
        $id = (int)$id;

        return match ($id) {
            self::APPROVE->value => 'Onaylanmış',
            self::REJECT->value => 'Reddedilmiş',
            self::DECLINE->value => 'Iptal',
            self::CREATED->value => 'Yeni',
            self::DELIVERED->value => 'Teslim Edilmiş',
            self::RESERVE->value => 'Reserve',
            self::AUTO_REJECT->value => 'Otamatik İptal',
            self::RETURNED->value => 'Geri İade',
            self::PENDING->value => 'Beklenen'
        };
    }


    public static function defineColor($id): string
    {
        $id = (int)$id;

        return match ($id) {
            self::APPROVE->value, self::DELIVERED->value => 'success',
            self::REJECT->value, self::RESERVE->value, self::RETURNED->value, self::PENDING->value => 'warning',
            self::DECLINE->value, self::AUTO_REJECT->value => 'danger',
            self::CREATED->value => 'info'
        };
    }

    //filament

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::APPROVE, self::DELIVERED => 'success',
            self::REJECT, self::RESERVE, self::RETURNED, self::PENDING => 'warning',
            self::CREATED => 'info',
            default => 'danger',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::APPROVE => 'Approved',
            self::REJECT => 'Rejected',
            self::DECLINE => 'Decline',
            self::CREATED => 'Created',
            self::DELIVERED => 'Delivered',
            self::RESERVE => 'Reserve',
            self::AUTO_REJECT => 'Automatic Return',
            self::RETURNED => 'Returned',
            self::PENDING => 'Waiting'
        };
    }
}
