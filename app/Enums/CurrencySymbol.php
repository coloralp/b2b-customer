<?php

namespace App\Enums;

enum CurrencySymbol: string
{
    case EUR = '€';
    case TRY = '₺';
    case GBP = '£';
    case USD = '$';
    case BTC = '₿';

    public static function defineName(mixed $id): string
    {

        if (is_null($id)) {
            throw new \Exception('CUrrency tespti edilemedi');
        }


        if (!is_int($id)) {
            $id = (int)$id;
        }

        return match ($id) {
            CurrencyEnum::EUR->value => self::EUR->value,
            CurrencyEnum::TRY->value => self::TRY->value,
            CurrencyEnum::GBP->value => self::GBP->value,
            CurrencyEnum::USD->value => self::USD->value,
            CurrencyEnum::BTC->value => self::BTC->value,
        };
    }
}
