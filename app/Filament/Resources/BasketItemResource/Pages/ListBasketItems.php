<?php

namespace App\Filament\Resources\BasketItemResource\Pages;

use App\Filament\Resources\BasketItemResource;
use App\Models\BasketItem;
use App\Services\OrderService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBasketItems extends ListRecords
{
    protected static string $resource = BasketItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Finish Order')
                ->requiresConfirmation()
                ->action(function () {
                    OrderService::createPanelCustomerOrder(auth()->id());
                })->hidden(fn() => !BasketItem::whereWho(auth()->id())->exists())

        ];
    }
}
