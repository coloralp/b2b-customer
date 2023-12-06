<?php

namespace App\Console\Commands;

use App\Services\KeyService;
use App\Services\KeyStatusHistoryService;
use Illuminate\Console\Command;

class MoveStatusHistories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:move-status-histories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(KeyStatusHistoryService $service, KeyService $keyService)
    {
//        Log::channel('test_log')->info('app:move-status-histories');
//
//        $count = MoveStatusHistories::whereMonth('created_at', '!=', now()->month)->count();
//
//        $lastSummaryData = $keyService->getLastMonthSummary();
//
//        Summary::upsert($lastSummaryData, ['year_id', 'month_id']);
//
//
//        while ($count > 0) {
//            //özeti tabloya yazdır sonra burayı bu ay için boşalt
//            MainKeyHistory::whereMonth('created_at', '!=', now()->month)->chunk(100, function (Collection $histories) {
//                $data = $histories->map(function (KeyStatusHistory $keyStatusHistory) {
//                    return $keyStatusHistory->toArray();
//                })->toArray();
//
//                $res = array();
//                foreach ($data as $datum) {
//                    $datum['id'] = null;
//                    $datum['created_at'] = Carbon::parse($datum['created_at']);
//                    $datum['updated_at'] = Carbon::parse($datum['updated_at']);
//                    $res [] = $datum;
//                }
//
//                DB::transaction(function () use ($histories, $res) {
//                    MainKeyHistory::insert($res);
//                    MainKeyHistory::whereIn('id', $histories->pluck('id')->toArray())->forceDelete();
//                });
//
//            });
//
//            $count = MainKeyHistory::whereMonth('created_at', '!=', now()->month)->count();
//        }
    }
}
