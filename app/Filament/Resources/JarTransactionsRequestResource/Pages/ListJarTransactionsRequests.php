<?php

namespace App\Filament\Resources\JarTransactionsRequestResource\Pages;

use App\Filament\Resources\JarTransactionsRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJarTransactionsRequests extends ListRecords
{
    protected static string $resource = JarTransactionsRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
