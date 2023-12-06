<?php

namespace App\Enums;

enum OrderExportStatus: int
{
    case APPROVE = 1;
    case REJECT = 2;


    case RESERVE = 6;

    case RETURNED = 8;

    case PENDING = 9;

    case ALL = -1;

    public static function defineStatus($id)
    {
        $id = (int)$id;

        return match ($id) {
            self::APPROVE->value => 'Onaylanmış',
            self::REJECT->value => 'Reddedilmiş',
            self::RESERVE->value => 'Reserve',
            self::RETURNED->value => 'Geri İade',
            self::PENDING->value => 'Beklenen',
            self::ALL->value => 'Tümü'
        };
    }


    public static function defineColor($id)
    {
        $id = (int)$id;

        return match ($id) {
            self::APPROVE->value => 'success',
            self::REJECT->value => 'danger',
            self::DECLINE->value => 'danger',
            self::CREATED->value => 'info',
            self::DELIVERED->value => 'success',
            self::RESERVE->value => 'warning',
            self::AUTO_REJECT->value => 'danger',
            self::RETURNED->value => 'warning',
            self::PENDING->value => 'warning'
        };
    }


}
