<?php

namespace App\Filament\Pages;

use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasPageShield;

    protected function getShieldRedirectPath(): string
    {

        return route('filament.admin.auth.login');
    }

}
