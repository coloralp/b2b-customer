<?php

namespace App\Services;

use App\Enums\CurrencySymbol;
use App\Models\Summary;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SummaryService
{
    public function getSummaryData(): array
    {
        $month = (now()->month) - 1;

        $thiYearSummaries = Summary::whereYearId(now()->year)->get();


        $summary = $thiYearSummaries->where('month_id', $month)->first();

        if (!$summary) {
            return [
                'summary' => null
            ];
        }


        $lastMonthData = [
            'cost' => PriceService::convertFloatForFront($summary->total_cost, true),
            'add_expense' => $summary->add_expense_front,
            'total_cost' => $summary->net_cost_front,

            'profit' => PriceService::convertFloatForFront($summary->total_margin, true),
            'giro' => PriceService::convertFloatForFront($summary->total_giro, true),
            'net_profit' => PriceService::convertFloatForFront($summary->net_margin, true),
        ];


        $thisYearDataFromCollection = $this->getSummaryDataFromSummaries($thiYearSummaries);

        $generalSummaries = Summary::all();

        return [
            'summary' => true,
            'lastMonthData' => $lastMonthData,
            'thisYearDataFromCollection' => $thisYearDataFromCollection,
            'generalSales' => $this->getSummaryDataFromSummaries($generalSummaries)
        ];


    }

    public function getSummaryDataFromSummaries(Collection $generalSummaries): array
    {
        return [
            'cost' => PriceService::convertFloatForFront(PriceService::convertStrToFloat($generalSummaries->sum('total_cost')), true),
            'add_expense' => PriceService::convertFloatForFront(PriceService::convertStrToFloat($generalSummaries->sum('add_cost')), true),
            'total_cost' => PriceService::convertStrToFloat($generalSummaries->sum('net_cost'), true),

            'profit' => PriceService::convertFloatForFront(PriceService::convertStrToFloat($generalSummaries->sum('total_margin')), true),
            'giro' => PriceService::convertFloatForFront(PriceService::convertStrToFloat($generalSummaries->sum('total_giro')), true),
            'net_profit' => PriceService::convertFloatForFront(PriceService::convertStrToFloat($generalSummaries->sum('net_margin')), true),
        ];
    }
}
