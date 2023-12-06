<?php

namespace App\Console\Commands;

use App\Enums\CategoryTypeEnum;
use App\Enums\MarketplaceName;
use App\Models\Category;
use App\Models\MarketplaceCommission;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SetComissionSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:set-comission-settings';

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
        MarketplaceCommission::truncate();
        //eneba
        $pcCategory = Category::whereName(CategoryTypeEnum::PC->name)->first();

        if (!$pcCategory) {
            $pcCategory = Category::create([
                'name' => CategoryTypeEnum::PC->name,
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::ENEBA->value,
            'category_id' => $pcCategory->id,
            'percent_value' => 6,
            'const_value' => 0.20,
            'min' => 0.01,
            'max' => 4.99
        ]);

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::ENEBA->value,
            'category_id' => $pcCategory->id,
            'percent_value' => 6,
            'const_value' => 0.25,
            'min' => 5.00,
            'max' => 500.00
        ]);

        $consoleCategory = Category::whereName(CategoryTypeEnum::PC->name)->first();
        if (!$pcCategory) {
            $consoleCategory = Category::create([
                'name' => CategoryTypeEnum::CONSOLE->name,
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::ENEBA->value,
            'category_id' => $consoleCategory->id,
            'percent_value' => 6,
            'const_value' => 0.10,
            'min' => 0.01,
            'max' => 4.99
        ]);

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::ENEBA->value,
            'category_id' => $consoleCategory->id,
            'percent_value' => 6,
            'const_value' => 0.20,
            'min' => 5.00,
            'max' => 500.00
        ]);

        $gifCardEpinCategory = Category::whereName(CategoryTypeEnum::GIFTCARD_EPIN->name)->first();

        if (!$gifCardEpinCategory) {
            $gifCardEpinCategory = Category::create([
                'name' => CategoryTypeEnum::GIFTCARD_EPIN->name,
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::ENEBA->value,
            'category_id' => $gifCardEpinCategory->id,
            'percent_value' => 5,
            'const_value' => 0.00,
            'min' => 0.01,
            'max' => 10
        ]);

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::ENEBA->value,
            'category_id' => $gifCardEpinCategory->id,
            'percent_value' => 5,
            'const_value' => 0.10,
            'min' => 10.01,
            'max' => 20.00
        ]);

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::ENEBA->value,
            'category_id' => $gifCardEpinCategory->id,
            'percent_value' => 5,
            'const_value' => 0.20,
            'min' => 20.01,
            'max' => 500.00
        ]);


        //gamivo

        $pc = Category::whereName('PC')->first();

        if (!$pc) {
            $pc = Category::create(['name' => 'PC',
                'uuid' => Str::uuid()]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $pc->id,
            'percent_value' => 8,
            'const_value' => 0.40,
            'min' => 3.70,
            'max' => 9999.99
        ]);


        $psnGif = Category::whereName('PSN Gift Card')->first();

        if (!$psnGif) {
            $psnGif = Category::create(['name' => 'PSN Gift Card',
                'uuid' => Str::uuid()]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $psnGif->id,
            'percent_value' => 5,
            'const_value' => 0,
            'min' => 0,
            'max' => 0
        ]);

        $xboxGif = Category::whereName('Xbox Gift Card')->first();

        if (!$xboxGif) {
            $xboxGif = Category::create(['name' => 'Xbox Gift Card',
                'uuid' => Str::uuid()]);
        }


        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $xboxGif->id,
            'percent_value' => 5,
            'const_value' => 0,
            'min' => 0,
            'max' => 0
        ]);


        $steamGif = Category::whereName('Nintendo Gift Card')->first();

        if (!$steamGif) {
            $steamGif = Category::create(['name' => 'Nintendo Gift Card',
                'uuid' => Str::uuid()]);
        }


        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $steamGif->id,
            'percent_value' => 3,
            'const_value' => 0.30,
            'min' => 0,
            'max' => 0
        ]);

        $ninomGif = Category::whereName('Nintendo Gift Card')->first();


        if (!$ninomGif) {
            $ninomGif = Category::create([
                'name' => 'Nintendo Gift Card',
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create(array(
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $ninomGif->id,
            'percent_value' => 3,
            'const_value' => 0.30,
            'min' => 0,
            'max' => 0
        ));

        $appleGif = Category::whereName('Apple Gift Card')->first();

        if (!$appleGif) {
            $appleGif = Category::create([
                'name' => 'Apple Gift Card',
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $appleGif->id,
            'percent_value' => 3,
            'const_value' => 0.30,
            'min' => 0,
            'max' => 0
        ]);

        $googleGif = Category::whereName('Google Gift Card')->first();

        if (!$googleGif) {
            $googleGif = Category::create([
                'name' => 'Google Gift Card',
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $googleGif->id,
            'percent_value' => 3,
            'const_value' => 0.30,
            'min' => 0,
            'max' => 0
        ]);

        $razerGif = Category::whereName('Steam Gift Card')->first();

        if (!$razerGif) {
            $razerGif = Category::create([
                'name' => 'Steam Gift Card',
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $razerGif->id,
            'percent_value' => 5.95,
            'const_value' => 0.30,
            'min' => 0,
            'max' => 0
        ]);

        $riot = Category::whereName('Riot')->first();

        if (!$riot) {
            $riot = Category::create([
                'name' => 'Riot',
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $riot->id,
            'percent_value' => 8,
            'const_value' => 0.40,
            'min' => 0,
            'max' => 0
        ]);

        $netFlixGif = Category::whereName('Netflix Gift Card')->first();

        if (!$netFlixGif) {
            $netFlixGif = Category::create([
                'name' => 'Netflix Gift Card',
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $netFlixGif->id,
            'percent_value' => 5.95,
            'const_value' => 0.30,
            'min' => 0,
            'max' => 0
        ]);

        $Kaspersky = Category::whereName('Kaspersky')->first();

        if (!$Kaspersky) {
            $Kaspersky = Category::create([
                'name' => 'Kaspersky',
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $Kaspersky->id,
            'percent_value' => 40,
            'const_value' => 0.30,
            'min' => 0,
            'max' => 0
        ]);


        $midasbuy = Category::whereName('midasbuy')->first();

        if (!$midasbuy) {
            $midasbuy = Category::create([
                'name' => 'midasbuy',
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $midasbuy->id,
            'percent_value' => 5.95,
            'const_value' => 0.30,
            'min' => 0,
            'max' => 0
        ]);

        $epic = Category::whereName('epic')->first();

        if (!$epic) {
            $epic = Category::create([
                'name' => 'epic',
                'uuid' => Str::uuid()
            ]);
        }

        MarketplaceCommission::create([
            'marketplace_id' => MarketplaceName::GAMIVO->value,
            'category_id' => $epic->id,
            'percent_value' => 5.95,
            'const_value' => 0.30,
            'min' => 0,
            'max' => 0
        ]);

    }
}
