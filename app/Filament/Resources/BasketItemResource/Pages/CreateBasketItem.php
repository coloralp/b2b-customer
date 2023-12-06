<?php

namespace App\Filament\Resources\BasketItemResource\Pages;

use App\Filament\Resources\BasketItemResource;
use App\Models\BasketItem;
use App\Models\Game;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBasketItem extends CreateRecord
{

    protected static string $resource = BasketItemResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $game = Game::find($data['game_id']);

        $data['unit_price'] = $game->amount;
        $data['who'] = auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        $resource = static::getResource();
        return $resource::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel())($data);

        if (
            static::getResource()::isScopedToTenant() &&
            ($tenant = Filament::getTenant())
        ) {
            return $this->associateRecordWithTenant($record, $tenant);
        }

        $data = BasketItem::whereWho(auth()->id())->whereGameId($record['game_id']);

        if ($data->exists()) {
            $data->update([
                'qty' => $record['qty']
            ]);
        } else {
            $record->save();
        }
        return $record;

    }


}
