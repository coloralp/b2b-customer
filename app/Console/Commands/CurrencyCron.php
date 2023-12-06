<?php

namespace App\Console\Commands;


use App\Services\CurrencyService;
use Illuminate\Console\Command;

class CurrencyCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $currencyService;

    /**
     * Create a new command instance.
     */
    public function __construct(CurrencyService $currencyService)
    {
        parent::__construct();

        $this->currencyService = $currencyService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->currencyService->getCurrency();

    }
}
