<?php

namespace App\Console\Commands;

use App\Enums\MarketplaceName;
use App\Models\MarketPlace;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateMarketplace extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-marketplace';

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
        MarketPlace::truncate();

        foreach (MarketplaceName::cases() as $case) {
            MarketPlace::create([
                'name' => $case->name,
                'id' => $case->value
            ]);

            if ($case->value == MarketplaceName::KINGUIN->value) {
                $kinguin = MarketPlace::whereId(MarketplaceName::KINGUIN->value)->first();

                if ($kinguin) {
                    $kinguinOld = DB::connection('b2b_live')->table('marketplace_api_credentials')->where('name', 'Kinguin Api')->first();
                    if ($kinguinOld) {
                        $kinguin->update([
                            'callback_urls' => $kinguinOld->callback_url
                        ]);
                    }
                }
            }

            if ($case->value == MarketplaceName::ENEBA->value) {
                $eneba = MarketPlace::whereId(MarketplaceName::ENEBA->value)->first();

                if ($eneba) {
                    $enebaOld = DB::connection('b2b_live')->table('marketplace_api_credentials')->where('name', 'Eneba Api')->first();
                    if ($enebaOld) {
                        $eneba->update([
                            'callback_urls' => $enebaOld->callback_url
                        ]);
                    }
                }
            }
        }
    }
}
