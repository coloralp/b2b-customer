<?php

namespace App\Console\Commands;

use App\Models\Bank;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GetBankInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-bank-info';

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
        $banks = DB::connection('b2b_live')->table('banks')->orderByDesc('id')
            ->chunk(100, function ($collection) {
                foreach ($collection as $item) {
                    Bank::query()->upsert(
                        [
                            [
                                'name' => $item->name
                            ]
                        ], 'name');
                }
            });

    }
}
