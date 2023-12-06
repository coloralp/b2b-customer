<?php

namespace App\Console\Commands;

use App\Models\ExchangeInfo;
use App\Services\CurrencyService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class GetDailyCurrency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-daily-currency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Log::channel('test_log')->info('app:get-daily-currency çalıştımmm');
        $data = CurrencyService::getCurrency();

        ExchangeInfo::create([
            'data' => json_encode($data),
            'date' => now()
        ]);

    }
}
