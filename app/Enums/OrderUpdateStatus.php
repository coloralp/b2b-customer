<?php

namespace App\Enums;

enum OrderUpdateStatus: int
{
    case APPROVE = 1;
    case DECLINE = 2;

    public static function defineStatus($id)
    {
        $id = (int)$id;

        return match ($id) {
            self::APPROVE->value => 'Onayla',
            self::DECLINE->value => 'İptal',
        };
    }
}
