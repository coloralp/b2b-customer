<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\Summary;
use App\Services\ExpenseService;
use App\Services\PriceService;
use App\Traits\AccountSummaryTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GetSummary extends Command
{
    use AccountSummaryTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-summary {--month=} {--year=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bu komut kebir sayfası için önceki aya ait veriyi db ye yazamk için kullanılır.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $montName = $this->option('month') != null
            ? Carbon::createFromDate(null, $this->option('month'))->locale('tr')->isoFormat('MMMM')
            : now()->locale('tr')->isoFormat('MMMM'); //ocak


        $month = $this->option('month') ?? now()->month;
        $year = $this->option('year') ?? now()->year;

        $month = ltrim($month, '0');

        echo "month:$month => year:$year" . PHP_EOL;

        ini_set('max_execution_time', '300'); //300 seconds = 5 minutes

        if ($year == now()->year && $month > now()->month) {
            $byCategory = [];

            foreach (range(1, 3) as $type) {
                $byCategory[] = [
                    'giro' => 0,
                    'cost' => 0,
                    'profit' => 0,
                    'category_id' => $type,
                    'category_type' => array_search($type, Game::GAME_CATEGORY_TYPES),
                ];
            }

            $data = [
                'month_name' => $montName,
                'year_id' => $year,
                'total_margin' => 0,
                'total_giro' => 0,
                'total_cost' => 0,
                'add_cost' => 0,
                'net_margin' => 0,
                'month_id' => $month,
                'by_category_info' => json_encode($byCategory),
            ];
            if ($summary = Summary::query()->where('year_id', $year)->where('month_id', $month)->first()) {
                $summary->update($data);
            } else {
                Summary::create($data);
            }
        } else {


            $subData = $this->getSummaryDatByYearAndMonth($month, $year);

            $start = Carbon::create($year, $month)->startOfMonth();
            $end = Carbon::create($year, $month)->endOfMonth();


            $margin = $subData['margin'];

            $cost = $subData['cost'];

            $giro = $subData['giro'];

            $addCost = ExpenseService::getTotalExpenseByDate($start, $end);

            $netProfit = PriceService::convertFloat($margin - $addCost);


            $byCategory = $this->byCategory($month, $year);

            $data = [
                'month_name' => $montName,
                'year_id' => $year,
                'total_margin' => $margin,
                'total_giro' => $giro,
                'total_cost' => $cost,
                'add_cost' => $addCost,
                'net_margin' => $netProfit,
                'month_id' => $month,
                'by_category_info' => json_encode($byCategory),
                'total_kdv' => $this->getTotalKdv($month, $year)
            ];

            if ($summary = Summary::query()->where('year_id', $year)->where('month_id', $month)->first()) {
                $summary->update($data);
            } else {
                Summary::create($data);
            }
        }


    }
}
