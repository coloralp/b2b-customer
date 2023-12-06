<?php

namespace App\Traits;

use App\Enums\KeyStatus;
use App\Models\Game;
use App\Models\Key;
use App\Models\Summary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait AccountSummaryTrait
{
    //margin gelir
    //giro ciro (amount_convert_euro)
    //gider cost


    public function getSummaryDatByYearAndMonth($month, $year): array
    {

        $keys = Key::getAccountSummary($month, $year)->with('saleInfo')
            ->whereNotNull('cost_convert_euro')
            ->get();


        $totalCost = $keys->sum('cost_convert_euro');

        $saleInfos = $keys->pluck('saleInfo');

        $totalAmount = $saleInfos->sum('amount_convert_euro');


        return [
            'cost' => $keys->count() ? (float)str_replace(',', '', number_format($totalCost, 2)) : "0.00",
            'giro' => $keys->count() ? (float)str_replace(',', '', number_format($totalAmount, 2)) : "0.00",
            'margin' => $keys->count() ? (float)str_replace(',', '', number_format(($totalAmount - $totalCost), 2)) : "0.00"
        ];
    }

    public function getTotalKdv($month, $year): mixed
    {

        return Key::getAccountSummary($month, $year)->sum('kdv_amount');
    }

    public function byCategory($month, $year): array
    {
        $byCategory = DB::table('keys')
            ->join('games', 'games.id', '=', 'keys.game_id')
            ->join('sale_infos', 'sale_infos.id', '=', 'keys.sale_info_id')
            ->select(
                'games.category_type as category_id',
                DB::raw("case when games.category_type = 1 then 'PC' when games.category_type = 2 then 'Console' when games.category_type = 3 then 'Giftcard/Epin' end as category_type"),
                DB::raw('SUM(keys.cost_convert_euro) as cost'),
                DB::raw('SUM(sale_infos.amount_convert_euro) as giro'),
                DB::raw('SUM(sale_infos.amount_convert_euro - keys.cost_convert_euro) as profit')
            )
            ->whereBetween('keys.sell_date', [Carbon::createFromDate($year, $month)->startOfMonth(), Carbon::createFromDate($year, $month)->endOfMonth()])
            ->where('keys.status', '=', KeyStatus::SOLD->value)
            ->groupBy('games.category_type')
            ->get()->toArray();

        $categories = collect($byCategory);

        $allCategoryInfo = array();
        foreach (Game::GAME_CATEGORY_TYPES as $index => $typeId) {
            if (!$categories->where('category_id', $typeId)->count()) {
                $allCategoryInfo[] = [
                    'category_id' => $typeId,
                    'category_type' => $index,
                    'cost' => 0,
                    'giro' => 0,
                    'profit' => 0
                ];
            } else {

                $allCategoryInfo [] = json_decode(json_encode($categories->where('category_id', $typeId)->first()), 1);
            }
        }

        return $allCategoryInfo;
    }
}
