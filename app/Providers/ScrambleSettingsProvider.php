<?php

namespace App\Providers;

use App\Models\User;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;

class ScrambleSettingsProvider extends ServiceProvider
{

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        Scramble::routes(function (Route $route) {
            return !Str::contains($route->uri, ['test', 'job', 'broadcast', 'csrf-cookie', 'horizon', 'webhook', 'callback'])
                and (!Str::contains($route->getName(), 'no-api-doc'));
        });

        Scramble::extendOpenApi(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });

    }
}
