<?php

namespace App\Providers;


use App\Filament\Pages\Auth\LoginAsCustomer;
use App\Http\Requests\Api\Ziraat\ZiraatExtreRequest;

use App\Services\GameService;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

use Filament\Pages\Auth\Login;
use Laravel\Horizon\HorizonApplicationServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(Login::class, LoginAsCustomer::class);
        $this->app->bind(HorizonApplicationServiceProvider::class, MyHorizonProvider::class);

        $this->app->bind(GameService::class, function () {
            return new GameService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Http::macro('ziraatExtre', function (ZiraatExtreRequest $ziraatExtreRequest) {
            return Http::timeout(30)->withOptions([
                'verify' => false,
            ])->asForm()->post(config('ziraat.ZIRAAT_URL'), [
                'KurumKod' => config('ziraat.ZIRAAT_INSTITUTION_CODE'),
                'Sifre' => config('ziraat.ZIRAAT_PASSWORD'),
                'HesapNo' => config('ziraat.ZIRAAT_ACCOUNT_NO'),
                'BaslangicTarihi' => $ziraatExtreRequest->input('start'),
                'BitisTarihi' => $ziraatExtreRequest->input('end')
            ]);
        });
        URL::forceScheme('https');
    }


}
