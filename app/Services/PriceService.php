<?php

namespace App\Services;

use App\Enums\CurrencySymbol;

class PriceService
{

    public static function convertFloat(float $float = null)
    {

        if (is_null($float)) {
            return 0;
        }


        $str = number_format($float, 2);
        $str = str_replace(',', '', $str);
        return (float)$str;
    }

    public static function convertFloatForFront(float $float = null, $isEur = false): int|string
    {

        if (is_null($float)) {
            return 0;
        }


        return $isEur ? number_format($float, 2) . CurrencySymbol::EUR->value : number_format($float, 2);
    }

    public static function convertStrToFloat($str, $isEur = false)
    {
        $str = str_replace(',', '', $str);
        return floatval($str);
    }


    public static function toFloatArrayItems(array $array): array
    {
        $result = [];
        foreach ($array as $index => $item) {
            $item = str_replace(',', '', $item);
            $result[$index] = self::convertFloat((float)$item);
        }
        return $result;
    }

    public static function toStringArrayItems(array $array): array
    {
        $result = [];
        foreach ($array as $index => $item) {
            $item = str_replace(',', '', $item);
            $item = self::convertFloat((float)$item);
            $result[$index] = number_format($item, 2) . \App\Enums\CurrencySymbol::EUR->value;
        }
        return $result;
    }


}
