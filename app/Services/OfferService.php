<?php

namespace App\Services;

use App\Enums\OfferStatus;
use App\Models\Offer;
use Illuminate\Support\Carbon;

class OfferService
{
    public static function isAvailable(?Carbon $start, ?Carbon $finish, $status): bool
    {
        $now = now();

        if ($status == OfferStatus::ACTIVE->value) {
            if (!$start && !$finish) {
                //todo burayı sor ikisi null ise ne olacak
                return true;
            }

            if ($start and !$finish) {
                //başlangıcı belli bitişi belli değilse
                //böyle durumda başlangıç zamanı geldi ise bu aktif
                return $start->lt($now);
            }

            if ($finish and !$start) {
                //bitişi belli başlangıcı belli değilse
                //böyle durumda bitiş zamanı gelmedi ise aktif
                return $finish->gt($now);
            }

            return $now->isBetween($start, $finish);
        }
        return false;

    }

    public static function remainingTime(?Carbon $start, ?Carbon $finish, $status): string
    {
        $now = now();

        if ($status == OfferStatus::ACTIVE->value) {
            if (!$start && !$finish) {
                //todo burayı sor ikisi null ise ne olacak
                return 'there is no valid time range';
            }

            if ($start and !$finish) {
                //başlangıcı belli bitişi belli değilse
                //böyle durumda başlangıç zamanı geldi ise bu aktif
                return $start->lt($now) ? 'over offer' : 'There is no range until change status';
            }

            if ($finish and !$start) {
                //bitişi belli başlangıcı belli değilse
                //böyle durumda bitiş zamanı gelmedi ise aktif
                return $finish->gt($now) ? self::calculateTimeDifference($now, $finish) : Offer::OFFER_OVER;
            }

            return $now->isBetween($start, $finish) ? self::calculateTimeDifference($start, $finish) : Offer::OFFER_OVER;
        }
        return '-';

    }

    public static function calculateTimeDifference(Carbon $start, Carbon $finish): string
    {
        if ($start->gte($finish)) {
            return Offer::OFFER_OVER;
        }

        $difference = $start->diff($finish);

        $asStr = '';

        if ($difference->y > 0) {
            $asStr .= $difference->y > 1 ? "{$difference->y} years " : "{$difference->y} year ";
        }

        if ($difference->m > 0) {
            $asStr .= $difference->m > 1 ? "{$difference->m} months " : "{$difference->m} month ";
        }


        if ($difference->d > 0) {
            $asStr .= $difference->d > 1 ? "{$difference->d} days " : "{$difference->d} day";
        }

        if ($difference->h > 0) {
            $asStr .= $difference->h > 1 ? "{$difference->h} hours " : "{$difference->h} hour ";
        }

        if ($difference->m > 0) {
            $asStr .= $difference->m > 1 ? "{$difference->m} minutes " : "{$difference->m} minute ";
        }

        if ($difference->s > 0) {
            $asStr .= $difference->s > 1 ? "{$difference->s} seconds " : "{$difference->s} second ";
        }

        return $asStr;

    }


}
