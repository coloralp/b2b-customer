<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Enums\CurrencyEnum;
use App\Models\Currency;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Artisan::call('app:get-customers');

        echo 'customers bitti' . PHP_EOL;

        Artisan::call('app:get-roles');

        echo 'Roles bitti' . PHP_EOL;

        Artisan::call('app:get-users');

        Artisan::call('app:get-marketplace');

        echo 'Marketplace bitti' . PHP_EOL;

        Artisan::call('app:get-languages');

        echo 'Languages doldu' . PHP_EOL;

        Currency::truncate();

        $currencies = CurrencyEnum::cases();

        foreach ($currencies as $currency) {
            Currency::create([
                'id' => $currency->value,
                'name' => $currency->name,
                'symbol' => match ($currency->value) {
                    CurrencyEnum::EUR->value => '€',
                    CurrencyEnum::TRY->value => '₺',
                    CurrencyEnum::USD->value => '$',
                    CurrencyEnum::GBP->value => '£',
                    CurrencyEnum::BTC->value => '₿',
                }
            ]);
        }

        echo 'Currencies doldu' . PHP_EOL;

        Artisan::call('app:get-suppliers');

        echo 'Suppliers doldu' . PHP_EOL;


        Artisan::call('app:get-regions');

        echo 'Reigions doldu' . PHP_EOL;


        Artisan::call('app:get-categories');

        echo 'Categories doldu' . PHP_EOL;

        Artisan::call('app:get-publishers');

        echo 'Publishers doldu' . PHP_EOL;

        Artisan::call('app:get-games');

        echo 'Games doldu' . PHP_EOL;

        Artisan::call('app:set-comission-settings');


        Artisan::call('app:get-offer-from-marketplace');

        echo 'Sistemde var olan eşli offerlar doldu' . PHP_EOL;
//
//        $date = Carbon::create(2022, 1)->startOfMonth()->format('Y-m-d');
//        Artisan::call("app:get-orders '$date'");
//
//
//        echo 'Orderlar alındı' . PHP_EOL;
//
//
//        Artisan::call('app:update-who');
//
//        echo 'wholar değiştirildi' . PHP_EOL;
//
//        echo 'Bitti!!!' . PHP_EOL;
    }
}
