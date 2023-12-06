<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CurrencyEnum: int implements HasLabel
{
    case EUR = 1;
    case TRY = 2;
    case GBP = 3;
    case USD = 4;
    case BTC = 5;

    //must be same currencies table id

    public static function defineSymbol(int $currencyId): string
    {
        return match ($currencyId) {
            self::EUR->value => '€',
            self::TRY->value => '₺',
            self::GBP->value => '£',
            self::USD->value => '$',
            self::BTC->value => '₿',
        };
    }

    public function getSymbol(): string
    {
        return match (true) {
            $this->value == self::EUR->value => '€',
            $this->value == self::TRY->value => '₺',
            $this->value == self::GBP->value => '£',
            $this->value == self::USD->value => '$',
            $this->value == self::BTC->value => '₿',
            default => '€',
        };
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::EUR => '€',
            self::TRY => '₺',
            self::GBP => '£',
            self::USD => '$',
            self::BTC => '₿',
        };
    }
}


