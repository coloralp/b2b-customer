<?php

namespace App\Filament\Pages\Auth;

use App\Rules\Customer\CheckIsCustomer;
use App\Rules\Customer\IfVerifyLogin;
use App\Rules\ExistJar;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\Login;

class LoginAsCustomer extends Login
{
    protected function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
            ->label(__('filament-panels::pages/auth/login.form.email.label'))
            ->email()
            ->rule(new CheckIsCustomer())->rule(new IfVerifyLogin())->rule(new ExistJar())
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);
    }
}
