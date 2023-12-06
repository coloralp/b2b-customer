<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\Currency;

use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Key>
 */
class KeyFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $costCurrency = Currency::findOrFail(rand(1, 4));

        $min = 10.0;
        $max = 20.0;

        $cotPrice = mt_rand() / mt_getrandmax() * ($max - $min) + $min;
        return [
            'uuid' => Str::uuid(),
            'who' => 2,
            'supplier_id' => User::role(RoleEnum::SUPPLIER->value)->inRandomOrder(1)->first()->id,
//            'game_id' => Game::inRandomOrder(1)->first()->id,
            'game_id' => 1,
            'key' => Str::random(10),

            'cost' => $cotPrice,
            'cost_currency_id' => $costCurrency->id,
            'cost_convert_euro' => CurrencyService::convertEur($cotPrice, $costCurrency)

        ];
    }


}
