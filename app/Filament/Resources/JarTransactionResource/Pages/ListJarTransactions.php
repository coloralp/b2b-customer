<?php

namespace App\Filament\Resources\JarTransactionResource\Pages;

use App\Filament\Resources\JarTransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJarTransactions extends ListRecords
{
    protected static string $resource = JarTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
