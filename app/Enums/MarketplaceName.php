<?php

namespace App\Enums;

use App\Models\User;

enum MarketplaceName: int
{

    //must be same market_places id and name
    case KINGUIN = 1;

    case ENEBA = 2;

    case GAMIVO = 3;
    case K4G = 4;
    case CODEWHOLESALE = 5;
    case G2A = 6;
    case ETAIL = 7;

    case GENERAL = 8;

    public static function defineCustomer($id): string
    {
        $id = (int)$id;

        if (is_null($id)) {
            return 'Belirlenemedi';
        }

        return match ($id) {
            self::KINGUIN->value => 'Kinguin Customer',
            self::ENEBA->value => 'Eneba Customer',
            self::GAMIVO->value => 'Gamivo Customer',
            self::K4G->value => 'K4G Customer',
            self::CODEWHOLESALE->value => 'CODEWHOLESALE Customer',
            self::G2A->value => 'G2A Customer',
            self::ETAIL->value => 'ETAIL Customer',
            default => "Customer *($id)"
        };
    }

    public static function defineApiName($id): string
    {
        $id = (int)$id;

        return match ($id) {
            self::KINGUIN->value => 'Kinguin Api',
            self::ENEBA->value => 'Eneba Api',
            self::GAMIVO->value => 'Gamivo Api',
            self::K4G->value => 'K4G Api',
            self::CODEWHOLESALE->value => 'CODEWHOLESALE Api',
            self::G2A->value => 'G2A Api',
            self::ETAIL->value => 'ETAIL Api',
            default => "Customer *($id)"
        };
    }

    public static function customer($id)
    {
        $id = (int)$id;

        return match ($id) {
            self::ENEBA->value => User::role(RoleEnum::CUSTOMER->value)->whereName('Eneba Customer')->first()->id ?? null,
            self::GAMIVO->value => User::role(RoleEnum::CUSTOMER->value)->whereName('Gamivo Customer')->first()->id ?? null,
            self::K4G->value => User::role(RoleEnum::CUSTOMER->value)->whereName('K4G Customer')->first()->id ?? null,
            self::KINGUIN->value => User::role(RoleEnum::CUSTOMER->value)->whereName('Kinguin Customer')->first()->id ?? null,
            default => null
        };
    }

    public static function supplier($id)
    {
        $id = (int)$id;

        return match ($id) {
            self::ENEBA->value => User::whereName('Eneba Supplier')->first()->id ?? null,
            self::GAMIVO->value => User::whereName('Gamivo Supplier')->first()->id ?? null,
            self::K4G->value => User::whereName('K4G Supplier')->first()->id ?? null,
            default => null
        };
    }

    public static function commissionPercent($id)
    {
        $id = (int)$id;

        return match ($id) {
            self::ENEBA->value => 0,
            self::GAMIVO->value => 5,
            self::K4G->value => 3,
            default => 0
        };
    }

    public static function commissionConst($id)
    {
        $id = (int)$id;

        return match ($id) {
            self::ENEBA->value => 5,
            self::GAMIVO->value => 0.10,
            self::K4G->value => 0,
            default => 0
        };
    }


    public static function defineImage($id): array
    {
        $id = (int)$id;

        return match ($id) {

//            default => "Customer *($id)"  self::KINGUIN->value => env('APP_URL') . '/images/vendor/kinguin-logo.png',
            self::KINGUIN->value => [
                'id' => self::KINGUIN->value,
                'image' => "https://api.cdkeyci.com" . '/images/vendor/kinguin-logo.png'
            ],
            self::ENEBA->value => [
                'id' => self::ENEBA->value,
                'image' => "https://api.cdkeyci.com" . '/images/vendor/eneba-logo.png'
            ],
            self::GAMIVO->value => [
                'id' => self::GAMIVO,
                'image' => "https://api.cdkeyci.com" . '/images/vendor/gamivo-logo.png'
            ],
            self::K4G->value => [
                'id' => self::K4G->value,
                'image' => "https://api.cdkeyci.com" . '/images/vendor/k4g-logo.png'
            ],
            self::CODEWHOLESALE->value => [
                'id' => self::CODEWHOLESALE->value,
                'image' => "https://api.cdkeyci.com" . '/images/vendor/code-wholesale-logo.png'
            ],
            self::G2A->value => [
                'id' => self::G2A->value,
                'image' => "https://api.cdkeyci.com" . '/images/vendor/g2a-logo.png'
            ],
            self::ETAIL->value => [
                'id' => self::ETAIL->value,
                'image' => "https://api.cdkeyci.com" . '/images/vendor/etail-logo.png'
            ],
            self::GENERAL->value => [
                'id' => self::GENERAL->value,
                'image' => "https://api.cdkeyci.com" . '/images/vendor/general.png'
            ],
            default => "Customer *($id)"
        };
    }

    public static function getImages(array $marketplaceIds): array
    {
        $array = [];

        foreach ($marketplaceIds as $marketplaceId) {
            $array[] = self::defineImage($marketplaceId);
        }

        return $array;
    }

    public static function defineErrorLog($id): string
    {
        $id = (int)$id;

        return match ($id) {
            self::KINGUIN->value => self::KINGUIN->name . '_error',
            self::ENEBA->value => self::ENEBA->name . '_error',
            self::GAMIVO->value => self::GAMIVO->name . '_error',
            self::K4G->value => self::K4G->name . '_error',
            self::CODEWHOLESALE->value => self::CODEWHOLESALE->name . '_error',
            self::G2A->value => self::G2A->name . '_error',
            self::ETAIL->value => self::ETAIL->name . '_error',
            default => "Customer *($id)"
        };
    }

    public static function defineSuccessLog($id): string
    {
        $id = (int)$id;

        return match ($id) {
            self::KINGUIN->value => self::KINGUIN->name . '_success',
            self::ENEBA->value => self::ENEBA->name . '_success',
            self::GAMIVO->value => self::GAMIVO->name . '_success',
            self::K4G->value => self::K4G->name . '_success',
            self::CODEWHOLESALE->value => self::CODEWHOLESALE->name . '_success',
            self::G2A->value => self::G2A->name . '_success',
            self::ETAIL->value => self::ETAIL->name . '_success',
            default => "Customer *($id)"
        };
    }

    public function getEmail(): string
    {
        return match ($this) {
            self::ENEBA => 'eneba@gamil.com',
            self::KINGUIN => 'kinguin@gamil.com',
            self::GAMIVO => 'gamivo@gamil.com',
            self::K4G => 'k4g@gamil.com',
            self::ETAIL => 'etail@gamil.com',
            self::CODEWHOLESALE => 'codewhosale@gamil.com',
            self::G2A => 'g2a@gamil.com',
            self::GENERAL => -1,
        };
    }

    public function defineCustomerName(): string
    {
        return match ($this) {
            self::KINGUIN => 'Kinguin Customer',
            self::ENEBA => 'Eneba Customer',
            self::GAMIVO => 'Gamivo Customer',
            self::K4G => 'K4G Customer',
            self::CODEWHOLESALE => 'CODEWHOLESALE Customer',
            self::G2A => 'G2A Customer',
            self::ETAIL => 'ETAIL Customer',
            default => "Customer *(not found)"
        };
    }

}
