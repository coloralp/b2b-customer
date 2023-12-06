<?php

namespace App\Filament\Resources\BasketItemResource\Pages;

use App\Filament\Resources\BasketItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBasketItem extends EditRecord
{
    protected static string $resource = BasketItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
