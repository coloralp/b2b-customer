<?php

namespace App\Filament\Resources\JarTransactionsRequestResource\Pages;

use App\Filament\Resources\JarTransactionsRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditJarTransactionsRequest extends EditRecord
{
    protected static string $resource = JarTransactionsRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
