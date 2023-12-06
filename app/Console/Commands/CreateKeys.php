<?php

namespace App\Console\Commands;

use App\Enums\RoleEnum;
use App\Models\Currency;
use App\Models\Game;
use App\Models\Key;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateKeys extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-keys';

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
        Key::truncate();

        foreach (Game::all() as $game) {

            foreach (range(1, rand(10, 20)) as $keyNum) {
                $costCurrency = Currency::findOrFail(rand(1, 4));
                $min = 10.0;
                $max = 20.0;

                $cotPrice = mt_rand() / mt_getrandmax() * ($max - $min) + $min;
                $game->keys()->create([
                    'uuid' => Str::uuid(),
                    'who' => 2,
                    'supplier_id' => User::role(RoleEnum::SUPPLIER->value)->inRandomOrder(1)->first()->id,
                    'key' => Str::random(10),

                    'cost' => $cotPrice,
                    'cost_currency_id' => $costCurrency->id,
                    'cost_convert_euro' => CurrencyService::convertEur($cotPrice, $costCurrency)
                ]);
            }
        }

    }
}
