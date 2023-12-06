<?php

namespace App\Enums;

use Ramsey\Uuid\Type\Integer;

enum KeyStatus: int
{
    case ACTIVE = 1;
    case PASSIVE = 2;

    case RESERVED = 3;

    case SOLD = 4;
    case REFUNDED = 5;
    case DELIVERED = 6;
    case KINGUIN_STOCK = 7;

    case CANCEL = 8;
    case DELETED = 9;
    case BUY = 10;

    public static function defineStatus($statusCode): string
    {
        $statusCode = (int)$statusCode;

        return match ($statusCode) {
            self::ACTIVE->value => 'Aktif',
            self::PASSIVE->value => 'Pasif',
            self::RESERVED->value => 'Reserve',
            self::SOLD->value => 'Satılmış',
            self::REFUNDED->value => 'İade',
            self::DELIVERED->value => 'Teslim edilmiş',
            self::KINGUIN_STOCK->value => 'Kinguin de stokta',
            self::DELETED->value => 'Silinmiş',
            self::BUY->value => 'Kişi Ödeme yaptı',
            default => 'Belirlenemedi(' . $statusCode . ')'
        };
    }

}
